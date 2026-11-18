<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\Station;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MachineController extends Controller
{
    public function index()
    {
        $stations = Station::orderBy('name')->get();
        $machines = Machine::with('station')->withCount('hmLogs')->orderBy('name')->paginate(20);

        return view('machines.index', compact('stations', 'machines'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'name' => ['required', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'installation_date' => ['required', 'date', 'before_or_equal:today'],
            'brand_type' => ['required', 'string', 'max:255'],
            'hm_awal' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        Machine::create($validated);

        return redirect()->route('machines.index')->with('status', 'Mesin berhasil ditambahkan.');
    }

    public function storeForStation(Request $request, Station $station)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'installation_date' => ['required', 'date', 'before_or_equal:today'],
            'brand_type' => ['required', 'string', 'max:255'],
            'hm_awal' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        $station->machines()->create($validated);

        return redirect()
            ->route('stations.show', $station)
            ->with('status', 'Mesin berhasil ditambahkan ke stasiun.');
    }

    public function edit(Request $request, Machine $machine)
    {
        $stations = Station::orderBy('name')->get();
        $returnToStation = $request->query('return_to') === 'station';

        return view('machines.edit', compact('machine', 'stations', 'returnToStation'));
    }

    public function update(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'name' => ['required', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'installation_date' => ['nullable', 'date', 'before_or_equal:today'],
            'brand_type' => ['nullable', 'string', 'max:255'],
            'hm_awal' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'return_to' => ['nullable', Rule::in(['station'])],
        ]);

        $returnToStation = ($validated['return_to'] ?? null) === 'station';
        unset($validated['return_to']);

        $machine->update($validated);

        return $this->machineRedirect($machine, $returnToStation)
            ->with('status', 'Data mesin berhasil diperbarui.');
    }

    public function destroy(Request $request, Machine $machine)
    {
        $validated = $request->validate([
            'return_to' => ['nullable', Rule::in(['station'])],
        ]);
        $returnToStation = ($validated['return_to'] ?? null) === 'station';

        if ($machine->hmLogs()->exists()) {
            return $this->machineRedirect($machine, $returnToStation)
                ->with('error', 'Mesin tidak dapat dihapus karena sudah memiliki catatan HM.');
        }

        if ($machine->checkUps()->exists()) {
            return $this->machineRedirect($machine, $returnToStation)
                ->with('error', 'Mesin tidak dapat dihapus karena sudah memiliki riwayat Check Up.');
        }

        if ($machine->components()->exists()) {
            return $this->machineRedirect($machine, $returnToStation)
                ->with('error', 'Mesin tidak dapat dihapus karena masih memiliki data komponen.');
        }

        $machine->delete();

        return $this->machineRedirect($machine, $returnToStation)
            ->with('status', 'Mesin berhasil dihapus.');
    }

    private function machineRedirect(Machine $machine, bool $returnToStation): RedirectResponse
    {
        return $returnToStation
            ? redirect()->route('stations.show', $machine->station)
            : redirect()->route('machines.index');
    }
}

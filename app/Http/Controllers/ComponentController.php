<?php

namespace App\Http\Controllers;

use App\Models\Component;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ComponentController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'station_id' => ['nullable', 'integer', 'exists:stations,id'],
            'machine_id' => ['nullable', 'integer', 'exists:machines,id'],
        ]);

        $stations = Station::with(['machines' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();
        $components = Component::with(['machine.station', 'machine.latestHmLog'])
            ->withCount('replacements')
            ->when(
                $validated['search'] ?? null,
                fn ($query, $search) => $query->where('name', 'like', "%{$search}%")
            )
            ->when(
                $validated['station_id'] ?? null,
                fn ($query, $stationId) => $query->whereHas(
                    'machine',
                    fn ($machineQuery) => $machineQuery->where('station_id', $stationId)
                )
            )
            ->when(
                $validated['machine_id'] ?? null,
                fn ($query, $machineId) => $query->where('machine_id', $machineId)
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $allComponents = Component::with('machine.latestHmLog')->get();
        $componentCount = $allComponents->count();
        $normalCount = $allComponents->where('status', 'Normal')->count();
        $nearLimitCount = $allComponents->where('status', 'Mendekati Batas')->count();
        $overLimitCount = $allComponents->where('status', 'Lewat Batas')->count();

        return view('components.index', compact(
            'stations',
            'components',
            'componentCount',
            'normalCount',
            'nearLimitCount',
            'overLimitCount'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateComponent($request);

        Component::create($validated);

        return redirect()->route('components.index')->with('status', 'Komponen berhasil ditambahkan.');
    }

    public function show(Component $component)
    {
        $component->load([
            'machine.station',
            'machine.latestHmLog',
            'replacements' => fn ($query) => $query->latest('removed_at'),
        ]);

        return view('components.show', ['part' => $component]);
    }

    public function edit(Component $component)
    {
        $component->load('machine.station');
        $stations = Station::with(['machines' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();

        return view('components.edit', [
            'part' => $component,
            'stations' => $stations,
        ]);
    }

    public function update(Request $request, Component $component)
    {
        $validated = $this->validateComponent($request);

        if ($component->replacements()->exists()
            && (int) $validated['machine_id'] !== (int) $component->machine_id) {
            throw ValidationException::withMessages([
                'machine_id' => 'Mesin tidak dapat diubah karena komponen sudah memiliki riwayat pergantian.',
            ]);
        }

        $component->update($validated);

        return redirect()->route('components.index')->with('status', 'Data komponen berhasil diperbarui.');
    }

    public function destroy(Component $component)
    {
        if ($component->replacements()->exists()) {
            return redirect()
                ->route('components.index')
                ->with('error', 'Komponen tidak dapat dihapus karena sudah memiliki riwayat pergantian.');
        }

        $component->delete();

        return redirect()->route('components.index')->with('status', 'Komponen berhasil dihapus.');
    }

    private function validateComponent(Request $request): array
    {
        $validated = $request->validate([
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'machine_id' => [
                'required',
                'integer',
                Rule::exists('machines', 'id')->where('station_id', $request->input('station_id')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'installed_at' => ['nullable', 'date', 'before_or_equal:today'],
            'hm_installed' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'target_lifetime_hm' => ['nullable', 'numeric', 'gt:0', 'max:9999999999.99'],
            'target_lifetime_months' => ['nullable', 'integer', 'min:1', 'max:1200'],
        ]);

        $lifetimeFields = [
            'installed_at',
            'hm_installed',
            'target_lifetime_hm',
            'target_lifetime_months',
        ];
        $providedLifetimeFields = array_filter(
            $lifetimeFields,
            fn ($field) => ($validated[$field] ?? null) !== null && $validated[$field] !== ''
        );

        if ($providedLifetimeFields !== [] && count($providedLifetimeFields) !== count($lifetimeFields)) {
            throw ValidationException::withMessages([
                'installed_at' => 'Lengkapi tanggal pemasangan dan seluruh target lifetime, atau kosongkan semuanya.',
            ]);
        }

        unset($validated['station_id']);

        return $validated;
    }
}

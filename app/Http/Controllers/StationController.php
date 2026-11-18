<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::withCount('machines')->orderBy('name')->get();

        return view('stations.index', compact('stations'));
    }

    public function show(Station $station)
    {
        $station->load([
            'machines' => fn ($query) => $query->withCount('hmLogs')->orderBy('name'),
        ]);

        return view('stations.show', compact('station'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:stations,name'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        Station::create($validated);

        return redirect()->route('stations.index')->with('status', 'Stasiun berhasil ditambahkan.');
    }

    public function update(Request $request, Station $station)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('stations', 'name')->ignore($station->id),
            ],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $station->update($validated);

        return redirect()
            ->route('stations.show', $station)
            ->with('status', 'Data stasiun berhasil diperbarui.');
    }

    public function destroy(Station $station)
    {
        if ($station->machines()->exists()) {
            return redirect()
                ->route('stations.index')
                ->with('error', 'Stasiun tidak dapat dihapus karena masih memiliki mesin.');
        }

        $station->delete();

        return redirect()
            ->route('stations.index')
            ->with('status', 'Stasiun berhasil dihapus.');
    }
}

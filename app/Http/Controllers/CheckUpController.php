<?php

namespace App\Http\Controllers;

use App\Exports\CheckUpExport;
use App\Models\CheckUp;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class CheckUpController extends Controller
{
    public function index()
    {
        $stations = Station::with(['machines' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get();
        $checkUps = CheckUp::with(['station', 'machine', 'legacyStation', 'legacyMachine'])
            ->latest('tanggal')
            ->paginate(15);

        return view('check-ups.index', compact('stations', 'checkUps'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'nama_teknisi' => ['required', 'string', 'max:255'],
            'machine_id' => [
                'required',
                'integer',
                Rule::exists('machines', 'id')->where('station_id', $request->input('station_id')),
            ],
            'tanggal' => ['required', 'date'],
            'keterangan' => ['nullable', 'string', 'max:5000'],
            'hm_perbaikan' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        CheckUp::create([
            'station_id' => $validated['station_id'],
            'machine_id' => $validated['machine_id'],
            'nama_teknisi' => $validated['nama_teknisi'],
            'tanggal' => $validated['tanggal'],
            'keterangan' => $validated['keterangan'] ?? null,
            'hm_perbaikan' => $validated['hm_perbaikan'],
        ]);

        return redirect()
            ->route('check-ups.index')
            ->with('status', 'Data check up berhasil disimpan.');
    }

    public function export()
    {
        return Excel::download(new CheckUpExport(), 'riwayat-check-up.xlsx');
    }
}

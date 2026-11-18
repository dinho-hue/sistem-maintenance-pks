<?php

namespace App\Http\Controllers;

use App\Models\HmLog;
use App\Models\Machine;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HmLogController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'station_id' => ['nullable', 'integer', 'exists:stations,id'],
            'brand_type' => ['nullable', 'string', 'max:255'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();
        $brandType = trim($validated['brand_type'] ?? '');
        $stations = Station::orderBy('name')->get();
        $station = null;

        if (! empty($validated['station_id'])) {
            $station = Station::with([
                'machines' => fn ($query) => $query
                    ->when($brandType !== '', fn ($query) => $query->where('brand_type', 'like', "%{$brandType}%"))
                    ->orderBy('name'),
                'machines.hmLogs' => fn ($query) => $query->whereDate('date', $date),
            ])->findOrFail($validated['station_id']);
        }

        $isDashboard = $request->routeIs('dashboard');
        $machineCount = $isDashboard ? Machine::count() : null;
        $stationCount = $isDashboard ? Station::count() : null;
        $todayLogCount = $isDashboard
            ? HmLog::whereDate('date', now()->toDateString())->count()
            : null;
        $monthHmTotal = $isDashboard
            ? HmLog::whereYear('date', now()->year)
                ->whereMonth('date', now()->month)
                ->sum('hm_terpakai')
            : null;

        return view('hm.input', compact(
            'stations',
            'station',
            'date',
            'brandType',
            'isDashboard',
            'machineCount',
            'stationCount',
            'todayLogCount',
            'monthHmTotal'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'station_id' => ['required', 'integer', 'exists:stations,id'],
            'readings' => ['required', 'array', 'min:1'],
            'readings.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
        ]);

        $readings = collect($validated['readings'])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();

        if ($readings === []) {
            throw ValidationException::withMessages([
                'readings' => 'Isi setidaknya satu nilai HM Reading.',
            ]);
        }

        $station = Station::with('machines')->findOrFail($validated['station_id']);
        $machineIds = $station->machines->modelKeys();

        if (array_diff(array_map('intval', array_keys($readings)), $machineIds)) {
            throw ValidationException::withMessages([
                'readings' => 'Mesin yang dikirim tidak termasuk station terpilih.',
            ]);
        }

        DB::transaction(function () use ($validated, $readings, $station) {
            foreach ($readings as $machineId => $rawReading) {
                $machine = Machine::where('station_id', $station->id)
                    ->whereKey($machineId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $reading = round((float) $rawReading, 2);
                $date = $validated['date'];

                $previous = HmLog::where('machine_id', $machine->id)
                    ->whereDate('date', '<', $date)
                    ->orderByDesc('date')
                    ->lockForUpdate()
                    ->first();

                $next = HmLog::where('machine_id', $machine->id)
                    ->whereDate('date', '>', $date)
                    ->orderBy('date')
                    ->lockForUpdate()
                    ->first();

                $previousReading = $previous
                    ? (float) $previous->hm_reading
                    : (float) $machine->hm_awal;

                if ($reading < $previousReading) {
                    throw ValidationException::withMessages([
                        "readings.$machineId" =>
                            'Pembacaan HM tidak boleh lebih kecil dari pembacaan sebelumnya atau HM Awal.',
                    ]);
                }

                if ($next && (float) $next->hm_reading < $reading) {
                    throw ValidationException::withMessages([
                        "readings.$machineId" =>
                            'Pembacaan HM melebihi pembacaan berikutnya yang sudah tersimpan.',
                    ]);
                }

                $log = HmLog::firstOrNew([
                    'machine_id' => $machine->id,
                    'date' => $date,
                ]);
                $log->hm_reading = $reading;
                $log->hm_terpakai = round($reading - $previousReading, 2);
                $log->save();

                if ($next) {
                    $next->hm_terpakai = round(
                        (float) $next->hm_reading - $reading,
                        2
                    );
                    $next->save();
                }
            }
        });

        return redirect()
            ->route('hm.input', [
                'station_id' => $station->id,
                'date' => $validated['date'],
            ])
            ->with('status', 'Data HM berhasil disimpan.');
    }
}

<?php

namespace App\Exports;

use App\Models\Machine;
use App\Models\Station;
use Maatwebsite\Excel\Concerns\Export;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HmReportExport implements Export, WithMultipleSheets
{
    public function __construct(
        private readonly int $year,
        private readonly int $month
    ) {}

    public function sheets(): array
    {
        $machines = Machine::with([
            'hmLogs' => fn ($query) => $query
                ->whereYear('date', $this->year)
                ->whereMonth('date', $this->month)
                ->orderBy('date'),
        ])->orderBy('station_id')->orderBy('name')->get();

        $sheets = [
            new MonthlySummarySheet(
                $machines,
                $this->year,
                $this->month,
                'Rekap Bulanan'
            ),
        ];

        $machinesByStation = $machines->groupBy('station_id');
        $usedTitles = ['rekap bulanan' => true];

        foreach (Station::orderBy('name')->get() as $station) {
            $baseTitle = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $station->name) ?: 'Station';
            $baseTitle = trim(substr($baseTitle, 0, 31)) ?: 'Station';
            $title = $baseTitle;
            $suffixNumber = 2;

            while (isset($usedTitles[strtolower($title)])) {
                $suffix = ' '.$suffixNumber++;
                $title = substr($baseTitle, 0, 31 - strlen($suffix)).$suffix;
            }

            $usedTitles[strtolower($title)] = true;
            $sheets[] = new StationHmSheet(
                $machinesByStation->get($station->id, new Collection()),
                $this->year,
                $this->month,
                $title
            );
        }

        return $sheets;
    }
}

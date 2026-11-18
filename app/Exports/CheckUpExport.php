<?php

namespace App\Exports;

use App\Models\Machine;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CheckUpExport implements Export, WithMultipleSheets
{
    public function sheets(): array
    {
        $machines = Machine::with([
            'station',
            'checkUps' => fn ($query) => $query
                ->orderBy('tanggal')
                ->orderBy('id'),
        ])->orderBy('station_id')->orderBy('name')->get();

        $sheets = [];
        $usedTitles = [];

        foreach ($machines as $machine) {
            $baseTitle = $this->cleanTitle($machine->name);

            if (isset($usedTitles[strtolower(mb_substr($baseTitle, 0, 31))])) {
                $baseTitle = $this->cleanTitle($machine->name.' - '.$machine->station->name);
            }

            $title = $this->uniqueTitle($baseTitle, $usedTitles);
            $usedTitles[strtolower($title)] = true;
            $sheets[] = new CheckUpMachineSheet($machine, $title);
        }

        return $sheets;
    }

    private function cleanTitle(string $title): string
    {
        $title = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $title) ?? '';
        $title = trim($title, " \t\n\r\0\x0B'");

        return $title !== '' ? $title : 'Mesin';
    }

    /**
     * @param  array<string, bool>  $usedTitles
     */
    private function uniqueTitle(string $baseTitle, array $usedTitles): string
    {
        $title = mb_substr($baseTitle, 0, 31);
        $suffixNumber = 2;

        while (isset($usedTitles[strtolower($title)])) {
            $suffix = ' '.$suffixNumber++;
            $title = mb_substr($baseTitle, 0, 31 - mb_strlen($suffix)).$suffix;
        }

        return $title;
    }
}

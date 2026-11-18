<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

abstract class HmMonthlySheet implements
    Export,
    FromCollection,
    WithHeadings,
    WithMapping,
    WithTitle,
    WithEvents
{
    public function __construct(
        protected Collection $machines,
        protected int $year,
        protected int $month,
        protected string $sheetTitle
    ) {}

    public function collection(): Collection
    {
        return $this->machines;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function headings(): array
    {
        return [
            array_merge(
                ['Unit Mesin', 'PIC', 'HM Mesin'],
                ['HM Reading'],
                array_fill(0, 30, null),
                ['HM Terpakai'],
                array_fill(0, 30, null),
                ['Total HM Terpakai']
            ),
            array_merge(['', '', ''], range(1, 31), range(1, 31), ['']),
        ];
    }

    public function map($machine): array
    {
        $logsByDay = $machine->hmLogs->keyBy(fn ($log) => $log->date->day);
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        $readings = [];
        $used = [];

        for ($day = 1; $day <= 31; $day++) {
            $log = $day <= $daysInMonth ? $logsByDay->get($day) : null;
            $readings[] = $log ? (float) $log->hm_reading : null;
            $used[] = $log ? (float) $log->hm_terpakai : null;
        }

        return array_merge(
            [$machine->name, $machine->pic, (float) $machine->hm_awal],
            $readings,
            $used,
            [round(array_sum($used), 2)]
        );
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:B2');
                $sheet->mergeCells('C1:C2');
                $sheet->mergeCells('D1:AH1');
                $sheet->mergeCells('AI1:BM1');
                $sheet->mergeCells('BN1:BN2');
                $sheet->getStyle('A1:BN2')->getFont()->setBold(true);
                $sheet->getStyle('A1:BN2')->getAlignment()
                    ->setHorizontal('center')
                    ->setVertical('center')
                    ->setWrapText(true);
                $sheet->freezePane('D3');
                $sheet->setAutoFilter('A2:BN2');
            },
        ];
    }
}

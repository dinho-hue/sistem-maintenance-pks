<?php

namespace App\Exports;

use App\Models\Machine;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class CheckUpMachineSheet implements
    Export,
    FromCollection,
    WithColumnWidths,
    WithCustomStartCell,
    WithEvents,
    WithHeadings,
    WithMapping,
    WithTitle
{
    private int $rowNumber = 0;

    public function __construct(
        private readonly Machine $machine,
        private readonly string $sheetTitle
    ) {}

    public function collection(): Collection
    {
        return $this->machine->checkUps;
    }

    public function startCell(): string
    {
        return 'A3';
    }

    public function headings(): array
    {
        return ['NO', 'TANGGAL', 'DESCRIPTION', 'HM', 'PIC'];
    }

    public function map($checkUp): array
    {
        return [
            ++$this->rowNumber,
            $checkUp->tanggal->format('d-M-y'),
            $checkUp->keterangan,
            (float) $checkUp->hm_perbaikan,
            $checkUp->nama_teknisi,
        ];
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 20,
            'C' => 55,
            'D' => 18,
            'E' => 30,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(3, $sheet->getHighestRow());

                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'RIWAYAT MESIN '.$this->machine->name);
                $sheet->getStyle("A1:E{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle('thin');
                $sheet->getStyle("A1:E{$lastRow}")->getAlignment()
                    ->setVertical('center')
                    ->setWrapText(true);
                $sheet->getStyle('A1:E1')->getFont()->setBold(true);
                $sheet->getStyle('A1:E1')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A3:E3')->getFont()->setBold(true);
                $sheet->getStyle('A3:E3')->getAlignment()->setHorizontal('center');
                $sheet->getStyle("A4:B{$lastRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("D4:D{$lastRow}")->getAlignment()->setHorizontal('center');
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getRowDimension(2)->setRowHeight(8);
                $sheet->getRowDimension(3)->setRowHeight(26);

                for ($row = 4; $row <= $lastRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(42);
                }

                $sheet->freezePane('A4');
                $sheet->setAutoFilter("A3:E{$lastRow}");
            },
        ];
    }
}

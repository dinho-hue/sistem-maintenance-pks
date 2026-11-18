<?php

namespace App\Exports;

use App\Models\ComponentReplacement;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Export;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ComponentReplacementExport implements Export, FromQuery, WithColumnWidths, WithCustomStartCell, WithEvents, WithHeadings, WithMapping, WithTitle
{
    private int $rowNumber = 0;

    public function __construct(private readonly array $filters = []) {}

    public function query(): Builder
    {
        return ComponentReplacement::query()
            ->with('component.machine.station')
            ->when(
                $this->filters['station_id'] ?? null,
                fn ($query, $stationId) => $query->whereHas(
                    'component.machine',
                    fn ($machineQuery) => $machineQuery->where('station_id', $stationId)
                )
            )
            ->when(
                $this->filters['machine_id'] ?? null,
                fn ($query, $machineId) => $query->whereHas(
                    'component',
                    fn ($componentQuery) => $componentQuery->where('machine_id', $machineId)
                )
            )
            ->when(
                $this->filters['component_id'] ?? null,
                fn ($query, $componentId) => $query->where('component_id', $componentId)
            )
            ->when(
                $this->filters['date_from'] ?? null,
                fn ($query, $date) => $query->whereDate('removed_at', '>=', $date)
            )
            ->when(
                $this->filters['date_to'] ?? null,
                fn ($query, $date) => $query->whereDate('removed_at', '<=', $date)
            )
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('old_name', 'like', "%{$search}%")
                        ->orWhere('new_name', 'like', "%{$search}%");
                });
            })
            ->latest('removed_at');
    }

    public function headings(): array
    {
        return [
            'NO',
            'KOMPONEN LAMA',
            'KOMPONEN BARU',
            'STASIUN',
            'MESIN',
            'TANGGAL PASANG',
            'TANGGAL LEPAS',
            'HM PASANG',
            'HM LEPAS',
            'PEMAKAIAN (HM)',
            'ALASAN',
            'KETERANGAN',
        ];
    }

    public function startCell(): string
    {
        return 'A4';
    }

    public function map($replacement): array
    {
        return [
            ++$this->rowNumber,
            $replacement->old_name,
            $replacement->new_name,
            $replacement->component->machine->station->name,
            $replacement->component->machine->name,
            $replacement->installed_at ? ExcelDate::dateTimeToExcel($replacement->installed_at) : null,
            ExcelDate::dateTimeToExcel($replacement->removed_at),
            $replacement->hm_installed === null ? null : (float) $replacement->hm_installed,
            (float) $replacement->hm_removed,
            $replacement->hm_installed === null
                ? null
                : max(0, (float) $replacement->hm_removed - (float) $replacement->hm_installed),
            $replacement->reason,
            $replacement->notes,
        ];
    }

    public function title(): string
    {
        return 'Riwayat Komponen';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 24,
            'C' => 24,
            'D' => 20,
            'E' => 30,
            'F' => 16,
            'G' => 16,
            'H' => 16,
            'I' => 16,
            'J' => 18,
            'K' => 24,
            'L' => 48,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = max(4, $sheet->getHighestRow());

                $sheet->mergeCells('A1:L1');
                $sheet->setCellValue('A1', 'RIWAYAT PERGANTIAN KOMPONEN');
                $sheet->mergeCells('A2:L2');
                $sheet->setCellValue('A2', 'Diekspor pada '.now()->format('d/m/Y H:i'));
                $sheet->getStyle("A1:L{$lastRow}")->getAlignment()
                    ->setVertical('center')
                    ->setWrapText(true);
                $sheet->getStyle("A4:L{$lastRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle('thin')
                    ->getColor()
                    ->setARGB('FFD9E2EF');

                $sheet->getStyle('A1:L1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF0F2A47']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                ]);
                $sheet->getStyle('A2:L2')->applyFromArray([
                    'font' => ['italic' => true, 'color' => ['argb' => 'FF64748B']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFF1F5F9']],
                ]);
                $sheet->getStyle('A4:L4')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF1565C0']],
                    'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                ]);
                $sheet->getStyle("F5:G{$lastRow}")->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                $sheet->getStyle("H5:J{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("A5:A{$lastRow}")->getAlignment()->setHorizontal('center');
                $sheet->getStyle("F5:J{$lastRow}")->getAlignment()->setHorizontal('center');
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(4)->setRowHeight(30);

                $sheet->freezePane('A5');
                $sheet->setAutoFilter("A4:L{$lastRow}");
            },
        ];
    }
}

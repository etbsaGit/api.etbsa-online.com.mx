<?php

namespace App\Exports;

use App\Models\Intranet\InvModel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ModelsGroupedReportExport implements FromCollection, WithHeadings, WithEvents
{
    public function headings(): array
    {
        // Solo 1 columna visible (A)
        return ['Reporte'];
    }

    public function collection(): Collection
    {
        $models = InvModel::with('invConfigurations.invCategory')->get();

        $rows = collect();

        foreach ($models as $model) {
            // MODELO
            $rows->push([
                'Reporte' => "MODELO: {$model->name} | CÓDIGO: {$model->code} | DESCRIPCIÓN: {$model->description}",
            ]);

            // Agrupar configs por categoría (nombre)
            $byCategory = $model->invConfigurations
                ->groupBy(fn ($c) => $c->invCategory?->name ?? 'Sin categoría');

            foreach ($byCategory as $categoryName => $configs) {
                // CATEGORÍA (indent 1)
                $rows->push([
                    'Reporte' => "CATEGORÍA: {$categoryName}",
                ]);

                // CONFIGS (indent 2)
                foreach ($configs as $conf) {
                    $rows->push([
                        'Reporte' => "• {$conf->code} - {$conf->name}",
                    ]);
                }
            }

            // Línea en blanco entre modelos
            $rows->push(['Reporte' => '']);
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('A')->setAutoSize(true);

                $highestRow = $sheet->getHighestRow();

                // Estilo encabezado
                $sheet->getStyle('A1')->getFont()->setBold(true);

                // Aplicar estilos por “tipo” leyendo el texto
                for ($row = 2; $row <= $highestRow; $row++) {
                    $value = (string) $sheet->getCell("A{$row}")->getValue();

                    if (str_starts_with($value, 'MODELO:')) {
                        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
                    }

                    if (str_starts_with($value, 'CATEGORÍA:')) {
                        $sheet->getStyle("A{$row}")->getAlignment()->setIndent(1);
                        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    }

                    if (str_starts_with($value, '• ')) {
                        $sheet->getStyle("A{$row}")->getAlignment()->setIndent(2);
                    }
                }

                // (Opcional) wrap para que no se corte texto largo
                $sheet->getStyle("A1:A{$highestRow}")->getAlignment()->setWrapText(true);
            }
        ];
    }
}

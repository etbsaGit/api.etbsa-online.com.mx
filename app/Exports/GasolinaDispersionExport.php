<?php

namespace App\Exports;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class GasolinaDispersionExport
{
    protected $dispersiones;
    protected string $periodoLabel;

    public function __construct($dispersiones, string $periodoLabel = 'General')
    {
        $this->dispersiones = $dispersiones;
        $this->periodoLabel = $periodoLabel;
    }

    public function generateRawXlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Corporativo ETBSA')
            ->setTitle('Reporte de Dispersiones y Rutas de Gasolina')
            ->setSubject('Dispersiones y Rutas de Gasolina');

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dispersiones de Gasolina');
        $sheet->setShowGridLines(true);

        // Encabezado del Reporte
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'REPORTE DE DISPERSIONES DE GASOLINA Y RUTAS');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Periodo: ' . $this->periodoLabel . '   |   Generado: ' . Carbon::now('America/Mexico_City')->format('d/m/Y h:i A'));
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '334155']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        // Cabeceras de Columnas
        $headers = [
            'A4' => 'Folio',
            'B4' => 'Fecha de Solicitud',
            'C4' => 'Unidad',
            'D4' => 'Usuario que Solicitó',
            'E4' => 'Litros Dispersados',
            'F4' => 'Estatus de la Solicitud',
            'G4' => 'Link de la Ruta (Google Maps)',
        ];

        foreach ($headers as $cell => $label) {
            $sheet->setCellValue($cell, $label);
        }

        $sheet->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
            ],
        ]);
        $sheet->getRowDimension(4)->setRowHeight(22);

        $rowIdx = 5;
        $totalLtsDispersados = 0;

        foreach ($this->dispersiones as $item) {
            $folio = '#' . $item->id;

            $fechaSolicitud = $item->created_at
                ? Carbon::parse($item->created_at)->format('d/m/Y h:i A')
                : '-';

            $placas = $item->vehicle?->placas ?? 'S/P';
            $nombreUnidad = $item->vehicle?->nombre_unidad ?? '';
            $serie = $item->vehicle?->serie ?? '';
            $unidadPartes = array_filter([
                $placas,
                $nombreUnidad && $nombreUnidad !== 'Unidad' ? "({$nombreUnidad})" : '',
                $serie ? "- {$serie}" : '',
            ]);
            $unidadLabel = implode(' ', $unidadPartes);

            $solicitante = $item->solicitante?->nombreCompleto
                ?? ($item->solicitante ? trim($item->solicitante->nombre . ' ' . $item->solicitante->apellido_paterno . ' ' . $item->solicitante->apellido_materno) : null)
                ?? $item->registrador?->empleado?->nombreCompleto
                ?? $item->registrador?->name
                ?? 'No especificado';

            $ltsDispersados = $item->lts_dispersados !== null
                ? (int) $item->lts_dispersados
                : 0;
            $totalLtsDispersados += $ltsDispersados;

            $estatusNombre = $item->estatus?->nombre ?? 'Sin estatus';
            $rutaUrl = !empty($item->recorrido_url) ? trim($item->recorrido_url) : 'Sin ruta adjunta';

            $sheet->setCellValue("A{$rowIdx}", $folio);
            $sheet->setCellValue("B{$rowIdx}", $fechaSolicitud);
            $sheet->setCellValue("C{$rowIdx}", $unidadLabel);
            $sheet->setCellValue("D{$rowIdx}", $solicitante);
            $sheet->setCellValue("E{$rowIdx}", $item->lts_dispersados !== null ? $ltsDispersados : 'Pendiente');
            $sheet->setCellValue("F{$rowIdx}", $estatusNombre);
            $sheet->setCellValue("G{$rowIdx}", $rutaUrl);

            if (!empty($item->recorrido_url) && (str_starts_with($rutaUrl, 'http://') || str_starts_with($rutaUrl, 'https://'))) {
                $sheet->getCell("G{$rowIdx}")->getHyperlink()->setUrl($rutaUrl);
                $sheet->getStyle("G{$rowIdx}")->applyFromArray([
                    'font' => ['color' => ['rgb' => '0284C7'], 'underline' => true],
                ]);
            }

            // Zebra striping
            if ($rowIdx % 2 === 0) {
                $sheet->getStyle("A{$rowIdx}:G{$rowIdx}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('F8FAFC');
            }

            $rowIdx++;
        }

        $lastDataRow = max(5, $rowIdx - 1);

        if ($rowIdx > 5) {
            $sheet->getStyle("A5:G{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E2E8F0']],
                ],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A5:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E5:F{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Fila de Totales
            $sheet->setCellValue("D{$rowIdx}", 'TOTAL LITROS DISPERSADOS:');
            $sheet->setCellValue("E{$rowIdx}", $totalLtsDispersados);
            $sheet->getStyle("A{$rowIdx}:G{$rowIdx}")->applyFromArray([
                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '0F172A']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
                'borders' => [
                    'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '94A3B8']],
                    'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '94A3B8']],
                ],
            ]);
            $sheet->getStyle("D{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        } else {
            $sheet->mergeCells('A5:G5');
            $sheet->setCellValue('A5', 'No se encontraron dispersiones registradas en el periodo seleccionado.');
            $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto-ajustar ancho de columnas
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setAutoFilter("A4:G{$lastDataRow}");

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }
}

<?php

namespace App\Exports;

use App\Models\Vehicle;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

class VehicleServicesExport
{
    protected Vehicle $vehicle;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }

    /**
     * Genera el archivo binario XLSX con tablas y gráficas nativas interactivas de Excel.
     */
    public function generateRawXlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Corporativo ETBSA')
            ->setTitle('Dashboard e Historial de Servicios - ' . ($this->vehicle->placas ?? 'Unidad'))
            ->setSubject('Historial de Servicios de Unidad');

        // Preparar datos planos de servicios ordenados por fecha descendente
        $flatServices = [];
        $ordenes = $this->vehicle->ordenesServicio ?? collect();

        foreach ($ordenes as $orden) {
            $detalles = $orden->detalles ?? $orden->detallesServicio ?? collect();
            foreach ($detalles as $det) {
                $servicio = $det->servicio;
                $tipoServicio = $servicio?->tipoServicio;

                $fechaStr = $orden->fecha_elaboracion
                    ? Carbon::parse($orden->fecha_elaboracion)->format('d/m/Y')
                    : 'Sin fecha';

                $periodoStr = $orden->fecha_elaboracion
                    ? Carbon::parse($orden->fecha_elaboracion)->format('Y-m')
                    : 'Sin fecha';

                $flatServices[] = [
                    'fecha' => $fechaStr,
                    'periodo' => $periodoStr,
                    'orden_compra' => $orden->orden_compra ?? '-',
                    'autorizacion_compras' => $orden->autorizacion_compras ?? '-',
                    'factura' => $orden->factura ?? '-',
                    'tipo_servicio' => $tipoServicio?->nombre ?? 'Sin tipo',
                    'servicio' => $servicio?->nombre ?? 'Servicio general',
                    'descripcion' => $det->descripcion ?? '',
                    'km_evento' => $det->km_evento !== null ? (int) $det->km_evento : null,
                    'proveedor' => $orden->proveedor?->nombre ?? 'No especificado',
                    'sucursal_cargo' => $orden->sucursalCargo?->nombre ?? '-',
                    'depto_cargo' => $orden->departamentoCargo?->nombre ?? '-',
                    'solicitante' => $orden->solicitante?->nombreCompleto ?? '-',
                    'subtotal' => (float) ($det->subtotal ?? 0),
                    'iva' => (float) ($det->iva ?? 0),
                    'total' => (float) ($det->total ?? 0),
                ];
            }
        }

        // =========================================================================
        // HOJA 1: DASHBOARD Y GRÁFICAS INTERACTIVAS
        // =========================================================================
        $dashSheet = $spreadsheet->getActiveSheet();
        $dashSheet->setTitle('Dashboard');
        $dashSheet->setShowGridLines(true);

        // =========================================================================
        // HOJA 2: HISTORIAL DE SERVICIOS (TABLA INTERACTIVA)
        // =========================================================================
        $tableSheet = $spreadsheet->createSheet();
        $tableSheet->setTitle('Historial de Servicios');
        $tableSheet->setShowGridLines(true);

        $this->buildTableSheet($tableSheet, $flatServices);
        $this->buildDashboardSheet($dashSheet, $flatServices, $ordenes->count());

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->setPreCalculateFormulas(true);

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    protected function buildTableSheet($sheet, array $services): void
    {
        $placas = $this->vehicle->placas ?? 'S/P';
        $serie = $this->vehicle->serie ?? 'Sin serie';
        $tipoVehiculo = $this->vehicle->estatus?->nombre ?? 'No definido';
        $sucursal = $this->vehicle->sucursal?->nombre ?? 'No asignada';

        // Banner superior
        $sheet->mergeCells('A1:O1');
        $sheet->setCellValue('A1', "HISTORIAL DETALLADO DE SERVICIOS - UNIDAD: {$placas} | SERIE: {$serie}");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->mergeCells('A2:O2');
        $sheet->setCellValue(
            'A2',
            "Tipo: {$tipoVehiculo} | Sucursal: {$sucursal} | Nota: Filtra cualquier columna de la tabla y los totales superiores + la gráfica interactiva se actualizarán automáticamente."
        );
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '37474F']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(20);

        $startRow = 6;
        $dataStartRow = 7;
        $countServices = count($services);
        $lastDataRow = max($dataStartRow, $dataStartRow + $countServices - 1);

        // Barra de resumen dinámico con SUBTOTAL (responde en vivo a los filtros de la tabla)
        $sheet->setCellValue('A4', 'Servicios Visibles:');
        $sheet->setCellValue('B4', $countServices > 0 ? "=SUBTOTAL(103,F{$dataStartRow}:F{$lastDataRow})" : 0);

        $sheet->setCellValue('D4', 'Mayor KM Filtrado:');
        $sheet->setCellValue('E4', $countServices > 0 ? "=SUBTOTAL(104,H{$dataStartRow}:H{$lastDataRow})" : 0);
        $sheet->getStyle('E4')->getNumberFormat()->setFormatCode('#,##0 "km"');

        $sheet->setCellValue('J4', 'Subtotal Filtrado:');
        $sheet->setCellValue('K4', $countServices > 0 ? "=SUBTOTAL(109,M{$dataStartRow}:M{$lastDataRow})" : 0);
        $sheet->getStyle('K4')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->setCellValue('L4', 'IVA Filtrado:');
        $sheet->setCellValue('M4', $countServices > 0 ? "=SUBTOTAL(109,N{$dataStartRow}:N{$lastDataRow})" : 0);
        $sheet->getStyle('M4')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->setCellValue('N4', 'TOTAL FILTRADO:');
        $sheet->setCellValue('O4', $countServices > 0 ? "=SUBTOTAL(109,O{$dataStartRow}:O{$lastDataRow})" : 0);
        $sheet->getStyle('O4')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->getStyle('A4:O4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F8E9']],
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AED581']],
            ],
        ]);
        $sheet->getStyle('N4:O4')->getFont()->getColor()->setRGB('1B5E20');
        $sheet->getRowDimension(4)->setRowHeight(22);

        // Encabezados de la Tabla en fila 6
        $headers = [
            'A' => 'Fecha',
            'B' => 'Orden de Compra',
            'C' => 'Aut. Compras',
            'D' => 'Factura',
            'E' => 'Tipo de Servicio',
            'F' => 'Servicio',
            'G' => 'Descripción / Detalle',
            'H' => 'Kilometraje (KM)',
            'I' => 'Proveedor',
            'J' => 'Sucursal Cargo',
            'K' => 'Depto. Cargo',
            'L' => 'Solicitante',
            'M' => 'Subtotal ($)',
            'N' => 'IVA ($)',
            'O' => 'Total Invertido ($)',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}{$startRow}", $title);
        }

        $sheet->getStyle("A{$startRow}:O{$startRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(24);

        // Llenar filas de datos
        if ($countServices === 0) {
            $sheet->setCellValue("A{$dataStartRow}", 'Sin registros');
            $sheet->setCellValue("E{$dataStartRow}", 'N/A');
            $sheet->setCellValue("F{$dataStartRow}", 'Sin servicios registrados');
            $sheet->setCellValue("H{$dataStartRow}", 0);
            $sheet->setCellValue("M{$dataStartRow}", 0);
            $sheet->setCellValue("N{$dataStartRow}", 0);
            $sheet->setCellValue("O{$dataStartRow}", 0);
        } else {
            $rowIdx = $dataStartRow;
            foreach ($services as $srv) {
                $sheet->setCellValue("A{$rowIdx}", $srv['fecha']);
                $sheet->setCellValue("B{$rowIdx}", $srv['orden_compra']);
                $sheet->setCellValue("C{$rowIdx}", $srv['autorizacion_compras']);
                $sheet->setCellValue("D{$rowIdx}", $srv['factura']);
                $sheet->setCellValue("E{$rowIdx}", $srv['tipo_servicio']);
                $sheet->setCellValue("F{$rowIdx}", $srv['servicio']);
                $sheet->setCellValue("G{$rowIdx}", $srv['descripcion']);
                if ($srv['km_evento'] !== null) {
                    $sheet->setCellValue("H{$rowIdx}", $srv['km_evento']);
                }
                $sheet->setCellValue("I{$rowIdx}", $srv['proveedor']);
                $sheet->setCellValue("J{$rowIdx}", $srv['sucursal_cargo']);
                $sheet->setCellValue("K{$rowIdx}", $srv['depto_cargo']);
                $sheet->setCellValue("L{$rowIdx}", $srv['solicitante']);
                $sheet->setCellValue("M{$rowIdx}", $srv['subtotal']);
                $sheet->setCellValue("N{$rowIdx}", $srv['iva']);
                $sheet->setCellValue("O{$rowIdx}", $srv['total']);
                $rowIdx++;
            }
        }

        // Formatos numéricos y bordes
        $sheet->getStyle("H{$dataStartRow}:H{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle("M{$dataStartRow}:O{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00');
        $sheet->getStyle("O{$dataStartRow}:O{$lastDataRow}")
            ->getFont()
            ->setBold(true);

        $sheet->getStyle("A{$startRow}:O{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Convertir rango en Tabla Nativa de Excel con AutoFilter interactivo
        $table = new Table("A{$startRow}:O{$lastDataRow}", 'TablaHistorialServicios');
        $tableStyle = new TableStyle();
        $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM2);
        $tableStyle->setShowRowStripes(true);
        $table->setStyle($tableStyle);
        $sheet->addTable($table);

        // Gráfica interactiva en la misma hoja vinculada a las filas visibles de la tabla (plotVisibleOnly = true)
        $chartMaxRow = min($lastDataRow, $dataStartRow + 49);
        $chartCount = max(1, $chartMaxRow - $dataStartRow + 1);
        $tableChartLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Historial de Servicios'!\$O\${$startRow}", null, 1),
        ];
        $tableChartCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "'Historial de Servicios'!\$F\${$dataStartRow}:\$F\${$chartMaxRow}", null, $chartCount),
        ];
        $tableChartValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "'Historial de Servicios'!\$O\${$dataStartRow}:\$O\${$chartMaxRow}", null, $chartCount),
        ];

        $tableChartSeries = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($tableChartValues) - 1),
            $tableChartLabels,
            $tableChartCategories,
            $tableChartValues
        );
        $tableChartSeries->setPlotDirection(DataSeries::DIRECTION_COL);

        $tableChartLayout = new Layout();
        $tableChartLayout->setShowVal(true);

        $tableChartPlotArea = new PlotArea($tableChartLayout, [$tableChartSeries]);
        $tableChartLegend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $tableChartTitle = new Title('Gráfica Interactiva de Servicios Filtrados ($)');

        $tableChart = new Chart(
            'chart_tabla_interactiva',
            $tableChartTitle,
            $tableChartLegend,
            $tableChartPlotArea,
            true, // plotVisibleOnly = true: se actualiza en vivo al filtrar la tabla en Excel
            DataSeries::EMPTY_AS_GAP,
            new Title('Servicio'),
            new Title('Total Invertido ($)')
        );
        $tableChart->setTopLeftPosition('Q6');
        $tableChart->setBottomRightPosition('Y25');
        $sheet->addChart($tableChart);

        // Ajustar ancho de columnas
        $widths = [
            'A' => 14,
            'B' => 16,
            'C' => 16,
            'D' => 15,
            'E' => 22,
            'F' => 26,
            'G' => 42,
            'H' => 18,
            'I' => 28,
            'J' => 18,
            'K' => 18,
            'L' => 26,
            'M' => 15,
            'N' => 14,
            'O' => 18,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        // Inmovilizar paneles debajo del encabezado de la tabla
        $sheet->freezePane("A{$dataStartRow}");
    }

    protected function buildDashboardSheet($sheet, array $services, int $totalOrdenes): void
    {
        $placas = $this->vehicle->placas ?? 'S/P';
        $serie = $this->vehicle->serie ?? 'Sin serie';
        $tipoVehiculo = $this->vehicle->estatus?->nombre ?? 'No definido';
        $sucursal = $this->vehicle->sucursal?->nombre ?? 'No asignada';
        $linea = $this->vehicle->linea?->nombre ?? 'No asignada';
        $depto = $this->vehicle->departamento?->nombre ?? 'No asignado';
        $estatusUnidad = $this->vehicle->activo == 1 ? 'ACTIVO' : 'INACTIVO';

        $historialPlacas = ($this->vehicle->historialPlacas ?? collect())
            ->pluck('placa')
            ->filter()
            ->implode(', ');
        if (empty($historialPlacas)) {
            $historialPlacas = $placas;
        }

        // 1. Encabezado del Dashboard
        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', "DASHBOARD DE SERVICIOS DE UNIDAD - PLACA: {$placas}");
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D47A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue(
            'A2',
            "Serie (VIN): {$serie}   |   Tipo: {$tipoVehiculo}   |   Sucursal: {$sucursal}   |   Línea: {$linea}   |   Depto: {$depto}   |   Estatus: {$estatusUnidad}   |   Historial Placas: {$historialPlacas}"
        );
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E293B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $countServices = count($services);
        $dataStartRow = 7;
        $lastDataRow = max($dataStartRow, $dataStartRow + $countServices - 1);

        // 2. Bloque de KPIs Ejecutivos (Filas 4 y 5) vinculados con fórmulas a 'Historial de Servicios'
        $kpiHeaders = [
            'A4:C4' => ['cell' => 'A4', 'title' => 'DINERO TOTAL INVERTIDO', 'color' => '1B5E20', 'bg' => 'E8F5E9'],
            'D4:E4' => ['cell' => 'D4', 'title' => 'SUBTOTAL + IVA', 'color' => '0D47A1', 'bg' => 'E3F2FD'],
            'F4:G4' => ['cell' => 'F4', 'title' => 'SERVICIOS REALIZADOS', 'color' => '4A148C', 'bg' => 'F3E5F5'],
            'H4:I4' => ['cell' => 'H4', 'title' => 'ÓRDENES DE SERVICIO', 'color' => '006064', 'bg' => 'E0F7FA'],
            'J4:K4' => ['cell' => 'J4', 'title' => 'MAYOR KM REGISTRADO', 'color' => 'E65100', 'bg' => 'FFF3E0'],
            'L4:N4' => ['cell' => 'L4', 'title' => 'ÚLTIMO SERVICIO REALIZADO', 'color' => '37474F', 'bg' => 'ECEFF1'],
        ];

        foreach ($kpiHeaders as $range => $cfg) {
            $sheet->mergeCells($range);
            $sheet->setCellValue($cfg['cell'], $cfg['title']);
            $sheet->getStyle($range)->applyFromArray([
                'font' => ['bold' => true, 'size' => 9, 'color' => ['rgb' => $cfg['color']]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $cfg['bg']]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']]],
            ]);
        }

        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', $countServices > 0 ? "='Historial de Servicios'!O4" : 0);
        $sheet->getStyle('A5')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->mergeCells('D5:E5');
        $sheet->setCellValue('D5', $countServices > 0 ? "='Historial de Servicios'!K4" : 0);
        $sheet->getStyle('D5')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->mergeCells('F5:G5');
        $sheet->setCellValue('F5', $countServices > 0 ? "='Historial de Servicios'!B4" : 0);

        $sheet->mergeCells('H5:I5');
        $sheet->setCellValue('H5', $totalOrdenes);

        $sheet->mergeCells('J5:K5');
        $sheet->setCellValue('J5', $countServices > 0 ? "='Historial de Servicios'!E4" : 0);
        $sheet->getStyle('J5')->getNumberFormat()->setFormatCode('#,##0 "km"');

        $ultimoTexto = $countServices > 0
            ? ($services[0]['fecha'] . ' - ' . $services[0]['servicio'])
            : 'Sin servicios';
        $sheet->mergeCells('L5:N5');
        $sheet->setCellValue('L5', $ultimoTexto);

        $sheet->getStyle('A5:N5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '111827']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']]],
        ]);
        $sheet->getStyle('L5:N5')->getFont()->setSize(10);
        $sheet->getStyle('A5')->getFont()->getColor()->setRGB('1B5E20');
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // =====================================================================
        // 3. TABLAS RESUMEN VINCULADAS A LAS GRÁFICAS INTERACTIVAS (Filas 25+)
        // (Colocamos las gráficas arriba en filas 7 a 23 y sus tablas fuente en 25+)
        // =====================================================================
        $summaryHeaderRow = 25;
        $summaryStartRow = 26;

        // Agrupar por Tipo de Servicio
        $byTipo = [];
        // Agrupar por Servicio específico (Top 10)
        $byServicio = [];
        // Agrupar por Periodo (Año-Mes)
        $byPeriodo = [];

        foreach ($services as $s) {
            $t = $s['tipo_servicio'];
            if (!isset($byTipo[$t])) {
                $byTipo[$t] = ['nombre' => $t, 'count' => 0, 'total' => 0.0];
            }
            $byTipo[$t]['count']++;
            $byTipo[$t]['total'] += $s['total'];

            $sv = $s['servicio'];
            if (!isset($byServicio[$sv])) {
                $byServicio[$sv] = ['nombre' => $sv, 'tipo' => $t, 'count' => 0, 'total' => 0.0, 'max_km' => 0];
            }
            $byServicio[$sv]['count']++;
            $byServicio[$sv]['total'] += $s['total'];
            if ($s['km_evento'] && $s['km_evento'] > $byServicio[$sv]['max_km']) {
                $byServicio[$sv]['max_km'] = $s['km_evento'];
            }

            $per = $s['periodo'];
            if (!isset($byPeriodo[$per])) {
                $byPeriodo[$per] = ['periodo' => $per, 'count' => 0, 'total' => 0.0];
            }
            $byPeriodo[$per]['count']++;
            $byPeriodo[$per]['total'] += $s['total'];
        }

        usort($byTipo, fn($a, $b) => $b['total'] <=> $a['total']);
        usort($byServicio, fn($a, $b) => $b['total'] <=> $a['total']);
        $topServicios = array_slice($byServicio, 0, 12);
        ksort($byPeriodo); // Orden cronológico ascendente para la gráfica de línea

        if (empty($byTipo)) {
            $byTipo[] = ['nombre' => 'Sin registros', 'count' => 0, 'total' => 0.0];
        }
        if (empty($topServicios)) {
            $topServicios[] = ['nombre' => 'Sin registros', 'tipo' => '-', 'count' => 0, 'total' => 0.0, 'max_km' => 0];
        }
        if (empty($byPeriodo)) {
            $byPeriodo[] = ['periodo' => date('Y-m'), 'count' => 0, 'total' => 0.0];
        }

        // --- TABLA RESUMEN 1: INVERSIÓN POR TIPO DE SERVICIO (Columnas A:D) ---
        $sheet->setCellValue("A{$summaryHeaderRow}", 'Tipo de Servicio');
        $sheet->setCellValue("B{$summaryHeaderRow}", 'Cant. Servicios');
        $sheet->setCellValue("C{$summaryHeaderRow}", 'Dinero Invertido ($)');
        $sheet->setCellValue("D{$summaryHeaderRow}", '% Inversión');

        $sheet->getStyle("A{$summaryHeaderRow}:D{$summaryHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
        ]);

        $rTipo = $summaryStartRow;
        foreach ($byTipo as $item) {
            $sheet->setCellValue("A{$rTipo}", $item['nombre']);
            $sheet->setCellValue("B{$rTipo}", (int) $item['count']);
            $sheet->setCellValue("C{$rTipo}", (float) $item['total']);
            $rTipo++;
        }
        $lastTipoRow = $rTipo - 1;
        for ($r = $summaryStartRow; $r <= $lastTipoRow; $r++) {
            $sheet->setCellValue("D{$r}", "=IF(SUM(\$C\${$summaryStartRow}:\$C\${$lastTipoRow})>0, C{$r}/SUM(\$C\${$summaryStartRow}:\$C\${$lastTipoRow}), 0)");
        }
        $sheet->getStyle("C{$summaryStartRow}:C{$lastTipoRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("D{$summaryStartRow}:D{$lastTipoRow}")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("A{$summaryHeaderRow}:D{$lastTipoRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']]],
        ]);

        // --- TABLA RESUMEN 2: PRINCIPALES SERVICIOS REALIZADOS (Columnas F:J) ---
        $sheet->setCellValue("F{$summaryHeaderRow}", 'Servicio Realizado');
        $sheet->setCellValue("G{$summaryHeaderRow}", 'Tipo');
        $sheet->setCellValue("H{$summaryHeaderRow}", 'Cantidad');
        $sheet->setCellValue("I{$summaryHeaderRow}", 'Dinero Invertido ($)');
        $sheet->setCellValue("J{$summaryHeaderRow}", 'Último KM');

        $sheet->getStyle("F{$summaryHeaderRow}:J{$summaryHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
        ]);

        $rServ = $summaryStartRow;
        foreach ($topServicios as $item) {
            $sheet->setCellValue("F{$rServ}", $item['nombre']);
            $sheet->setCellValue("G{$rServ}", $item['tipo']);
            $sheet->setCellValue("H{$rServ}", (int) $item['count']);
            $sheet->setCellValue("I{$rServ}", (float) $item['total']);
            $sheet->setCellValue("J{$rServ}", $item['max_km'] ?: 0);
            $rServ++;
        }
        $lastServRow = $rServ - 1;
        $sheet->getStyle("I{$summaryStartRow}:I{$lastServRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("J{$summaryStartRow}:J{$lastServRow}")->getNumberFormat()->setFormatCode('#,##0 "km"');
        $sheet->getStyle("F{$summaryHeaderRow}:J{$lastServRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']]],
        ]);

        // --- TABLA RESUMEN 3: INVERSIÓN POR PERIODO CRONOLÓGICO (Columnas L:N) ---
        $sheet->setCellValue("L{$summaryHeaderRow}", 'Periodo (Año-Mes)');
        $sheet->setCellValue("M{$summaryHeaderRow}", 'Cant. Servicios');
        $sheet->setCellValue("N{$summaryHeaderRow}", 'Dinero Invertido ($)');

        $sheet->getStyle("L{$summaryHeaderRow}:N{$summaryHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6A1B9A']],
        ]);

        $rPer = $summaryStartRow;
        foreach ($byPeriodo as $item) {
            $sheet->setCellValue("L{$rPer}", $item['periodo']);
            $sheet->setCellValue("M{$rPer}", $item['count']);
            $sheet->setCellValue("N{$rPer}", $item['total']);
            $rPer++;
        }
        $lastPerRow = $rPer - 1;
        $sheet->getStyle("N{$summaryStartRow}:N{$lastPerRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("L{$summaryHeaderRow}:N{$lastPerRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']]],
        ]);

        // =====================================================================
        // 4. GRÁFICAS NATIVAS INTERACTIVAS DE EXCEL (Filas 7 a 23)
        // =====================================================================

        // GRÁFICA 1: Distribución de Inversión por Tipo de Servicio (Doughnut / Pastel)
        $pieLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$C\${$summaryHeaderRow}", null, 1),
        ];
        $pieCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$A\${$summaryStartRow}:\$A\${$lastTipoRow}", null, count($byTipo)),
        ];
        $pieValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$C\${$summaryStartRow}:\$C\${$lastTipoRow}", null, count($byTipo)),
        ];

        $pieSeries = new DataSeries(
            DataSeries::TYPE_DOUGHNUTCHART,
            null,
            range(0, count($pieValues) - 1),
            $pieLabels,
            $pieCategories,
            $pieValues
        );

        $pieLayout = new Layout();
        $pieLayout->setShowVal(true);
        $pieLayout->setShowPercent(true);

        $piePlotArea = new PlotArea($pieLayout, [$pieSeries]);
        $pieLegend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $pieTitle = new Title('Inversión ($) por Tipo de Servicio');

        $pieChart = new Chart(
            'chart_tipo_servicio',
            $pieTitle,
            $pieLegend,
            $piePlotArea,
            true,
            DataSeries::EMPTY_AS_GAP,
            null,
            null
        );
        $pieChart->setTopLeftPosition('A7');
        $pieChart->setBottomRightPosition('E23');
        $sheet->addChart($pieChart);

        // GRÁFICA 2: Principales Servicios por Dinero Invertido (Barras / Columnas)
        $barLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$I\${$summaryHeaderRow}", null, 1),
        ];
        $barCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$F\${$summaryStartRow}:\$F\${$lastServRow}", null, count($topServicios)),
        ];
        $barValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$I\${$summaryStartRow}:\$I\${$lastServRow}", null, count($topServicios)),
        ];

        $barSeries = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($barValues) - 1),
            $barLabels,
            $barCategories,
            $barValues
        );
        $barSeries->setPlotDirection(DataSeries::DIRECTION_COL);

        $barLayout = new Layout();
        $barLayout->setShowVal(true);

        $barPlotArea = new PlotArea($barLayout, [$barSeries]);
        $barLegend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $barTitle = new Title('Dinero Invertido ($) por Servicio');

        $barChart = new Chart(
            'chart_top_servicios',
            $barTitle,
            $barLegend,
            $barPlotArea,
            true,
            DataSeries::EMPTY_AS_GAP,
            null,
            new Title('Monto Invertido (MXN)')
        );
        $barChart->setTopLeftPosition('F7');
        $barChart->setBottomRightPosition('J23');
        $sheet->addChart($barChart);

        // GRÁFICA 3: Evolución Cronológica del Gasto por Periodo (Línea)
        $lineLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$N\${$summaryHeaderRow}", null, 1),
        ];
        $lineCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$L\${$summaryStartRow}:\$L\${$lastPerRow}", null, count($byPeriodo)),
        ];
        $lineValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$N\${$summaryStartRow}:\$N\${$lastPerRow}", null, count($byPeriodo)),
        ];

        $lineSeries = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($lineValues) - 1),
            $lineLabels,
            $lineCategories,
            $lineValues,
            null,
            true,
            DataSeries::STYLE_LINEMARKER
        );

        $lineLayout = new Layout();
        $lineLayout->setShowVal(true);

        $linePlotArea = new PlotArea($lineLayout, [$lineSeries]);
        $lineLegend = new Legend(Legend::POSITION_BOTTOM, null, false);
        $lineTitle = new Title('Evolución de Inversión por Periodo');

        $lineChart = new Chart(
            'chart_evolucion_periodo',
            $lineTitle,
            $lineLegend,
            $linePlotArea,
            true,
            DataSeries::EMPTY_AS_GAP,
            new Title('Periodo (Año-Mes)'),
            new Title('Inversión ($)')
        );
        $lineChart->setTopLeftPosition('K7');
        $lineChart->setBottomRightPosition('N23');
        $sheet->addChart($lineChart);

        // Ajustar anchos de columna en Dashboard
        $dashWidths = [
            'A' => 24,
            'B' => 16,
            'C' => 20,
            'D' => 14,
            'E' => 6,
            'F' => 26,
            'G' => 18,
            'H' => 12,
            'I' => 20,
            'J' => 16,
            'K' => 6,
            'L' => 18,
            'M' => 16,
            'N' => 20,
        ];
        foreach ($dashWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
    }
}

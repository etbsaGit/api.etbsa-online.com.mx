<?php

namespace App\Exports;

use App\Models\Vehicle;
use App\Models\VehiclesOrdenesServicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Table;
use PhpOffice\PhpSpreadsheet\Worksheet\Table\TableStyle;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;

class VehiclesGeneralDashboardExport
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * Genera el archivo binario XLSX del Dashboard General de Vehículos.
     */
    public function generateRawXlsx(): string
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('Corporativo ETBSA')
            ->setTitle('Dashboard General de Vehículos y Servicios')
            ->setSubject('Reporte Ejecutivo de Flotilla y Servicios');

        [$vehicles, $ordenes, $flatServices] = $this->loadFilteredData();

        // =========================================================================
        // HOJA 1: DASHBOARD EJECUTIVO Y GRÁFICAS
        // =========================================================================
        $dashSheet = $spreadsheet->getActiveSheet();
        $dashSheet->setTitle('Dashboard');
        $dashSheet->setShowGridLines(true);

        // =========================================================================
        // HOJA 2: HISTORIAL DE SERVICIOS (TABLA INTERACTIVA)
        // =========================================================================
        $servicesSheet = $spreadsheet->createSheet();
        $servicesSheet->setTitle('Historial de Servicios');
        $servicesSheet->setShowGridLines(true);

        // =========================================================================
        // HOJA 3: RESUMEN DE FLOTILLA (TABLA INTERACTIVA)
        // =========================================================================
        $fleetSheet = $spreadsheet->createSheet();
        $fleetSheet->setTitle('Resumen de Flotilla');
        $fleetSheet->setShowGridLines(true);

        $this->buildServicesTableSheet($servicesSheet, $flatServices);
        $this->buildFleetTableSheet($fleetSheet, $vehicles, $flatServices);
        $this->buildDashboardSheet($dashSheet, $vehicles, $flatServices, $ordenes->count());

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        $writer->setPreCalculateFormulas(true);

        ob_start();
        $writer->save('php://output');
        return ob_get_clean();
    }

    protected function loadFilteredData(): array
    {
        $f = $this->filters;

        // 1. Filtrar vehículos según filtros de unidad y asignación
        $vQuery = Vehicle::with([
            'departamento',
            'linea',
            'sucursal',
            'marca',
            'modelo',
            'tipo',
            'estatus',
            'responsable:id,nombre,segundo_nombre,apellido_paterno,apellido_materno,vehicle_id',
            'empleados:empleados.id,empleados.nombre,empleados.segundo_nombre,empleados.apellido_paterno,empleados.apellido_materno,empleados.vehicle_id',
        ]);

        if (!empty($f['vehicle_id'])) {
            $vQuery->where('id', (int) $f['vehicle_id']);
        }
        if (!empty($f['marca_id'])) {
            $vQuery->where('marca_id', (int) $f['marca_id']);
        }
        if (!empty($f['modelo_id'])) {
            $vQuery->where('modelo_id', (int) $f['modelo_id']);
        }
        if (!empty($f['tipo_id'])) {
            $vQuery->where('tipo_id', (int) $f['tipo_id']);
        }
        if (isset($f['activo']) && $f['activo'] !== '' && $f['activo'] !== null) {
            $vQuery->where('activo', (int) $f['activo']);
        }
        if (!empty($f['sucursal_id'])) {
            $vQuery->where('sucursal_id', (int) $f['sucursal_id']);
        }
        if (!empty($f['linea_id'])) {
            $vQuery->where('linea_id', (int) $f['linea_id']);
        }
        if (!empty($f['departamento_id'])) {
            $vQuery->where('departamento_id', (int) $f['departamento_id']);
        }
        if (!empty($f['responsable_id'])) {
            $respId = (int) $f['responsable_id'];
            $vQuery->where(function ($q) use ($respId) {
                $q->whereHas('responsable', function ($rq) use ($respId) {
                    $rq->where('id', $respId);
                })->orWhereHas('empleados', function ($eq) use ($respId) {
                    $eq->where('empleados.id', $respId);
                });
            });
        }

        $vehicles = $vQuery->orderBy('placas', 'asc')->get();

        $empAppends = ['nombreCompleto', 'apellidoCompleto'];
        foreach ($vehicles as $veh) {
            if ($veh->relationLoaded('responsable') && $veh->responsable) {
                $veh->responsable->setAppends($empAppends);
            }
            if ($veh->relationLoaded('empleados') && $veh->empleados) {
                $veh->empleados->each->setAppends($empAppends);
            }
        }

        $vehicleIds = $vehicles->pluck('id')->all();

        $maxKmsServicios = !empty($vehicleIds)
            ? DB::table('vehicles_detalles_servicio')
                ->whereIn('vehicle_id', $vehicleIds)
                ->whereNotNull('km_evento')
                ->groupBy('vehicle_id')
                ->select('vehicle_id', DB::raw('MAX(km_evento) as ultimo_km'))
                ->pluck('ultimo_km', 'vehicle_id')
            : collect();

        $maxKmsGasolina = !empty($vehicleIds)
            ? DB::table('vehicles_dispersion_gasolina')
                ->whereIn('vehicle_id', $vehicleIds)
                ->whereNotNull('km_evento')
                ->groupBy('vehicle_id')
                ->select('vehicle_id', DB::raw('MAX(km_evento) as ultimo_km'))
                ->pluck('ultimo_km', 'vehicle_id')
            : collect();

        $vehicles->each(function ($v) use ($maxKmsServicios, $maxKmsGasolina) {
            $kmServ = isset($maxKmsServicios[$v->id]) ? (int) $maxKmsServicios[$v->id] : 0;
            $kmGas = isset($maxKmsGasolina[$v->id]) ? (int) $maxKmsGasolina[$v->id] : 0;
            $v->ultimo_km = max($kmServ, $kmGas);
        });

        if (empty($vehicleIds)) {
            return [$vehicles, collect(), []];
        }

        // 2. Cargar órdenes de esos vehículos aplicando filtros de orden/servicio/fecha
        $oQuery = VehiclesOrdenesServicio::whereIn('vehicle_id', $vehicleIds)
            ->whereHas('detalles.servicio', function ($q) {
                $q->where('nombre', '!=', 'Dispersión de gasolina');
            })
            ->with([
                'vehicle.departamento',
                'vehicle.linea',
                'vehicle.sucursal',
                'vehicle.marca',
                'vehicle.modelo',
                'vehicle.tipo',
                'vehicle.estatus',
                'proveedor',
                'solicitante',
                'sucursalCargo',
                'departamentoCargo',
                'estatus',
                'detalles.servicio.tipoServicio',
                'detalles.proveedor',
            ]);

        if (!empty($f['proveedor_id'])) {
            $provId = (int) $f['proveedor_id'];
            $oQuery->where(function ($q) use ($provId) {
                $q->where('proveedor_id', $provId)
                    ->orWhereHas('detalles', function ($dq) use ($provId) {
                        $dq->where('proveedor_id', $provId);
                    });
            });
        }
        if (!empty($f['solicitante_id'])) {
            $oQuery->where('solicitante_id', (int) $f['solicitante_id']);
        }
        if (!empty($f['suc_cargo_id'])) {
            $oQuery->where('suc_cargo_id', (int) $f['suc_cargo_id']);
        }
        if (!empty($f['depto_cargo_id'])) {
            $oQuery->where('depto_cargo_id', (int) $f['depto_cargo_id']);
        }
        if (!empty($f['estatus_orden_id'])) {
            $oQuery->where('estatus_id', (int) $f['estatus_orden_id']);
        }
        if (!empty($f['fecha_desde'])) {
            $oQuery->whereDate('fecha_elaboracion', '>=', $f['fecha_desde']);
        }
        if (!empty($f['fecha_hasta'])) {
            $oQuery->whereDate('fecha_elaboracion', '<=', $f['fecha_hasta']);
        }
        if (!empty($f['tipo_servicio_id'])) {
            $oQuery->whereHas('detalles.servicio', function ($sq) use ($f) {
                $sq->where('tipo_servicio_id', (int) $f['tipo_servicio_id']);
            });
        }
        if (!empty($f['servicio_id'])) {
            $oQuery->whereHas('detalles', function ($dq) use ($f) {
                $dq->where('servicio_id', (int) $f['servicio_id']);
            });
        }

        $ordenes = $oQuery->orderBy('fecha_elaboracion', 'desc')->orderBy('id', 'desc')->get();

        foreach ($ordenes as $orden) {
            if ($orden->relationLoaded('solicitante') && $orden->solicitante) {
                $orden->solicitante->setAppends($empAppends);
            }
        }

        $search = !empty($f['search']) ? mb_strtolower(trim($f['search'])) : null;
        $filterTipoId = !empty($f['tipo_servicio_id']) ? (int) $f['tipo_servicio_id'] : null;
        $filterServicioId = !empty($f['servicio_id']) ? (int) $f['servicio_id'] : null;
        $filterProveedorId = !empty($f['proveedor_id']) ? (int) $f['proveedor_id'] : null;

        $flatServices = [];
        foreach ($ordenes as $orden) {
            $veh = $orden->vehicle;
            $detalles = $orden->detalles ?? collect();

            foreach ($detalles as $det) {
                $servicio = $det->servicio;
                $tipoServicio = $servicio?->tipoServicio;

                if ($filterTipoId && (int) ($servicio?->tipo_servicio_id ?? 0) !== $filterTipoId) {
                    continue;
                }
                if ($filterServicioId && (int) ($det->servicio_id ?? 0) !== $filterServicioId) {
                    continue;
                }
                $provIdActual = $det->proveedor_id ?: $orden->proveedor_id;
                if ($filterProveedorId && (int) $provIdActual !== $filterProveedorId) {
                    continue;
                }

                $proveedorNombre = $det->proveedor?->nombre ?? $orden->proveedor?->nombre ?? 'No especificado';
                $vehDesc = trim(($veh?->marca?->nombre ?? '') . ' ' . ($veh?->modelo?->nombre ?? ''));
                if (empty($vehDesc)) {
                    $vehDesc = $veh?->tipo?->nombre ?? 'Unidad';
                }

                $anioVeh = isset($veh?->anio) && is_numeric($veh->anio) ? (int) $veh->anio : null;
                $anioServicio = $orden->fecha_elaboracion
                    ? (int) Carbon::parse($orden->fecha_elaboracion)->format('Y')
                    : (int) date('Y');
                $edadServicio = ($anioVeh && $anioVeh >= 1970 && $anioVeh <= 2100)
                    ? max(0, $anioServicio - $anioVeh)
                    : null;

                $kmEvento = $det->km_evento !== null ? (int) $det->km_evento : null;
                $kmBucket = ($kmEvento !== null && $kmEvento > 0)
                    ? (int) floor(($kmEvento - 1) / 10000)
                    : null;

                if (isset($f['antiguedad_anios']) && $f['antiguedad_anios'] !== '' && $f['antiguedad_anios'] !== null) {
                    if ($edadServicio === null || $edadServicio !== (int) $f['antiguedad_anios']) {
                        continue;
                    }
                }

                if (isset($f['km_bucket']) && $f['km_bucket'] !== '' && $f['km_bucket'] !== null) {
                    if ($kmBucket === null || $kmBucket !== (int) $f['km_bucket']) {
                        continue;
                    }
                }

                $item = [
                    'vehicle_id' => $veh?->id ?? $orden->vehicle_id,
                    'placas' => $veh?->placas ?? 'S/P',
                    'serie' => $veh?->serie ?? 'Sin serie',
                    'vehiculo_desc' => $vehDesc,
                    'anio_vehiculo' => $anioVeh,
                    'edad_servicio' => $edadServicio,
                    'km_bucket' => $kmBucket,
                    'sucursal_vehiculo' => $veh?->sucursal?->nombre ?? '-',
                    'linea_vehiculo' => $veh?->linea?->nombre ?? '-',
                    'depto_vehiculo' => $veh?->departamento?->nombre ?? '-',
                    'fecha' => $orden->fecha_elaboracion
                        ? Carbon::parse($orden->fecha_elaboracion)->format('d/m/Y')
                        : 'Sin fecha',
                    'periodo' => $orden->fecha_elaboracion
                        ? Carbon::parse($orden->fecha_elaboracion)->format('Y-m')
                        : 'Sin fecha',
                    'orden_compra' => $orden->orden_compra ?? (string) $orden->id,
                    'autorizacion_compras' => $orden->autorizacion_compras ?? '-',
                    'factura' => $orden->factura ?? '-',
                    'estatus_orden' => $orden->estatus?->nombre ?? 'Registrado',
                    'tipo_servicio' => $tipoServicio?->nombre ?? 'Sin tipo',
                    'servicio' => $servicio?->nombre ?? 'Servicio general',
                    'descripcion' => $det->descripcion ?? '',
                    'km_evento' => $kmEvento,
                    'proveedor' => $proveedorNombre,
                    'sucursal_cargo' => $orden->sucursalCargo?->nombre ?? '-',
                    'depto_cargo' => $orden->departamentoCargo?->nombre ?? '-',
                    'solicitante' => $orden->solicitante?->nombreCompleto ?? '-',
                    'subtotal' => (float) ($det->subtotal ?? 0),
                    'iva' => (float) ($det->iva ?? 0),
                    'total' => (float) ($det->total ?? 0),
                ];

                if ($search) {
                    $haystack = mb_strtolower(implode(' ', [
                        $item['placas'],
                        $item['serie'],
                        $item['vehiculo_desc'],
                        $item['orden_compra'],
                        $item['autorizacion_compras'],
                        $item['factura'],
                        $item['tipo_servicio'],
                        $item['servicio'],
                        $item['descripcion'],
                        $item['proveedor'],
                        $item['solicitante'],
                        $item['estatus_orden'],
                    ]));
                    if (!str_contains($haystack, $search)) {
                        continue;
                    }
                }

                $flatServices[] = $item;
            }
        }

        return [$vehicles, $ordenes, $flatServices];
    }

    protected function buildServicesTableSheet($sheet, array $services): void
    {
        $sheet->mergeCells('A1:R1');
        $sheet->setCellValue('A1', 'HISTORIAL GENERAL DE SERVICIOS DE VEHÍCULOS - CORPORATIVO ETBSA');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1565C0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $sheet->mergeCells('A2:R2');
        $sheet->setCellValue(
            'A2',
            'Nota: Filtra cualquier columna (Unidad, Sucursal, Tipo, Proveedor, Estatus, etc.) y los totales superiores se actualizarán automáticamente.'
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

        // Barra SUBTOTAL
        $sheet->setCellValue('A4', 'Servicios Visibles:');
        $sheet->setCellValue('B4', $countServices > 0 ? "=SUBTOTAL(103,I{$dataStartRow}:I{$lastDataRow})" : 0);

        $sheet->setCellValue('D4', 'Mayor KM Filtrado:');
        $sheet->setCellValue('E4', $countServices > 0 ? "=SUBTOTAL(104,K{$dataStartRow}:K{$lastDataRow})" : 0);
        $sheet->getStyle('E4')->getNumberFormat()->setFormatCode('#,##0 "km"');

        $sheet->setCellValue('M4', 'Subtotal Filtrado:');
        $sheet->setCellValue('N4', $countServices > 0 ? "=SUBTOTAL(109,P{$dataStartRow}:P{$lastDataRow})" : 0);
        $sheet->getStyle('N4')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->setCellValue('O4', 'IVA Filtrado:');
        $sheet->setCellValue('P4', $countServices > 0 ? "=SUBTOTAL(109,Q{$dataStartRow}:Q{$lastDataRow})" : 0);
        $sheet->getStyle('P4')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->setCellValue('Q4', 'TOTAL FILTRADO:');
        $sheet->setCellValue('R4', $countServices > 0 ? "=SUBTOTAL(109,R{$dataStartRow}:R{$lastDataRow})" : 0);
        $sheet->getStyle('R4')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->getStyle('A4:R4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F8E9']],
            'borders' => [
                'outline' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AED581']],
            ],
        ]);
        $sheet->getStyle('Q4:R4')->getFont()->getColor()->setRGB('1B5E20');
        $sheet->getRowDimension(4)->setRowHeight(22);

        $headers = [
            'A' => 'Fecha',
            'B' => 'Placas',
            'C' => 'Vehículo',
            'D' => 'Sucursal Unidad',
            'E' => 'Orden Compra',
            'F' => 'Aut. Compras',
            'G' => 'Estatus Orden',
            'H' => 'Tipo de Servicio',
            'I' => 'Servicio',
            'J' => 'Descripción / Detalle',
            'K' => 'Kilometraje (KM)',
            'L' => 'Proveedor',
            'M' => 'Sucursal Cargo',
            'N' => 'Depto. Cargo',
            'O' => 'Solicitante',
            'P' => 'Subtotal ($)',
            'Q' => 'IVA ($)',
            'R' => 'Total Invertido ($)',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}{$startRow}", $title);
        }

        $sheet->getStyle("A{$startRow}:R{$startRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(24);

        if ($countServices === 0) {
            $sheet->setCellValue("A{$dataStartRow}", 'Sin registros');
            $sheet->setCellValue("B{$dataStartRow}", '-');
            $sheet->setCellValue("I{$dataStartRow}", 'Sin servicios registrados');
            $sheet->setCellValue("K{$dataStartRow}", 0);
            $sheet->setCellValue("P{$dataStartRow}", 0);
            $sheet->setCellValue("Q{$dataStartRow}", 0);
            $sheet->setCellValue("R{$dataStartRow}", 0);
        } else {
            $rowIdx = $dataStartRow;
            foreach ($services as $srv) {
                $sheet->setCellValue("A{$rowIdx}", $srv['fecha']);
                $sheet->setCellValue("B{$rowIdx}", $srv['placas']);
                $sheet->setCellValue("C{$rowIdx}", $srv['vehiculo_desc']);
                $sheet->setCellValue("D{$rowIdx}", $srv['sucursal_vehiculo']);
                $sheet->setCellValue("E{$rowIdx}", $srv['orden_compra']);
                $sheet->setCellValue("F{$rowIdx}", $srv['autorizacion_compras']);
                $sheet->setCellValue("G{$rowIdx}", $srv['estatus_orden']);
                $sheet->setCellValue("H{$rowIdx}", $srv['tipo_servicio']);
                $sheet->setCellValue("I{$rowIdx}", $srv['servicio']);
                $sheet->setCellValue("J{$rowIdx}", $srv['descripcion']);
                if ($srv['km_evento'] !== null) {
                    $sheet->setCellValue("K{$rowIdx}", $srv['km_evento']);
                }
                $sheet->setCellValue("L{$rowIdx}", $srv['proveedor']);
                $sheet->setCellValue("M{$rowIdx}", $srv['sucursal_cargo']);
                $sheet->setCellValue("N{$rowIdx}", $srv['depto_cargo']);
                $sheet->setCellValue("O{$rowIdx}", $srv['solicitante']);
                $sheet->setCellValue("P{$rowIdx}", $srv['subtotal']);
                $sheet->setCellValue("Q{$rowIdx}", $srv['iva']);
                $sheet->setCellValue("R{$rowIdx}", $srv['total']);
                $rowIdx++;
            }
        }

        $sheet->getStyle("K{$dataStartRow}:K{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0');
        $sheet->getStyle("P{$dataStartRow}:R{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00');
        $sheet->getStyle("R{$dataStartRow}:R{$lastDataRow}")
            ->getFont()
            ->setBold(true);

        $sheet->getStyle("A{$startRow}:R{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $table = new Table("A{$startRow}:R{$lastDataRow}", 'TablaGeneralServicios');
        $tableStyle = new TableStyle();
        $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM2);
        $tableStyle->setShowRowStripes(true);
        $table->setStyle($tableStyle);
        $sheet->addTable($table);

        $widths = [
            'A' => 14,
            'B' => 14,
            'C' => 24,
            'D' => 18,
            'E' => 15,
            'F' => 16,
            'G' => 16,
            'H' => 20,
            'I' => 26,
            'J' => 38,
            'K' => 16,
            'L' => 28,
            'M' => 18,
            'N' => 18,
            'O' => 24,
            'P' => 15,
            'Q' => 14,
            'R' => 18,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $sheet->freezePane("A{$dataStartRow}");
    }

    protected function buildFleetTableSheet($sheet, $vehicles, array $services): void
    {
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'PADRÓN Y RESUMEN DE FLOTILLA VEHICULAR - CORPORATIVO ETBSA');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00695C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        $statsByVehicle = [];
        foreach ($services as $s) {
            $vid = $s['vehicle_id'];
            if (!isset($statsByVehicle[$vid])) {
                $statsByVehicle[$vid] = ['count' => 0, 'total' => 0.0];
            }
            $statsByVehicle[$vid]['count']++;
            $statsByVehicle[$vid]['total'] += $s['total'];
        }

        $startRow = 4;
        $dataStartRow = 5;
        $countVehicles = $vehicles->count();
        $lastDataRow = max($dataStartRow, $dataStartRow + $countVehicles - 1);

        $headers = [
            'A' => 'Placas',
            'B' => 'Serie (VIN)',
            'C' => 'Marca / Modelo',
            'D' => 'Tipo Vehículo',
            'E' => 'Año',
            'F' => 'Sucursal',
            'G' => 'Línea',
            'H' => 'Departamento',
            'I' => 'Responsable',
            'J' => 'Estatus',
            'K' => 'Cant. Servicios',
            'L' => 'Total Invertido ($)',
        ];

        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}{$startRow}", $title);
        }

        $sheet->getStyle("A{$startRow}:L{$startRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '004D40']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension($startRow)->setRowHeight(24);

        if ($countVehicles === 0) {
            $sheet->setCellValue("A{$dataStartRow}", 'Sin unidades');
            $sheet->setCellValue("K{$dataStartRow}", 0);
            $sheet->setCellValue("L{$dataStartRow}", 0);
        } else {
            $r = $dataStartRow;
            foreach ($vehicles as $v) {
                $st = $statsByVehicle[$v->id] ?? ['count' => 0, 'total' => 0.0];
                $marcaModelo = trim(($v->marca?->nombre ?? '') . ' ' . ($v->modelo?->nombre ?? ''));

                $sheet->setCellValue("A{$r}", $v->placas ?? 'S/P');
                $sheet->setCellValue("B{$r}", $v->serie ?? '-');
                $sheet->setCellValue("C{$r}", $marcaModelo ?: '-');
                $sheet->setCellValue("D{$r}", $v->tipo?->nombre ?? '-');
                $sheet->setCellValue("E{$r}", $v->anio ?? '-');
                $sheet->setCellValue("F{$r}", $v->sucursal?->nombre ?? '-');
                $sheet->setCellValue("G{$r}", $v->linea?->nombre ?? '-');
                $sheet->setCellValue("H{$r}", $v->departamento?->nombre ?? '-');
                $sheet->setCellValue("I{$r}", $v->responsable?->nombreCompleto ?? 'Sin responsable');
                $sheet->setCellValue("J{$r}", $v->activo == 1 ? 'ACTIVO' : 'INACTIVO');
                $sheet->setCellValue("K{$r}", $st['count']);
                $sheet->setCellValue("L{$r}", $st['total']);
                $r++;
            }
        }

        $sheet->getStyle("L{$dataStartRow}:L{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00');

        $sheet->getStyle("A{$startRow}:L{$lastDataRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']],
            ],
        ]);

        $table = new Table("A{$startRow}:L{$lastDataRow}", 'TablaResumenFlotilla');
        $tableStyle = new TableStyle();
        $tableStyle->setTheme(TableStyle::TABLE_STYLE_MEDIUM4);
        $tableStyle->setShowRowStripes(true);
        $table->setStyle($tableStyle);
        $sheet->addTable($table);

        $widths = [
            'A' => 14,
            'B' => 20,
            'C' => 24,
            'D' => 18,
            'E' => 10,
            'F' => 18,
            'G' => 18,
            'H' => 20,
            'I' => 26,
            'J' => 14,
            'K' => 16,
            'L' => 20,
        ];
        foreach ($widths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $sheet->freezePane("A{$dataStartRow}");
    }

    protected function buildDashboardSheet($sheet, $vehicles, array $services, int $totalOrdenes): void
    {
        $totalVehicles = $vehicles->count();
        $activosCount = $vehicles->where('activo', 1)->count();
        $inactivosCount = $totalVehicles - $activosCount;
        $countServices = count($services);

        $sheet->mergeCells('A1:N1');
        $sheet->setCellValue('A1', 'DASHBOARD GENERAL DE VEHÍCULOS Y SERVICIOS - CORPORATIVO ETBSA');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D47A1']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        $sheet->mergeCells('A2:N2');
        $sheet->setCellValue(
            'A2',
            "Total Flotilla Filtrada: {$totalVehicles} unidad(es) ({$activosCount} Activas / {$inactivosCount} Inactivas)   |   Órdenes Filtradas: {$totalOrdenes}   |   Servicios Registrados: {$countServices}"
        );
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => '1E293B']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(22);

        $kpiHeaders = [
            'A4:C4' => ['cell' => 'A4', 'title' => 'DINERO TOTAL INVERTIDO', 'color' => '1B5E20', 'bg' => 'E8F5E9'],
            'D4:E4' => ['cell' => 'D4', 'title' => 'SUBTOTAL FILTRADO', 'color' => '0D47A1', 'bg' => 'E3F2FD'],
            'F4:G4' => ['cell' => 'F4', 'title' => 'SERVICIOS REALIZADOS', 'color' => '4A148C', 'bg' => 'F3E5F5'],
            'H4:I4' => ['cell' => 'H4', 'title' => 'ÓRDENES DE SERVICIO', 'color' => '006064', 'bg' => 'E0F7FA'],
            'J4:K4' => ['cell' => 'J4', 'title' => 'UNIDADES EN FLOTILLA', 'color' => 'E65100', 'bg' => 'FFF3E0'],
            'L4:N4' => ['cell' => 'L4', 'title' => 'ACTIVOS / INACTIVOS', 'color' => '37474F', 'bg' => 'ECEFF1'],
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
        $sheet->setCellValue('A5', $countServices > 0 ? "='Historial de Servicios'!R4" : 0);
        $sheet->getStyle('A5')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->mergeCells('D5:E5');
        $sheet->setCellValue('D5', $countServices > 0 ? "='Historial de Servicios'!N4" : 0);
        $sheet->getStyle('D5')->getNumberFormat()->setFormatCode('$#,##0.00');

        $sheet->mergeCells('F5:G5');
        $sheet->setCellValue('F5', $countServices > 0 ? "='Historial de Servicios'!B4" : 0);

        $sheet->mergeCells('H5:I5');
        $sheet->setCellValue('H5', $totalOrdenes);

        $sheet->mergeCells('J5:K5');
        $sheet->setCellValue('J5', $totalVehicles);

        $sheet->mergeCells('L5:N5');
        $sheet->setCellValue('L5', "{$activosCount} Activos / {$inactivosCount} Inactivos");

        $sheet->getStyle('A5:N5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '111827']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0BEC5']]],
        ]);
        $sheet->getStyle('A5')->getFont()->getColor()->setRGB('1B5E20');
        $sheet->getRowDimension(4)->setRowHeight(18);
        $sheet->getRowDimension(5)->setRowHeight(26);

        $summaryHeaderRow = 25;
        $summaryStartRow = 26;

        $byTipo = [];
        $byUnidad = [];
        $bySucursal = [];

        foreach ($services as $s) {
            $t = $s['tipo_servicio'];
            if (!isset($byTipo[$t])) {
                $byTipo[$t] = ['nombre' => $t, 'count' => 0, 'total' => 0.0];
            }
            $byTipo[$t]['count']++;
            $byTipo[$t]['total'] += $s['total'];

            $u = $s['placas'] . ' (' . $s['vehiculo_desc'] . ')';
            if (!isset($byUnidad[$u])) {
                $byUnidad[$u] = ['nombre' => $u, 'sucursal' => $s['sucursal_vehiculo'], 'count' => 0, 'total' => 0.0];
            }
            $byUnidad[$u]['count']++;
            $byUnidad[$u]['total'] += $s['total'];

            $suc = $s['sucursal_vehiculo'] ?: 'Sin sucursal';
            if (!isset($bySucursal[$suc])) {
                $bySucursal[$suc] = ['nombre' => $suc, 'count' => 0, 'total' => 0.0];
            }
            $bySucursal[$suc]['count']++;
            $bySucursal[$suc]['total'] += $s['total'];
        }

        usort($byTipo, fn($a, $b) => $b['total'] <=> $a['total']);
        usort($byUnidad, fn($a, $b) => $b['total'] <=> $a['total']);
        usort($bySucursal, fn($a, $b) => $b['total'] <=> $a['total']);
        $topUnidades = array_slice($byUnidad, 0, 12);

        if (empty($byTipo)) {
            $byTipo[] = ['nombre' => 'Sin registros', 'count' => 0, 'total' => 0.0];
        }
        if (empty($topUnidades)) {
            $topUnidades[] = ['nombre' => 'Sin registros', 'sucursal' => '-', 'count' => 0, 'total' => 0.0];
        }
        if (empty($bySucursal)) {
            $bySucursal[] = ['nombre' => 'Sin registros', 'count' => 0, 'total' => 0.0];
        }

        // TABLA 1: Inversión por Tipo de Servicio (A:D)
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

        // TABLA 2: Top Unidades con Mayor Inversión (F:I)
        $sheet->setCellValue("F{$summaryHeaderRow}", 'Unidad (Placa)');
        $sheet->setCellValue("G{$summaryHeaderRow}", 'Sucursal');
        $sheet->setCellValue("H{$summaryHeaderRow}", 'Cant. Servicios');
        $sheet->setCellValue("I{$summaryHeaderRow}", 'Dinero Invertido ($)');
        $sheet->getStyle("F{$summaryHeaderRow}:I{$summaryHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2E7D32']],
        ]);

        $rUni = $summaryStartRow;
        foreach ($topUnidades as $item) {
            $sheet->setCellValue("F{$rUni}", $item['nombre']);
            $sheet->setCellValue("G{$rUni}", $item['sucursal']);
            $sheet->setCellValue("H{$rUni}", (int) $item['count']);
            $sheet->setCellValue("I{$rUni}", (float) $item['total']);
            $rUni++;
        }
        $lastUniRow = $rUni - 1;
        $sheet->getStyle("I{$summaryStartRow}:I{$lastUniRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("F{$summaryHeaderRow}:I{$lastUniRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']]],
        ]);

        // TABLA 3: Inversión por Sucursal (L:N)
        $sheet->setCellValue("L{$summaryHeaderRow}", 'Sucursal');
        $sheet->setCellValue("M{$summaryHeaderRow}", 'Cant. Servicios');
        $sheet->setCellValue("N{$summaryHeaderRow}", 'Dinero Invertido ($)');
        $sheet->getStyle("L{$summaryHeaderRow}:N{$summaryHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6A1B9A']],
        ]);

        $rSuc = $summaryStartRow;
        foreach ($bySucursal as $item) {
            $sheet->setCellValue("L{$rSuc}", $item['nombre']);
            $sheet->setCellValue("M{$rSuc}", (int) $item['count']);
            $sheet->setCellValue("N{$rSuc}", (float) $item['total']);
            $rSuc++;
        }
        $lastSucRow = $rSuc - 1;
        $sheet->getStyle("N{$summaryStartRow}:N{$lastSucRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("L{$summaryHeaderRow}:N{$lastSucRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']]],
        ]);

        // GRÁFICA 1: Inversión por Tipo de Servicio
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
        $pieChart = new Chart(
            'chart_tipo_servicio',
            new Title('Inversión ($) por Tipo de Servicio'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            new PlotArea($pieLayout, [$pieSeries]),
            true,
            DataSeries::EMPTY_AS_GAP
        );
        $pieChart->setTopLeftPosition('A7');
        $pieChart->setBottomRightPosition('E23');
        $sheet->addChart($pieChart);

        // GRÁFICA 2: Top Unidades por Inversión
        $barLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$I\${$summaryHeaderRow}", null, 1),
        ];
        $barCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$F\${$summaryStartRow}:\$F\${$lastUniRow}", null, count($topUnidades)),
        ];
        $barValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$I\${$summaryStartRow}:\$I\${$lastUniRow}", null, count($topUnidades)),
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
        $barChart = new Chart(
            'chart_top_unidades',
            new Title('Top Unidades por Dinero Invertido ($)'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            new PlotArea($barLayout, [$barSeries]),
            true,
            DataSeries::EMPTY_AS_GAP,
            null,
            new Title('Monto Invertido (MXN)')
        );
        $barChart->setTopLeftPosition('F7');
        $barChart->setBottomRightPosition('J23');
        $sheet->addChart($barChart);

        // GRÁFICA 3: Inversión por Sucursal
        $sucLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$N\${$summaryHeaderRow}", null, 1),
        ];
        $sucCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$L\${$summaryStartRow}:\$L\${$lastSucRow}", null, count($bySucursal)),
        ];
        $sucValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$N\${$summaryStartRow}:\$N\${$lastSucRow}", null, count($bySucursal)),
        ];
        $sucSeries = new DataSeries(
            DataSeries::TYPE_BARCHART,
            DataSeries::GROUPING_CLUSTERED,
            range(0, count($sucValues) - 1),
            $sucLabels,
            $sucCategories,
            $sucValues
        );
        $sucSeries->setPlotDirection(DataSeries::DIRECTION_COL);
        $sucLayout = new Layout();
        $sucLayout->setShowVal(true);
        $sucChart = new Chart(
            'chart_sucursales',
            new Title('Inversión ($) por Sucursal'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            new PlotArea($sucLayout, [$sucSeries]),
            true,
            DataSeries::EMPTY_AS_GAP
        );
        $sucChart->setTopLeftPosition('K7');
        $sucChart->setBottomRightPosition('N23');
        $sheet->addChart($sucChart);

        // =====================================================================
        // SECCIÓN DE TENDENCIAS: ANTIGÜEDAD Y RANGO DE KILOMETRAJE (10,000 KM)
        // =====================================================================
        $byAntiguedad = [];
        $byKmBucket = [];

        foreach ($services as $s) {
            if ($s['edad_servicio'] !== null && $s['edad_servicio'] <= 50) {
                $edad = (int) $s['edad_servicio'];
                if (!isset($byAntiguedad[$edad])) {
                    $byAntiguedad[$edad] = [
                        'edad' => $edad,
                        'label' => $edad === 0 ? '0 años (Nuevo)' : ($edad === 1 ? '1 año' : "{$edad} años"),
                        'count' => 0,
                        'unidades' => [],
                        'total' => 0.0,
                    ];
                }
                $byAntiguedad[$edad]['count']++;
                $byAntiguedad[$edad]['unidades'][$s['vehicle_id']] = true;
                $byAntiguedad[$edad]['total'] += $s['total'];
            }

            if ($s['km_bucket'] !== null && $s['km_bucket'] >= 0 && $s['km_bucket'] <= 200) {
                $b = (int) $s['km_bucket'];
                if (!isset($byKmBucket[$b])) {
                    $byKmBucket[$b] = [
                        'bucket' => $b,
                        'label' => ($b * 10) . 'k - ' . (($b + 1) * 10) . 'k km',
                        'count' => 0,
                        'unidades' => [],
                        'total' => 0.0,
                    ];
                }
                $byKmBucket[$b]['count']++;
                $byKmBucket[$b]['unidades'][$s['vehicle_id']] = true;
                $byKmBucket[$b]['total'] += $s['total'];
            }
        }

        ksort($byAntiguedad);
        ksort($byKmBucket);

        $antiguedadRows = array_values($byAntiguedad);
        if (empty($antiguedadRows)) {
            $antiguedadRows[] = ['label' => 'Sin datos', 'count' => 0, 'unidades' => [], 'total' => 0.0];
        }

        $kmRows = array_values($byKmBucket);
        if (empty($kmRows)) {
            $kmRows[] = ['label' => 'Sin datos', 'count' => 0, 'unidades' => [], 'total' => 0.0];
        }

        $maxFirstTablesRow = max($lastTipoRow, $lastUniRow, $lastSucRow);
        $trendChartTopRow = $maxFirstTablesRow + 3;
        $trendChartBottomRow = $trendChartTopRow + 15;
        $trendTableHeaderRow = $trendChartBottomRow + 2;
        $trendTableStartRow = $trendTableHeaderRow + 1;

        // TABLA 4: Tendencia por Antigüedad de la Unidad (A:D)
        $sheet->setCellValue("A{$trendTableHeaderRow}", 'Antigüedad Unidad');
        $sheet->setCellValue("B{$trendTableHeaderRow}", 'Cant. Servicios');
        $sheet->setCellValue("C{$trendTableHeaderRow}", 'Monto Total ($)');
        $sheet->setCellValue("D{$trendTableHeaderRow}", 'Promedio / Unidad ($)');
        $sheet->getStyle("A{$trendTableHeaderRow}:D{$trendTableHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D47A1']],
        ]);

        $rAnt = $trendTableStartRow;
        foreach ($antiguedadRows as $item) {
            $numUnidades = max(1, count($item['unidades']));
            $avgUni = $item['total'] > 0 ? $item['total'] / $numUnidades : 0.0;
            $sheet->setCellValue("A{$rAnt}", $item['label']);
            $sheet->setCellValue("B{$rAnt}", (int) $item['count']);
            $sheet->setCellValue("C{$rAnt}", (float) $item['total']);
            $sheet->setCellValue("D{$rAnt}", (float) $avgUni);
            $rAnt++;
        }
        $lastAntRow = $rAnt - 1;
        $sheet->getStyle("C{$trendTableStartRow}:D{$lastAntRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("A{$trendTableHeaderRow}:D{$lastAntRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']]],
        ]);

        // TABLA 5: Tendencia por Rango de Kilometraje 10,000 km (F:I)
        $sheet->setCellValue("F{$trendTableHeaderRow}", 'Rango de KM (10k)');
        $sheet->setCellValue("G{$trendTableHeaderRow}", 'Cant. Servicios');
        $sheet->setCellValue("H{$trendTableHeaderRow}", 'Monto Total ($)');
        $sheet->setCellValue("I{$trendTableHeaderRow}", 'Promedio / Unidad ($)');
        $sheet->getStyle("F{$trendTableHeaderRow}:I{$trendTableHeaderRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00695C']],
        ]);

        $rKm = $trendTableStartRow;
        foreach ($kmRows as $item) {
            $numUnidades = max(1, count($item['unidades']));
            $avgUni = $item['total'] > 0 ? $item['total'] / $numUnidades : 0.0;
            $sheet->setCellValue("F{$rKm}", $item['label']);
            $sheet->setCellValue("G{$rKm}", (int) $item['count']);
            $sheet->setCellValue("H{$rKm}", (float) $item['total']);
            $sheet->setCellValue("I{$rKm}", (float) $avgUni);
            $rKm++;
        }
        $lastKmRow = $rKm - 1;
        $sheet->getStyle("H{$trendTableStartRow}:I{$lastKmRow}")->getNumberFormat()->setFormatCode('$#,##0.00');
        $sheet->getStyle("F{$trendTableHeaderRow}:I{$lastKmRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CFD8DC']]],
        ]);

        // GRÁFICA DE LÍNEAS 4: Monto Gastado por Antigüedad de la Unidad
        $antLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$C\${$trendTableHeaderRow}", null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$D\${$trendTableHeaderRow}", null, 1),
        ];
        $antCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$A\${$trendTableStartRow}:\$A\${$lastAntRow}", null, count($antiguedadRows)),
        ];
        $antValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$C\${$trendTableStartRow}:\$C\${$lastAntRow}", null, count($antiguedadRows)),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$D\${$trendTableStartRow}:\$D\${$lastAntRow}", null, count($antiguedadRows)),
        ];
        $antSeries = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($antValues) - 1),
            $antLabels,
            $antCategories,
            $antValues
        );
        $antLayout = new Layout();
        $antLayout->setShowVal(true);
        $antChart = new Chart(
            'chart_tendencia_antiguedad',
            new Title('Tendencia de Gasto ($) por Antigüedad de la Unidad'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            new PlotArea($antLayout, [$antSeries]),
            true,
            DataSeries::EMPTY_AS_GAP,
            new Title('Años de Antigüedad'),
            new Title('Monto Invertido (MXN)')
        );
        $antChart->setTopLeftPosition("A{$trendChartTopRow}");
        $antChart->setBottomRightPosition("F{$trendChartBottomRow}");
        $sheet->addChart($antChart);

        // GRÁFICA DE LÍNEAS 5: Monto Gastado por Rango de KM (Intervalos de 10,000 km)
        $kmChartLabels = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$H\${$trendTableHeaderRow}", null, 1),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$I\${$trendTableHeaderRow}", null, 1),
        ];
        $kmChartCategories = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_STRING, "Dashboard!\$F\${$trendTableStartRow}:\$F\${$lastKmRow}", null, count($kmRows)),
        ];
        $kmChartValues = [
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$H\${$trendTableStartRow}:\$H\${$lastKmRow}", null, count($kmRows)),
            new DataSeriesValues(DataSeriesValues::DATASERIES_TYPE_NUMBER, "Dashboard!\$I\${$trendTableStartRow}:\$I\${$lastKmRow}", null, count($kmRows)),
        ];
        $kmChartSeries = new DataSeries(
            DataSeries::TYPE_LINECHART,
            DataSeries::GROUPING_STANDARD,
            range(0, count($kmChartValues) - 1),
            $kmChartLabels,
            $kmChartCategories,
            $kmChartValues
        );
        $kmChartLayout = new Layout();
        $kmChartLayout->setShowVal(true);
        $kmChart = new Chart(
            'chart_tendencia_km',
            new Title('Tendencia de Gasto ($) por Rango de KM (Intervalos de 10,000 km)'),
            new Legend(Legend::POSITION_BOTTOM, null, false),
            new PlotArea($kmChartLayout, [$kmChartSeries]),
            true,
            DataSeries::EMPTY_AS_GAP,
            new Title('Rango de Kilometraje'),
            new Title('Monto Invertido (MXN)')
        );
        $kmChart->setTopLeftPosition("G{$trendChartTopRow}");
        $kmChart->setBottomRightPosition("N{$trendChartBottomRow}");
        $sheet->addChart($kmChart);

        $dashWidths = [
            'A' => 24,
            'B' => 16,
            'C' => 20,
            'D' => 14,
            'E' => 6,
            'F' => 26,
            'G' => 18,
            'H' => 14,
            'I' => 20,
            'J' => 6,
            'K' => 6,
            'L' => 20,
            'M' => 16,
            'N' => 20,
        ];
        foreach ($dashWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }
    }
}

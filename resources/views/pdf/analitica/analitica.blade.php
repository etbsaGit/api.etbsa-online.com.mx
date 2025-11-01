<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte de solicitud de financiamiento</title>
    <style>
        /* ==================== CONFIGURACIÓN GENERAL ==================== */
        @page {
            margin: 30px 25px;
        }

        body {
            font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            background-color: #f8f9fa;
        }

        h2,
        h3,
        h4,
        h5 {
            color: #1c3f27;
            margin-bottom: 6px;
            margin-top: 18px;
        }

        h2 {
            border-bottom: 3px solid #367c2b;
            padding-bottom: 5px;
            margin-bottom: 12px;
        }

        h3 {
            background: #e9f4ec;
            padding: 6px 10px;
            border-left: 5px solid #367c2b;
            border-radius: 4px;
            font-size: 14px;
        }

        h4 {
            color: #367c2b;
            margin-top: 10px;
            font-size: 13px;
        }

        p {
            margin: 3px 0;
        }

        hr {
            border: 0;
            border-top: 1px solid #ccc;
            margin: 15px 0;
        }

        /* ==================== BLOQUE DE INFORMACIÓN ==================== */
        .header-info {
            background: #e9f4ec;
            border-left: 4px solid #367c2b;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        /* ==================== TABLAS ==================== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            background-color: #fff;
            border-radius: 6px;
            overflow: hidden;
            table-layout: fixed;
            /* Evita desbordes en PDF */
            word-wrap: break-word;
        }

        th,
        td {
            padding: 7px 8px;
            border-bottom: 1px solid #e0e0e0;
            text-align: left;
            font-size: 12px;
        }

        th {
            background-color: #367c2b;
            color: #fff;
            font-weight: 600;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        .total-row th {
            background-color: #e9f4ec;
            color: #1c3f27;
            font-weight: bold;
            border-top: 2px solid #367c2b;
        }

        .total-row td,
        .total-row th {
            font-weight: bold;
        }

        /* ==================== NUMERACIÓN (fuente especial) ==================== */
        .numeric {
            font-family: 'Consolas', 'Courier New', monospace !important;
            text-align: right;
        }

        td.numeric,
        th.numeric {
            text-align: right;
        }

        /* ==================== TABLAS GRANDES ==================== */
        .wide-table {
            width: 100%;
            overflow-x: auto;
            display: block;
            page-break-inside: avoid;
        }

        /* ==================== PIE DE PÁGINA ==================== */
        footer {
            text-align: center;
            font-size: 11px;
            color: #777;
            margin-top: 25px;
            border-top: 1px solid #ddd;
            padding-top: 6px;
        }
    </style>

<body>

    <h2>Reporte Financiero</h2>

    <div class="header-info">
        <p><strong>Periodo:</strong> {{ $analitica ?? 'Sin periodo establecido' }}</p>
        <p><strong>Cliente:</strong> {{ $cliente['nombre'] ?? 'N/A' }}</p>
        <p><strong>Estado:</strong> {{ $cliente['state_entity']['name'] ?? 'N/A' }}</p>
        <p><strong>Ciudad:</strong> {{ $cliente['town']['name'] ?? 'N/A' }}</p>

    </div>

    {{-- ================== ACTIVOS CIRCULANTES ================== --}}
    <h3>Activos Circulantes</h3>

    {{-- ---------- Base (efectivo, caja, documentos, mercancías) ---------- --}}
    <h4>Valores Base</h4>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th style="text-align:right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @php
                $base = $activos_circulantes['base'] ?? [];
                $subtotalBase = array_sum($base);
            @endphp

            @forelse($base as $concepto => $valor)
                <tr>
                    <td>{{ ucfirst($concepto) }}</td>
                    <td style="text-align:right">${{ number_format($valor ?? 0, 2, '.', ',') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">No hay valores base registrados.</td>
                </tr>
            @endforelse

            <tr class="total-row">
                <th style="text-align:right">Subtotal Base</th>
                <th style="text-align:right">${{ number_format($subtotalBase ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ---------- Inversiones Agrícolas ---------- --}}
    <h4>Inversiones Agrícolas</h4>
    <table>
        <thead>
            <tr>
                <th>Cultivo</th>
                <th>Año</th>
                <th># de Hectareas</th>
                <th>Costo por hectarea</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $inversionesAgricolas = $activos_circulantes['inversiones']['agricolas']['items'] ?? [];
                $totalAgricolas =
                    $activos_circulantes['inversiones']['agricolas']['totalAgricolas'] ??
                    collect($inversionesAgricolas)->sum('total');
            @endphp

            @forelse($inversionesAgricolas as $inv)
                <tr>
                    <td>{{ $inv['cultivo']['name'] ?? 'Sin cultivo' }}</td>
                    <td>{{ $inv['year'] ?? '-' }}</td>
                    <td>{{ $inv['hectareas'] ?? '-' }}</td>
                    <td>${{ number_format($inv['costo'] ?? 0, 2, '.', ',') }}</td>
                    <td style="text-align:right">
                        ${{ number_format($inv['total'] ?? 0, 2, '.', ',') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No hay inversiones agrícolas registradas.</td>
                </tr>
            @endforelse

            <tr class="total-row">
                <th colspan="4" style="text-align:right">Subtotal Inversiones Agrícolas</th>
                <th style="text-align:right">${{ number_format($totalAgricolas ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ---------- Inversiones Ganaderas ---------- --}}
    <h4>Inversiones Ganaderas</h4>
    <table>
        <thead>
            <tr>
                <th>Ganado</th>
                <th>Año</th>
                <th>Cabezas</th>
                <th>Costo por cabeza</th>
                <th style="text-align:right">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $inversionesGanaderas = $activos_circulantes['inversiones']['ganaderas']['items'] ?? [];
                $totalGanaderas =
                    $activos_circulantes['inversiones']['ganaderas']['totalGanaderas'] ??
                    collect($inversionesGanaderas)->sum('total');
            @endphp

            @forelse($inversionesGanaderas as $inv)
                <tr>
                    <td>{{ $inv['ganado']['name'] ?? 'Sin ganado' }}</td>
                    <td>{{ $inv['year'] ?? '-' }}</td>
                    <td>{{ $inv['unidades'] ?? '-' }}</td>
                    <td>
                        ${{ number_format($inv['costo'] ?? 0, 2, '.', ',') }}
                    </td>
                    <td style="text-align:right">
                        ${{ number_format($inv['total'] ?? 0, 2, '.', ',') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">No hay inversiones ganaderas registradas.</td>
                </tr>
            @endforelse

            <tr class="total-row">
                <th colspan="4" style="text-align:right">Subtotal Inversiones Ganaderas</th>
                <th style="text-align:right">${{ number_format($totalGanaderas ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ---------- Total General de Activos Circulantes ---------- --}}
    @php
        $totalActivosCirculantes =
            $activos_circulantes['totalActivosCirculantes'] ??
            ($subtotalBase ?? 0) + ($totalAgricolas ?? 0) + ($totalGanaderas ?? 0);
    @endphp
    <table>
        <tbody>
            <tr class="total-row">
                <th style="text-align:right">Total Activos Circulantes (Base + Inversiones)</th>
                <th style="text-align:right">${{ number_format($totalActivosCirculantes ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ================== ACTIVOS FIJOS (Máquinas + Fincas) ================== --}}
    <h3>Activos Fijos</h3>

    {{-- ---------- Tabla Máquinas ---------- --}}
    <h4>Máquinas</h4>
    <table>
        <thead>
            <tr>
                <th>Condición</th>
                <th>Clase Equipo</th>
                <th>Tipo Equipo</th>
                <th style="text-align:right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @php
                $machines = $activos_fijos['machines']['items'] ?? [];
            @endphp

            @if (empty($machines))
                <tr>
                    <td colspan="4">No hay máquinas registradas.</td>
                </tr>
            @else
                @foreach ($machines as $machine)
                    <tr>
                        <td>{{ $machine['condicion']['name'] ?? '-' }}</td>
                        <td>{{ $machine['clas_equipo']['name'] ?? '-' }}</td>
                        <td>{{ $machine['tipo_equipo']['name'] ?? '-' }}</td>
                        <td style="text-align:right">
                            ${{ number_format($machine['valor'] ?? 0, 2, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            @endif

            {{-- Subtotal Máquinas (si existe en data, se respeta; si no, se calcula) --}}
            @php
                $subtotalMachines = $activos_fijos['machines']['totalMachines'] ?? collect($machines)->sum('valor');
            @endphp
            <tr class="total-row">
                <th colspan="3" style="text-align:right">Subtotal Máquinas</th>
                <th style="text-align:right">${{ number_format($subtotalMachines ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ---------- Tabla Fincas ---------- --}}
    <h4>Fincas</h4>
    <table>
        <thead>
            <tr>
                <th>Finca</th>
                <th>Superficie</th>
                <th style="text-align:right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @php
                $fincas = $activos_fijos['fincas']['items'] ?? [];
            @endphp

            @if (empty($fincas))
                <tr>
                    <td colspan="2">No hay fincas registradas.</td>
                </tr>
            @else
                @foreach ($fincas as $finca)
                    <tr>
                        <td>{{ $finca['nombre'] ?? '-' }}</td>
                        <td>{{ $finca['descripcion'] ?? '-' }}</td>
                        <td style="text-align:right">
                            ${{ number_format($finca['valor'] ?? 0, 2, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            @endif

            {{-- Subtotal Fincas --}}
            @php
                $subtotalFincas = $activos_fijos['fincas']['totalFincas'] ?? collect($fincas)->sum('valor');
            @endphp
            <tr class="total-row">
                <th colspan="2" style="text-align:right">Subtotal Fincas</th>
                <th style="text-align:right">${{ number_format($subtotalFincas ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ---------- Total Activos Fijos (Máquinas + Fincas) ---------- --}}
    @php
        // Si viene totalActivosFijos en la data, respetarlo; si no, sumar los subtotales calculados.
        $totalActivosFijos = $activos_fijos['totalActivosFijos'] ?? ($subtotalMachines ?? 0) + ($subtotalFincas ?? 0);
    @endphp

    <table>
        <tbody>
            <tr class="total-row">
                <th style="text-align:right">Total Activos Fijos (Máquinas + Fincas)</th>
                <th style="text-align:right">${{ number_format($totalActivosFijos ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ================== PASIVOS ================== --}}
    <h3>Pasivos</h3>

    @php
        $itemsPasivos = $pasivos['items'] ?? [];
        $totalPasivoCorto = collect($itemsPasivos)->sum('pasivo_corto');
        $totalPasivoLargo = collect($itemsPasivos)->sum('pasivo_largo');
        $totalPasivos = collect($itemsPasivos)->sum('total');
    @endphp

    <table>
        <thead>
            <tr>
                <th>A quien le debe</th>
                <th>Detalle de la deuda</th>
                <th style="text-align:right">Pasivo corto ($)</th>
                <th style="text-align:right">Pasivo largo ($)</th>
                <th style="text-align:right">Total ($)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($itemsPasivos as $item)
                <tr>
                    <td>{{ $item['entidad'] ?? '-' }}</td>
                    <td>{{ $item['concepto'] ?? '-' }}</td>
                    <td style="text-align:right">${{ number_format($item['pasivo_corto'] ?? 0, 2, '.', ',') }}</td>
                    <td style="text-align:right">${{ number_format($item['pasivo_largo'] ?? 0, 2, '.', ',') }}</td>
                    <td style="text-align:right">${{ number_format($item['total'] ?? 0, 2, '.', ',') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">No hay pasivos registrados.</td>
                </tr>
            @endforelse

            @if (!empty($itemsPasivos))
                <tr class="total-row">
                    <th colspan="2" style="text-align:right">Totales</th>
                    <th style="text-align:right">${{ number_format($totalPasivoCorto, 2, '.', ',') }}</th>
                    <th style="text-align:right">${{ number_format($totalPasivoLargo, 2, '.', ',') }}</th>
                    <th style="text-align:right">${{ number_format($totalPasivos, 2, '.', ',') }}</th>
                </tr>
            @endif
        </tbody>
    </table>

    {{-- ================== CAPITAL CONTABLE ================== --}}
    @php
        $totalActivosFijos = $activos_fijos['totalActivosFijos'] ?? 0;
        $totalActivosCirculantes = $activos_circulantes['totalActivosCirculantes'] ?? 0;
        $totalPasivos = $pasivos['total_pasivos'] ?? 0;

        $totalActivos = $totalActivosFijos + $totalActivosCirculantes;
        $capitalContable = $totalActivos - $totalPasivos;
    @endphp

    <h3>Capital Contable</h3>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th style="text-align:right">Valor</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Activo (Fijos + Circulantes)</td>
                <td style="text-align:right">${{ number_format($totalActivos, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td>Total Pasivo</td>
                <td style="text-align:right">-${{ number_format($totalPasivos, 2, '.', ',') }}</td>
            </tr>
            <tr class="total-row">
                <th style="text-align:right">Capital Contable</th>
                <th style="text-align:right">${{ number_format($capitalContable, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>


    {{-- ================== INGRESOS AGRÍCOLAS ================== --}}
    <h3>Ingresos Agrícolas</h3>
    @foreach ($ingresos['agricolas'] ?? [] as $anio => $datos)
        <h5>Año {{ $anio }}</h5>
        <div class="wide-table">
            <table>
                <thead>
                    <tr>
                        <th>Cultivo</th>
                        <th>Hectáreas</th>
                        <th>Costo por hectárea</th>
                        <th>Costo total</th>
                        <th>Rendimiento (ton/ha)</th>
                        <th>Precio por tonelada</th>
                        <th>Ingreso</th>
                        <th>Utilidad</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $items = $datos['items'] ?? [];
                        $totalCosto = collect($items)->sum('total');
                        $totalIngreso = collect($items)->sum('ingreso');
                        $totalUtilidad = collect($items)->sum('utilidad');
                    @endphp

                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item['cultivo']['name'] ?? 'Sin cultivo' }}</td>
                            <td>{{ number_format($item['hectareas'] ?? 0, 0, '.', ',') }}</td>
                            <td>${{ number_format($item['costo'] ?? 0, 2, '.', ',') }}</td>
                            <td>${{ number_format($item['total'] ?? 0, 2, '.', ',') }}</td>
                            <td>{{ number_format($item['toneladas'] ?? 0, 0, '.', ',') }}</td>
                            <td>${{ number_format($item['precio'] ?? 0, 2, '.', ',') }}</td>
                            <td>${{ number_format($item['ingreso'] ?? 0, 2, '.', ',') }}</td>
                            <td>${{ number_format($item['utilidad'] ?? 0, 2, '.', ',') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No hay ingresos agrícolas registrados.</td>
                        </tr>
                    @endforelse

                    {{-- Totales por año --}}
                    <tr class="total-row">
                        <th colspan="3" style="text-align:right">Totales Año {{ $anio }}</th>
                        <th style="text-align:right">${{ number_format($totalCosto, 2, '.', ',') }}</th>
                        <th></th>
                        <th></th>
                        <th style="text-align:right">${{ number_format($totalIngreso, 2, '.', ',') }}</th>
                        <th style="text-align:right">${{ number_format($totalUtilidad, 2, '.', ',') }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach


    {{-- ================== INGRESOS GANADEROS ================== --}}
    <h3>Ingresos Ganaderos</h3>
    @foreach ($ingresos['ganaderas'] ?? [] as $anio => $datos)
        <h5>Año {{ $anio }}</h5>
        <div class="wide-table">
            <table>
                <thead>
                    <tr>
                        <th>Ganado</th>
                        <th>Costo por cabeza</th>
                        <th># de cabezas</th>
                        <th>Total</th>
                        <th># cabezas para venta</th>
                        <th>Precio por kilo</th>
                        <th>Ingreso</th>
                        <th>Utilidad</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $items = $datos['items'] ?? [];
                        $totalCosto = collect($items)->sum('total');
                        $totalIngreso = collect($items)->sum('ingreso');
                        $totalUtilidad = collect($items)->sum('utilidad');
                    @endphp

                    @forelse ($items as $item)
                        <tr>
                            <td>{{ $item['ganado']['name'] ?? 'Sin ganado' }}</td>
                            <td>${{ number_format($item['costo'] ?? 0, 2, '.', ',') }}</td>
                            <td>{{ number_format($item['unidades'] ?? 0, 0, '.', ',') }}</td>
                            <td>${{ number_format($item['total'] ?? 0, 2, '.', ',') }}</td>
                            <td>{{ number_format($item['cantidad'] ?? 0, 0, '.', ',') }}</td>
                            <td>${{ number_format($item['precio'] ?? 0, 2, '.', ',') }}</td>
                            <td>${{ number_format($item['ingreso'] ?? 0, 2, '.', ',') }}</td>
                            <td>${{ number_format($item['utilidad'] ?? 0, 2, '.', ',') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">No hay ingresos ganaderos registrados.</td>
                        </tr>
                    @endforelse

                    {{-- Totales por año --}}
                    <tr class="total-row">
                        <th colspan="3" style="text-align:right">Totales Año {{ $anio }}</th>
                        <th style="text-align:right">${{ number_format($totalCosto, 2, '.', ',') }}</th>
                        <th></th>
                        <th></th>
                        <th style="text-align:right">${{ number_format($totalIngreso, 2, '.', ',') }}</th>
                        <th style="text-align:right">${{ number_format($totalUtilidad, 2, '.', ',') }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    @endforeach

    {{-- ================== INGRESOS DIRECTOS ================== --}}
    <h3>Otros ingresos</h3>

    @php
        // ✅ Datos del JSON
        $anioDirecto = $ingresosDirectos['anio'] ?? null;
        $itemsDirectos = $ingresosDirectos['items'] ?? [];
        $totalesDirectos = $ingresosDirectos['totales'] ?? [];

        // ✅ Cálculo de totales seguros
        $totalDirectoBruto = $totalesDirectos['total'] ?? collect($itemsDirectos)->sum('total');
        $totalDirectoCostos = $totalesDirectos['costos'] ?? collect($itemsDirectos)->sum('costos');
        $totalDirectoNeto = $totalesDirectos['neto'] ?? collect($itemsDirectos)->sum('neto');
    @endphp

    @if (!empty($itemsDirectos))
        <h5>Año {{ $anioDirecto ?? 'Sin año' }}</h5>
        <div class="wide-table">
            <table>
                <thead>
                    <tr>
                        <th>Tipo de ingreso</th>
                        <th>Monto mensual</th>
                        <th>Meses</th>
                        <th>Total bruto</th>
                        <th>Costos</th>
                        <th>Ingreso neto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($itemsDirectos as $item)
                        <tr>
                            <td>{{ $item['tipo'] ?? '-' }}</td>
                            <td style="text-align:right">${{ number_format($item['monto'] ?? 0, 2, '.', ',') }}</td>
                            <td style="text-align:right">{{ $item['months'] ?? '-' }}</td>
                            <td style="text-align:right">${{ number_format($item['total'] ?? 0, 2, '.', ',') }}</td>
                            <td style="text-align:right">${{ number_format($item['costos'] ?? 0, 2, '.', ',') }}</td>
                            <td style="text-align:right">${{ number_format($item['neto'] ?? 0, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach

                    <tr class="total-row">
                        <th colspan="3" style="text-align:right">Totales</th>
                        <th style="text-align:right">${{ number_format($totalDirectoBruto, 2, '.', ',') }}</th>
                        <th style="text-align:right">${{ number_format($totalDirectoCostos, 2, '.', ',') }}</th>
                        <th style="text-align:right">${{ number_format($totalDirectoNeto, 2, '.', ',') }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    @else
        <p>No hay otros directos registrados.</p>
    @endif

    {{-- ================== OTROS GASTOS ================== --}}
    <h3>Otros Gastos</h3>

    {{-- ======= FINCAS ======= --}}
    <h4>Costos de renta (Fincas)</h4>
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Superficie</th>
                <th>Costo</th>
            </tr>
        </thead>
        <tbody>
            @if (!empty($otros_gastos['fincas']['items']))
                @foreach ($otros_gastos['fincas']['items'] as $finca)
                    <tr>
                        <td>{{ $finca['nombre'] ?? '-' }}</td>
                        <td>{{ $finca['descripcion'] ?? '-' }}</td>
                        <td>${{ number_format($finca['costo'] ?? 0, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="3">No hay registros de fincas.</td>
                </tr>
            @endif
            <tr class="total-row">
                <th colspan="2">Total Costos Fincas</th>
                <th>${{ number_format($otros_gastos['fincas']['total_costos_fincas'] ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ======= GASTOS FAMILIARES ======= --}}
    <h4>Gastos familiares</h4>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gastos familiares</td>
                <td>${{ number_format($otros_gastos['analitica']['gastos'] ?? 0, 2, '.', ',') }}</td>
            </tr>
            <tr class="total-row">
                <th>Total Otros Gastos</th>
                <th>${{ number_format($otros_gastos['total_otros_gastos'] ?? 0, 2, '.', ',') }}</th>
            </tr>
        </tbody>
    </table>

    {{-- ================== RESUMEN FINAL ================== --}}
    @php
        // === Totales de ingresos y costos agrícolas ===
        $totalCostoAgricola = 0;
        $totalIngresoAgricola = 0;

        foreach ($ingresos['agricolas'] ?? [] as $anio => $datos) {
            $totalCostoAgricola += collect($datos['items'] ?? [])->sum('total');
            $totalIngresoAgricola += collect($datos['items'] ?? [])->sum('ingreso');
        }

        // === Totales de ingresos y costos ganaderos ===
        $totalCostoGanadero = 0;
        $totalIngresoGanadero = 0;

        foreach ($ingresos['ganaderas'] ?? [] as $anio => $datos) {
            $totalCostoGanadero += collect($datos['items'] ?? [])->sum('total');
            $totalIngresoGanadero += collect($datos['items'] ?? [])->sum('ingreso');
        }

        // === Ingresos directos (desde su estructura propia) ===
        $itemsDirectos = $ingresosDirectos['items'] ?? [];
        $totalesDirectos = $ingresosDirectos['totales'] ?? [];

        // Si existen totales en el JSON los usa; si no, los calcula
        $totalIngresoDirecto = $totalesDirectos['total'] ?? collect($itemsDirectos)->sum('total');
        $totalCostoDirecto = $totalesDirectos['costos'] ?? collect($itemsDirectos)->sum('costos');
        $totalUtilidadDirecta = $totalesDirectos['neto'] ?? collect($itemsDirectos)->sum('neto');

        // === Totales generales ===
        $totalCostos = $totalCostoAgricola + $totalCostoGanadero + $totalCostoDirecto;
        $totalIngresos = $totalIngresoAgricola + $totalIngresoGanadero + $totalIngresoDirecto;

        $totalOtrosGastos = $otros_gastos['total_otros_gastos'] ?? 0;

        // === Resultado final ===
        // Si prefieres mostrar utilidad neta real, podrías usar ($totalUtilidadDirecta + utilidades agrícolas + ganaderas)
        $resultadoNeto = $totalIngresos - ($totalCostos + $totalOtrosGastos);
    @endphp

    <h3>Resumen Final</h3>
    <table>
        <thead>
            <tr>
                <th>Concepto</th>
                <th style="text-align:right">Valor ($)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Ingresos Agrícolas</strong></td>
                <td style="text-align:right">${{ number_format($totalIngresoAgricola, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td><strong>Ingresos Ganaderos</strong></td>
                <td style="text-align:right">${{ number_format($totalIngresoGanadero, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td><strong>Otros ingresos</strong></td>
                <td style="text-align:right">${{ number_format($totalIngresoDirecto, 2, '.', ',') }}</td>
            </tr>

            <tr class="total-row">
                <th style="text-align:right">Total Ingresos</th>
                <th style="text-align:right">${{ number_format($totalIngresos, 2, '.', ',') }}</th>
            </tr>

            <tr>
                <td>Costos Agrícolas</td>
                <td style="text-align:right">-${{ number_format($totalCostoAgricola, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td>Costos Ganaderos</td>
                <td style="text-align:right">-${{ number_format($totalCostoGanadero, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td>Costos Directos</td>
                <td style="text-align:right">-${{ number_format($totalCostoDirecto, 2, '.', ',') }}</td>
            </tr>
            <tr>
                <td>Otros Gastos</td>
                <td style="text-align:right">-${{ number_format($totalOtrosGastos, 2, '.', ',') }}</td>
            </tr>

            <tr class="total-row">
                <th style="text-align:right">Total gastos (Costos + Gastos)</th>
                <th style="text-align:right">-${{ number_format($totalCostos + $totalOtrosGastos, 2, '.', ',') }}</th>
            </tr>

            <tr class="total-row">
                <th style="text-align:right">Resultado Neto (Ingresos - Egresos)</th>
                <th style="text-align:right">
                    ${{ number_format($resultadoNeto, 2, '.', ',') }}
                </th>
            </tr>
        </tbody>
    </table>

</body>

</html>

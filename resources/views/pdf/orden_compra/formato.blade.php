<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de Compra {{ $folioOC }}</title>
    <style>
        @page {
            margin: 0.8cm 1cm 0.8cm 1cm;
            font-family: Arial, Helvetica, sans-serif;
            size: letter portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8.5px;
            color: #111;
            margin: 0;
            padding: 0;
            line-height: 1.2;
        }

        /* Marca de agua fija centrada en el fondo */
        #watermark {
            position: fixed;
            top: 38%;
            left: 28%;
            width: 250px;
            opacity: 0.09;
            z-index: -1000;
            text-align: center;
        }

        #watermark img {
            width: 100%;
            height: auto;
        }

        /* 1. Encabezado de la Empresa y Folio */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .header-table td {
            vertical-align: middle;
            padding: 0;
        }

        .company-header {
            text-align: center;
        }

        .company-title {
            font-size: 13px;
            font-weight: bold;
            color: #111;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .company-info {
            font-size: 8px;
            color: #333;
            line-height: 1.25;
        }

        .oc-header {
            text-align: right;
            width: 170px;
            vertical-align: top !important;
            padding-top: 4px !important;
        }

        .oc-label {
            font-size: 13px;
            font-weight: bold;
            color: #111;
            letter-spacing: 0.5px;
        }

        .oc-folio {
            font-size: 22px;
            font-weight: bold;
            color: #b71c1c;
            margin-top: 2px;
            letter-spacing: 1px;
        }

        /* 2. Bloque Proveedor y Logística */
        .info-box {
            width: 100%;
            border: 1.5px solid #000;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .info-box td {
            padding: 4px 6px;
            font-size: 8px;
            vertical-align: top;
        }

        .info-left {
            width: 58%;
            border-right: 1.5px solid #000;
        }

        .info-right {
            width: 42%;
            padding: 0 !important;
        }

        .info-right-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-right-table td {
            padding: 3px 6px;
            font-size: 8px;
        }

        .field-label {
            font-weight: bold;
            color: #111;
            display: inline-block;
        }

        .field-value {
            color: #111;
        }

        /* 3. Tabla Principal de Conceptos y Cierre de Orden */
        .main-order-table {
            width: 100%;
            border: 1.5px solid #000;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .main-order-table th {
            background-color: #1a1a1a;
            color: #ffffff;
            font-size: 8.5px;
            font-weight: bold;
            text-align: center;
            padding: 4px 3px;
            border: 1px solid #1a1a1a;
            letter-spacing: 0.5px;
        }

        .col-cant {
            width: 8%;
            text-align: center;
            border-right: 1px solid #000;
        }

        .col-unit {
            width: 12%;
            text-align: center;
            border-right: 1px solid #000;
        }

        .col-desc {
            width: 50%;
            text-align: left;
            border-right: 1px solid #000;
        }

        .col-unitprice {
            width: 15%;
            text-align: right;
            border-right: 1px solid #000;
        }

        .col-amount {
            width: 15%;
            text-align: right;
        }

        .item-row td {
            padding: 5px 6px;
            font-size: 8px;
            vertical-align: top;
            border-bottom: 0.5px dashed #bbb;
        }

        .item-title {
            font-weight: bold;
            color: #111;
            font-size: 8.5px;
        }

        .item-desc {
            color: #222;
            margin-top: 1px;
        }

        .item-spec {
            font-size: 7.5px;
            color: #333;
            margin-top: 1px;
        }

        .item-unit-info {
            font-size: 7.5px;
            color: #444;
            margin-top: 2px;
        }

        .sat-code {
            font-size: 7px;
            color: #555;
        }

        /* Fila del cuerpo inferior: autorización, datos y totales */
        .middle-body-row td {
            vertical-align: top;
            border-bottom: 1.5px solid #000;
        }

        .middle-desc-cell {
            padding: 12px 8px 8px 8px;
            vertical-align: top !important;
            border-right: 1px solid #000;
        }

        .auth-banner-container {
            text-align: center;
            margin-bottom: 8px;
        }

        .auth-banner {
            display: inline-block;
            border: 1.5px solid #000;
            padding: 3px 18px;
            font-size: 9.5px;
            font-weight: bold;
            background-color: #ffffff;
            letter-spacing: 0.4px;
        }

        .inner-details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inner-details-table td {
            vertical-align: top;
            padding: 1px 2px;
            font-size: 8px;
            line-height: 1.35;
        }

        .label-td {
            width: 32%;
            font-weight: bold;
            color: #111;
        }

        .disclaimer-text {
            font-size: 7.5px;
            font-style: italic;
            color: #333;
            margin-top: 10px;
        }

        .totals-col-cell {
            vertical-align: bottom !important;
            padding: 8px 6px 6px 6px;
            border-right: 1.5px solid #000;
        }

        .financial-table {
            width: 100%;
            border-collapse: collapse;
        }

        .financial-table td {
            padding: 2.5px 2px;
            font-size: 8.5px;
        }

        .fin-label {
            text-align: right;
            font-weight: bold;
            width: 50%;
        }

        .fin-amount {
            text-align: right;
            width: 50%;
        }

        .total-row td {
            font-size: 10.5px;
            font-weight: bold;
            border-top: 1.5px solid #000;
            border-bottom: 2.5px double #000;
            padding: 4px 2px;
        }

        /* 4. Cuadro de firmas inferior (3 columnas exactas como el formato real) */
        .signatures-box {
            width: 100%;
            border: 1.5px solid #000;
            border-collapse: collapse;
            margin-top: 2px;
        }

        .signatures-box td {
            border: 1px solid #000;
            padding: 4px 6px;
            vertical-align: top;
            font-size: 8px;
        }

        .sig-col-person {
            width: 33.33%;
            height: 62px;
            text-align: center;
        }

        .sig-title {
            font-weight: bold;
            font-size: 8px;
            color: #111;
            text-align: left;
            margin-bottom: 2px;
        }

        .sig-person-space {
            height: 28px;
        }

        .sig-name {
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
        }

        .sig-name-center {
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border-top: 0.5px solid #888;
            display: inline-block;
            width: 90%;
            padding-top: 2px;
        }
    </style>
</head>
<body>

    @php
        $logoJdPath = public_path('img/logo-jd.png');
        if (!file_exists($logoJdPath)) {
            $logoJdPath = public_path('img/logo-jd-dark.png');
        }
        $logoJdBase64 = file_exists($logoJdPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoJdPath)) : null;
    @endphp

    <!-- MARCA DE AGUA EN EL FONDO (DOMPDF FIXED) -->
    @if($logoJdBase64)
        <div id="watermark">
            <img src="{{ $logoJdBase64 }}" />
        </div>
    @endif

    <!-- 1. ENCABEZADO DE LA EMPRESA Y FOLIO -->
    <table class="header-table">
        <tr>
            <td class="company-header">
                <div class="company-title">EQUIPOS Y TRACTORES DEL BAJIO, S.A. DE C.V.</div>
                <div class="company-info">Carretera Panamericana Celaya - Salamanca Km. 61</div>
                <div class="company-info">1ra. Fracc. Crespo C.P. 38120 Celaya, Gto.</div>
                <div class="company-info">Tel. 461-614-2323, 461-614-2324, 461-614-2325</div>
                <div class="company-info" style="font-weight: bold;">R.F.C.: ETB-860812-I23</div>
            </td>
            <td class="oc-header">
                <div class="oc-label">ORDEN DE COMPRA</div>
                <div class="oc-folio">{{ $folioOC }}</div>
            </td>
        </tr>
    </table>

    <!-- 2. DATOS DEL PROVEEDOR Y LOGÍSTICA -->
    <table class="info-box">
        <tr>
            <td class="info-left">
                <div>
                    <span class="field-label">PROVEEDOR:</span>
                    <span class="field-value" style="font-weight: bold; font-size: 8.5px;">
                        {{ $proveedor?->nombre ?: 'NO ASIGNADO' }}
                    </span>
                </div>
                <div style="margin-top: 3px;">
                    <span class="field-label">DOMICILIO:</span>
                    <span class="field-value">{{ $proveedor?->domicilio ?: '-' }}</span>
                </div>
                <div style="margin-top: 3px;">
                    <span class="field-label">CIUDAD:</span>
                    <span class="field-value">{{ $proveedor?->ciudad ?: 'Celaya, Guanajuato, MÉXICO' }}</span>
                </div>
            </td>
            <td class="info-right">
                <table class="info-right-table">
                    <tr>
                        <td style="width: 50%; border-right: 1px solid #000; border-bottom: 1px solid #000;">
                            <span class="field-label">FECHA DE ENTREGA:</span>
                            <span class="field-value">{{ $fechaEntrega }}</span>
                        </td>
                        <td style="width: 50%; border-bottom: 1px solid #000;">
                            <span class="field-label">EMBARQUESE A:</span>
                            <span class="field-value">{{ $embarqueseA }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <span class="field-label">CONDICIONES DE PAGO:</span>
                            <span class="field-value" style="font-weight: bold;">{{ $condicionesPago }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- 3. TABLA PRINCIPAL DE CONCEPTOS, DATOS CENTRALES Y TOTALES -->
    <table class="main-order-table">
        <thead>
            <tr>
                <th class="col-cant">CANTIDAD</th>
                <th class="col-unit">UNIDAD</th>
                <th class="col-desc">DESCRIPCION</th>
                <th class="col-unitprice">PRECIO UNIT.</th>
                <th class="col-amount">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orden->detalles as $det)
                @php
                    $isRefaccion = ($det->servicio?->tipoServicio?->nombre === 'Refacciones');
                    $unidadSat = $isRefaccion ? 'PIEZA' : 'SERV';
                    $claveSat = $isRefaccion ? 'H87' : 'E48';
                @endphp
                <tr class="item-row">
                    <td class="col-cant" style="font-weight: bold;">
                        {{ $det->cantidad ?: 1 }}
                    </td>
                    <td class="col-unit">
                        <div>{{ $unidadSat }}</div>
                        <div class="sat-code">{{ $claveSat }}</div>
                    </td>
                    <td class="col-desc">
                        <div class="item-title">
                            {{ $det->servicio?->nombre ?: 'Servicio Automotriz' }}
                        </div>
                        @if(!empty($det->descripcion))
                            <div class="item-desc">
                                {{ $det->descripcion }}
                            </div>
                        @endif
                        @if($det->llanta_anchura)
                            <div class="item-spec">
                                Medida: {{ $det->llanta_anchura }}/{{ $det->llanta_altura }} R{{ $det->llanta_rin }}
                            </div>
                        @endif
                        <div class="item-unit-info">
                            Unidad: <b>{{ $orden->vehicle?->placas ?: 'S/P' }}</b>
                            @if($orden->vehicle?->estatus?->nombre) ({{ $orden->vehicle->estatus->nombre }}) @endif
                            @if($orden->vehicle?->serie) &bull; Serie: {{ $orden->vehicle->serie }} @endif
                        </div>
                    </td>
                    <td class="col-unitprice">
                        ${{ number_format($det->precio_unitario ?: $det->subtotal, 2) }}
                    </td>
                    <td class="col-amount" style="font-weight: bold;">
                        ${{ number_format($det->subtotal, 2) }}
                    </td>
                </tr>
            @endforeach

            <!-- FILA INFERIOR: COLUMNAS VERTICALES COMPLETAS, AUTORIZACIÓN, DATOS Y TOTALES -->
            <tr class="middle-body-row">
                <td class="col-cant"></td>
                <td class="col-unit"></td>
                <td class="col-desc middle-desc-cell">
                    <!-- Cuadro con Folio de Autorización -->
                    <div class="auth-banner-container">
                        <div class="auth-banner">
                            N° Autorización: {{ $orden->autorizacion_compras ?: 'N/A' }}
                        </div>
                    </div>

                    <!-- Datos Operativos y de Gasto -->
                    <table class="inner-details-table">
                        <tr>
                            <td class="label-td">PAGADO POR:</td>
                            <td>{{ $orden->sucursalCargo?->nombre ?: ($orden->vehicle?->sucursal?->nombre ?: 'MATRIZ') }}</td>
                        </tr>
                        <tr>
                            <td class="label-td">REFERENCIA:</td>
                            <td>{{ $orden->vehicle?->placas ?: 'S/P' }}@if($orden->vehicle?->serie) - {{ $orden->vehicle->serie }}@endif</td>
                        </tr>
                        <tr>
                            <td class="label-td">CONCEPTO:</td>
                            <td>{{ $firstDetalle?->servicio?->tipoServicio?->nombre ?: 'MANTENIMIENTO VEHICULAR' }}</td>
                        </tr>
                        <tr>
                            <td class="label-td">SOLICITA:</td>
                            <td style="font-weight: bold;">{{ $orden->solicitante?->nombreCompleto ?: 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="label-td">CARGO A:</td>
                            <td>{{ $orden->departamentoCargo?->nombre ?: ($orden->vehicle?->departamento?->nombre ?: 'CORPORATIVO') }}</td>
                        </tr>
                    </table>

                    <!-- Datos Fiscales -->
                    <table class="inner-details-table" style="margin-top: 8px;">
                        <tr>
                            <td class="label-td">USO DEL CFDI:</td>
                            <td>
                                @if($firstDetalle?->usoCfdi)
                                    {{ $firstDetalle->usoCfdi->clave }} - {{ $firstDetalle->usoCfdi->nombre }}
                                @else
                                    G03 - GASTOS EN GENERAL
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td">FORMA DE PAGO:</td>
                            <td>
                                @if($firstDetalle?->formaPago)
                                    {{ $firstDetalle->formaPago->clave }} - {{ $firstDetalle->formaPago->nombre }}
                                @else
                                    TRANSFERENCIA
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="label-td">MÉTODO DE PAGO:</td>
                            <td>
                                @if($firstDetalle?->metodoPago)
                                    {{ $firstDetalle->metodoPago->clave }} - {{ $firstDetalle->metodoPago->nombre }}
                                @else
                                    PUE - PAGO EN UNA SOLA EXHIBICIÓN
                                @endif
                            </td>
                        </tr>
                    </table>

                    <div class="disclaimer-text">
                        Para todo trámite sírvase hacer referencia al número de esta Orden de Compra
                    </div>
                </td>

                <td colspan="2" class="totals-col-cell">
                    <table class="financial-table">
                        <tr>
                            <td class="fin-label">SUBTOTAL:</td>
                            <td class="fin-amount">${{ number_format($subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="fin-label">IVA:</td>
                            <td class="fin-amount">${{ number_format($iva, 2) }}</td>
                        </tr>
                        <tr class="total-row">
                            <td class="fin-label">Total:</td>
                            <td class="fin-amount" style="font-weight: bold;">${{ number_format($total, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- 4. CUADRO DE FIRMAS / RESPONSABLES -->
    <table class="signatures-box">
        <tr>
            <!-- Columna 1: ELABORA -->
            <td class="sig-col-person">
                <div class="sig-title">ELABORA:</div>
                <div class="sig-person-space"></div>
                <div class="sig-name-center">{{ $elaboraNombre }}</div>
            </td>

            <!-- Columna 2: Vo. Bo. -->
            <td class="sig-col-person">
                <div class="sig-title">Vo. Bo.</div>
                <div class="sig-person-space"></div>
                <div class="sig-name-center">{{ $voBoNombre }}</div>
            </td>

            <!-- Columna 3: DEPARTAMENTO DE COMPRAS -->
            <td class="sig-col-person">
                <div class="sig-title">DEPARTAMENTO DE COMPRAS</div>
                <div class="sig-person-space"></div>
                <div class="sig-name-center">{{ $comprasNombre }}</div>
            </td>
        </tr>
    </table>

</body>
</html>

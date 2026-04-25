<html>

<head>
    <meta charset="utf-8" />
    <title>Equipos y Tractores del Bajio</title>
    <style>
        @page {
            margin: 0.75cm 0cm;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        body {
            margin: 3.6cm 0.25cm;
        }

        header {
            position: fixed;
            top: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2.5cm;

            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 0.8em;
            line-height: 1em;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
            padding-top: 2rem;
            padding-left: 1rem;
            padding-right: 1rem;

        }

        header table {
            height: inherit;
            width: 100%;
            line-height: inherit;
        }

        header table td {
            vertical-align: top;
        }

        footer {
            position: fixed;
            bottom: 0cm;
            left: 0cm;
            right: 0cm;
            height: 2cm;

            /** Extra personal styles **/
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 0.8em;
            line-height: 1em;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #555;
            background-color: #123f0283;
            color: white;
            text-align: center;
            line-height: 1rem;
            padding-bottom: 2rem;
            padding-left: 1rem;
            padding-right: 1rem;

        }

        footer table {
            height: inherit;
            width: 100%;
            line-height: inherit;
        }

        footer table td {
            vertical-align: middle;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
    <style>
        .invoice-box {
            width: 100%;
            /* max-width: 300px; */
            /* margin: auto; */
            /* padding: 30px; */
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            font-size: 0.8em;
            line-height: 1em;
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #555;
        }

        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }

        .invoice-box table td {
            padding: 5px;
            /* border: 1px solid #df1414; */
            vertical-align: top;
        }

        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }

        .invoice-box table tr.top table td {
            padding-bottom: 20px;
            /* border: 1px solid #3614df; */
        }

        .invoice-box table tr.top table td.title {
            font-size: 45px;
            line-height: 45px;
            color: #333;
        }

        .invoice-box table tr.information table td {
            padding: 5px;
        }

        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            vertical-align: middle;
        }

        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }

        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
            /* border: 2px solid #eeb004; */
        }

        .invoice-box table tr.item.last td {
            border-bottom: none;
            padding-top: 1cm;
            /* border: 3px solid #d5df14; */
        }

        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }

        @media only screen and (max-width: 400px) {
            .invoice-box table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            .invoice-box table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            header table tr.top table td {
                width: 100%;
                display: block;
                text-align: center;
            }

            header table tr.information table td {
                width: 100%;
                display: block;
                text-align: center;
            }
        }

        /** RTL **/
        .invoice-box.rtl {
            direction: rtl;
            font-family: Tahoma, 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
        }

        .invoice-box.rtl table {
            text-align: right;
        }

        .invoice-box.rtl table tr td:nth-child(2) {
            text-align: left;
        }

        .swiss {
            max-width: 600px;
            opacity: 0.2;
        }

        .position-ref {
            position: relative;
        }
    </style>

    @php

        function convertExchange($value, $currentTo, $currentFrom, $exchange)
        {
            if ($currentTo == $currentFrom) {
                return $value;
            } elseif ($currentTo == 'USD' && $currentFrom == 'MXN') {
                return $value / $exchange;
            } elseif ($currentTo == 'MXN' && $currentFrom == 'USD') {
                return $value * $exchange;
            }
        }

    @endphp

</head>

<body>
    <script type="text/php">
        if ( isset($pdf) ) {
            $pdf->page_script('
                $font = $fontMetrics->get_font("DejaVu Sans", "normal");
                $pdf->text(520, 25, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 10);
            ');
        }
    </script>
    <header>


        <table>
            <tr class="top">
                <td style="width: 120px">
                    <img src="img/logo.png" style="width: 180px" />
                </td>
                <td colspan="4"
                    style="text-align: center; width: 350px; font-size: 9pt; line-height: 1rem; text-transform: uppercase;">
                    <b style="font-size: 12pt;">Equipos y Tractores del Bajio</b><br />
                    Carretera Panamericana Celaya - Salamanca Km. 61<br />
                    1ra Fracc. crespo C.P. 38120 Celaya, Gto.<br />
                    Tel. 461-614-2323, 461-614-2324, 461-614-2325<br />
                    <b>R.F.C.: ETB-860812-I23</b>
                </td>

                {{-- <!-- <td style="text-align: right; width: 200px; line-height: 1rem;">
                    <b>Cotizacion: </b># {{ str_pad($data->id, 5, '100', STR_PAD_LEFT) }}<br />
                    LEAD: # {{ str_pad($data->tracking->id, 4, '0', STR_PAD_LEFT) }}<br />
                    Creacion: {{ $data->created_at }}<br />
                    Vencimiento: {{ $data->date_due }}
                </td> --> --}}
                <td style="text-align: center; width: 100px;">
                    <img src="img/logo-jd-dark.png" style="width: 90;" />
                </td>
            </tr>
        </table>
        <div style="text-align: center; height: 50%;">
            <img class="swiss" style="margin-top: 250px;" src="img/logo-dark.png">
        </div>

    </header>
    <footer>
        <!-- <div>
            El Numero de Orden de Compra debe aparecer siempre en la Factura, Etiquetas y Notas de Envio
            correspondientes, inclusive en los embarques a terceros. La Factura debera contener la misma
            infomacion (linea por linea) que la mostrada en esta Orden de Compra, Para entregas contactar al
            Requisitor.
        </div> -->
        <table>
            <tr class="top">
                <td style="width: 100px;text-align: center;">
                    <img src="img/etbsa-tree.png" style="width: 64px" />
                </td>
                <td colspan="4"
                    style="text-align: left; width: 370px; font-size: 0.7rem; line-height: 0.8rem; font-style: italic;color: #ddd;">
                    <b
                        style="color: #ffffff; font-size: 12pt; text-transform: uppercase;font-weight: bold; font-family: Arial, Helvetica, sans-serif;">Plan
                        mundial de reforestacion</b><br>
                    ¡Juntos por un México más Verde!<br>

                    Por cada máquina entregada, ETBSA junto con John Deere Plantará 3 árboles con el objetivo de lograr
                    Un Millón de árboles en 2030.

                    Considere el medio ambiente antes de imprimir este documento.
                    Consulte nuestros lineamisntos y códigos de proveedores en nuestra paqina (URL).
                </td>

                <td style="text-align: right; width: 200px; line-height: 1rem;">
                    <b>Cotizacion: </b># {{ str_pad($quote->folio, 5, '100', STR_PAD_LEFT) }}<br />
                    {{-- LEAD: # {{ str_pad($data->tracking->id, 4, '0', STR_PAD_LEFT) }}<br /> --}}
                    Creacion: {{ $quote->updated_at->format('d/m/Y') }}<br />
                    Vencimiento: {{ $quote->updated_at->copy()->addDays(30)->format('d/m/Y') }}
                </td>
            </tr>
        </table>
    </footer>

    <main>
        <div class="invoice-box">
            <table cellpadding="0" cellspacing="0">
                <tr class="information">
                    <td colspan="2">
                        <table>
                            <tr class="heading">
                                <td>
                                    Vendedor: <br>
                                    Email:
                                </td>
                                <td style="text-align: left;">
                                    {{ $quote->vendedor->nombreCompleto }} <br>
                                    {{ $quote->vendedor->correo_institucional }}
                                </td>
                            </tr>

                            <tr class="heading">
                                <td>
                                    Atencion a: <br />
                                    Domicilio:<br>
                                    Telefono:
                                </td>
                                <td style="text-align: left;">
                                    <b>{{ $quote->prospecto->nombre ?? $quote->cliente->nombre }}</b><br />
                                    {{ $quote->cliente->town->name ?? '' }},
                                    {{ $quote->cliente->colonia ?? ' ' }},
                                    {{ $quote->cliente->calle ?? ' ' }},
                                    {{ $quote->cliente->codigo_postal ?? ' ' }},
                                    <br>
                                    <b>{{ $quote->prospecto->telefono ?? $quote->cliente->telefono }}</b>
                                </td>

                            </tr>
                        </table>
                    </td>
                    <td colspan="6" style="text-align: right;">
                        <img src="img/QR_VoBo.jpg" style="width: 100px ; height: 100px;" />
                    </td>
                </tr>

                <tr class="information">
                    <td colspan="2">
                        <table>
                            <tr class="heading">
                                <td>Condicion de Pago</td>
                                <td style="text-align: left;"> {{ $quote->condicionPago->name }} </td>
                            </tr>
                        </table>
                    </td>
                    <td colspan="6"></td>
                </tr>

                <tr class="heading">
                    <td colspan="1" style="text-align: center;">Cant.</td>
                    <td colspan="3" style="text-align: center;">SKU. / Producto</td>
                    <td colspan="2" style="text-align: right;">Precio U.</td>
                    <td colspan="2" style="text-align: right;">Subtotal</td>
                </tr>
                {{-- producto --}}
                @foreach ($quote->detalles as $detalle)
                    <tr class="item">
                        <td colspan="1" style="text-align: center;">
                            {{ $detalle->cantidad }}
                        </td>

                        <td colspan="3" style="width: 324px;">
                            <div style="text-align: start">
                                <b>{{ $detalle->productos->sku }}</b>
                                ({{ $detalle->productos->name }})
                            </div>
                            <div style="text-align: justify; font-size: 0.5rem; padding-top: 5px;">
                                {{ $detalle->productos->description }}
                            </div>
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{ '$ ' .
                                number_format(
                                    convertExchange($detalle->precio_unidad, $quote->currency->name, 'MXN', $quote->tarifa_cambio),
                                    2,
                                    '.',
                                    ',',
                                ) }}

                            {{ $quote->currency->name }}
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{ '$ ' .
                                number_format(
                                    convertExchange(
                                        $detalle->cantidad * $detalle->precio_unidad,
                                        $quote->currency->name,
                                        'MXN',
                                        $quote->tarifa_cambio,
                                    ),
                                    2,
                                    '.',
                                    ',',
                                ) }}
                            {{ $quote->currency->name }}</td>
                    </tr>
                @endforeach

                {{-- extra --}}
                @foreach ($quote->extras as $extra)
                    <tr class="item">
                        <td colspan="1" style="text-align: center;">
                            {{ $extra->cantidad }}
                        </td>

                        <td colspan="3" style="width: 324px;">
                            <div style="text-align: start">
                                <b>{{ $extra->item->nro_parte }}</b>
                                ({{ $extra->item->descripcion }})
                            </div>
                            {{-- <div style="text-align: justify; font-size: 0.5rem; padding-top: 5px;">
                                {{ $extra->item->description }}
                            </div> --}}
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{ '$ ' .
                                number_format(
                                    convertExchange($extra->precio_unidad, $quote->currency->name, 'MXN', $quote->tarifa_cambio),
                                    2,
                                    '.',
                                    ',',
                                ) }}

                            {{ $quote->currency->name }}
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{ '$ ' .
                                number_format(
                                    convertExchange($extra->cantidad * $extra->precio_unidad, $quote->currency->name, 'MXN', $quote->tarifa_cambio),
                                    2,
                                    '.',
                                    ',',
                                ) }}
                            {{ $quote->currency->name }}</td>
                    </tr>
                @endforeach

                <tr class="total" style="text-align: end;">
                    <td colspan="4"></td>
                    <td colspan="2">Subtotal:</td>
                    <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                        {{ '$ ' . number_format($quote->subtotal, 2, '.', ',') }}
                        {{ $quote->currency->name }}
                    </td>
                </tr>
                <tr class="total" style="text-align: end;">
                    <td colspan="4"></td>
                    <td colspan="2">IVA (16%):</td>
                    <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                        {{ '$ ' . number_format($quote->iva_monto, 2, '.', ',') }}
                        {{ $quote->currency->name }}
                    </td>
                </tr>
                @if ($quote->descuento > 0)
                    <tr class="total" style="text-align: end;">
                        <td colspan="4"></td>
                        <td colspan="2">Descuento:</td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{ '$ ' . number_format($quote->descuento, 2, '.', ',') }}
                            {{ $quote->currency->name }}
                        </td>
                    </tr>
                @endif

                <tr class="total" style="text-align: right">
                    <td colspan="4"></td>
                    <td colspan="2">T.C.: </td>
                    <td colspan="2" style="font-size: 0.8rem;">
                        {{ '$ ' . number_format($quote->tarifa_cambio, 2, '.', ',') }}
                        MXN </td>
                </tr>
                <tr class="total" style="text-align: end;">
                    <td colspan="4"></td>
                    <td colspan="2">Total:</td>
                    <td colspan="2" style="text-align: right;font-size: 0.8rem;font-weight: bold;">
                        {{ '$ ' . number_format($quote->total, 2, '.', ',') }}
                        {{ $quote->currency->name }}
                    </td>
                </tr>
                <tr class="information" style="font-size: 0.7em; line-height: 1em;text-transform: uppercase;">
                    <td colspan="8" style="padding-top: 40px;">
                        <table>
                            @if ($quote->notas)
                                <tr style="padding-bottom: 0px;">
                                    <td style="padding-bottom: 5px; font-size: 0.8rem;">
                                        <b>NOTA del Vendedor:</b><br /><br />
                                        {{ $quote->notas }}
                                    </td>
                                </tr>
                            @endif
                            <tr style="padding-bottom: 0px;">
                                <td style="padding-bottom: 0px;">
                                    Costos de Envío / Flete se definen de acuerdo a la zona <b>**NO INCLUIDO EN EL
                                        PRECIO PUBLICADO**</b>, El costo del producto puede variar sin previo aviso,
                                    favor de verificar con nosotros antes de apartar. <b>**ETBSA Nunca le Solicitara que
                                        deposite dinero sin antes haber visto la Maquinaria**</b> <b>**Este Documento NO
                                        es un Comprobante de Pago**</b><br>
                                    <!-- Táctica de mano de obra con el precio normal de los 22mas iva y automáticamente el
                                    descuento quedando a $ 8120 y que te arroje un cupón que diga 20 % de descuento en
                                    el compra del paquete de refacciones de mantenimiento <br> -->
                                    <br> ETBSA realiza todas
                                    las operaciones y tramites de venta de Maquinaria, dentro de sus instalaciones
                                    oficiales, las ubicaciones las puede consultar en la pagina oficial <a
                                        href="https://www.etbsa.com.mx/"> www.etbsa.com.mx </a> <br> <b><br> **La
                                        presente
                                        cotización tiene una vigencia de 30 días a partir de la fecha de expedición de
                                        la misma** </b>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            </td>
            </tr>
            </table>
        </div>

    </main>


</body>

</html>

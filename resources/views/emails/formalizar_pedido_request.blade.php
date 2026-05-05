<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Solicitud de Formalización de Pedido</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            background-color: #f4f6f8;
            margin: 0;
            padding: 0;
        }

        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 600px;
            margin: 30px auto;
            border-top: 6px solid #367c2b;
        }

        h2 {
            color: #367c2b;
            margin-bottom: 12px;
        }

        .data {
            background-color: #f3f6fa;
            padding: 12px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .data p {
            margin: 6px 0;
        }

        .footer {
            margin-top: 20px;
            font-size: 13px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .brand {
            font-weight: bold;
            color: #367c2b;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Solicitud de Formalización de Pedido</h2>

        <p>
            Por medio del presente, el empleado
            <strong>{{ $tracking->vendedor->nombreCompleto }}</strong>
            solicita la formalización del siguiente pedido para seguir con el proceso de venta:
        </p>

        <div class="data">
            <h3>Datos del Cliente</h3>

            <p><strong>ID:</strong> {{ $tracking->cliente->id }}</p>
            <p><strong>Nombre:</strong> {{ $tracking->cliente->nombre }}</p>
            <p><strong>RFC:</strong> {{ $tracking->cliente->rfc }}</p>
            <p><strong>Correo electrónico:</strong> {{ $tracking->cliente->email }}</p>

            <br>

            <h3>Detalles del pedido</h3>
            <p><strong>Folio:</strong> #{{ $tracking->folio }}</p>
            <p><strong>Vendedor:</strong> #{{ $tracking->folio }}</p>
            <p><strong>Departamento:</strong> #{{ $tracking->folio }}</p>
            <p><strong>Sucursal:</strong> #{{ $tracking->folio }}</p>
            <p><strong>Categoria:</strong> #{{ $tracking->folio }}</p>
            <p><strong>Condición de Pago:</strong> #{{ $tracking->folio }}</p>

            {{-- producto y extra --}}
            <table>
                <tr class="heading">
                    <td colspan="1" style="text-align: center;">Cant.</td>
                    <td colspan="3" style="text-align: center;">SKU. / Producto</td>
                    <td colspan="2" style="text-align: right;">Precio U.</td>
                    <td colspan="2" style="text-align: right;">Subtotal</td>
                </tr>

                @foreach ($tracking->detalles as $detalle)
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
                            {{$detalle->precio_unidad}}
                            {{ $tracking->currency->name }}
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{$detalle->subtotal}}
                            {{ $tracking->currency->name }}</td>
                    </tr>
                @endforeach

                {{-- extra --}}
                @foreach ($tracking->extras as $extra)
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
                            {{$extra->precio_unidad}}
                            {{ $tracking->currency->name }}
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{$extra->subtotal}}
                            {{ $tracking->currency->name }}</td>
                    </tr>
                @endforeach
            </table>

            <p><strong>Subtotal:</strong> ${{ $tracking->subtotal }}</p>
            <p><strong>IVA:</strong> ${{ $tracking->iva_monto }}</p>
            <p><strong>Tipo de Cambio:</strong> ${{ $tracking->tarifa_cambio }}</p>
            <p><strong>Descuento:</strong> #{{ $tracking->descuento ?? 0 }}</p>
            <p><strong>Total:</strong> ${{ $tracking->total }}</p>
            <p><strong>Anticipo:</strong> ${{ $tracking->anticipo_monto }}</p>

            <h5>Notas del Vendedor:</h5>
            <p>{{ $tracking->notas }}</p>

            <p>
                Se adjunta la cotización correspondiente con folio
                <strong>#{{ $tracking->folio }}</strong>
                para su revisión y evaluación.
            </p>

            <p>
                Agradecemos de antemano su atención y apoyo para dar seguimiento a esta solicitud.
            </p>
        </div>

        <div class="footer">
            <p>
                Este mensaje fue generado automáticamente por el sistema
                <span class="brand">Corporativo ETBSA</span>.
            </p>
            <p>Favor de no responder a este correo electrónico.</p>
        </div>
    </div>
</body>

</html>

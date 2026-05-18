<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Actualización de Asignación de Número de Serie a pedido</title>
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table td,
        table th {
            border: 1px solid #ccc;
            padding: 6px;
        }

        table .heading td {
            background-color: #e9eef5;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Actualización de Asignación de Número de Serie a pedido</h2>

        <p>
            Por medio del presente, se informa sobre la asignación de número de serie para el pedido con folio
            <strong> #{{ $tracking->folio }}</strong>.
        </p>

        <div class="data">
            <h3>Tractor asignado</h3>

            <p><strong>Modelo:</strong> {{ $tracking->asignacion->invItem->invModel->name }}</p>
            <p><strong>Código:</strong> {{ $tracking->asignacion->invItem->invModel->code }}</p>
            <p><strong>RD:</strong> {{ $tracking->asignacion->invItem->rd }}</p>
            <p><strong>#Serie:</strong> {{ $tracking->asignacion->invItem->s_n }}</p>
            <p><strong>#Eco:</strong> {{ $tracking->asignacion->invItem->e_n }}</p>
            <p><strong>En sucursal:</strong> {{ $tracking->asignacion->invItem->sucursal->nombre }}</p>
            <p><strong>Asignado por:</strong> {{ $tracking->asignacion->empleado->nombreCompleto }}</p>
            <p><strong>Notas:</strong> {{ $tracking->asignacion->comentarios ?? 'Sin notas' }}</p>


            <h3>Datos del Cliente</h3>

            <p><strong>ID:</strong> {{ $tracking->cliente->id }}</p>
            <p><strong>Nombre:</strong> {{ $tracking->cliente->nombre }}</p>
            <p><strong>RFC:</strong> {{ $tracking->cliente->rfc }}</p>
            <p><strong>Correo electrónico:</strong> {{ $tracking->cliente->email }}</p>

            <br>

            <h3>Detalles del pedido</h3>
            <p><strong>Folio:</strong> #{{ $tracking->folio }}</p>
            <p><strong>Vendedor:</strong> {{ $tracking->vendedor->nombreCompleto }}</p>
            <p><strong>Departamento:</strong> {{ $tracking->vendedor->departamento->nombre }}</p>
            <p><strong>Sucursal:</strong> {{ $tracking->vendedor->sucursal->nombre }}</p>
            <p><strong>Categoria:</strong> {{ $tracking->categoria->name }}</p>
            <p><strong>Condición de Pago:</strong> {{ $tracking->condicionPago->name }}</p>

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
                            {{ number_format($detalle->precio_unidad, 2) }}
                            {{ $tracking->currency->name }}
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{ number_format($detalle->subtotal, 2) }}
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
                            {{ number_format($extra->precio_unidad, 2) }}
                            {{ $tracking->currency->name }}
                        </td>
                        <td colspan="2" style="text-align: right;font-size: 0.8rem;">
                            {{ number_format($extra->subtotal, 2) }}
                            {{ $tracking->currency->name }}</td>
                    </tr>
                @endforeach
            </table>

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

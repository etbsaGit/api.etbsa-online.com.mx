<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de vacaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #4a4a4a;
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            padding: 20px;
        }

        .content h2 {
            font-size: 20px;
            margin-bottom: 10px;
            color: #4a4a4a;
        }

        .content p {
            margin: 5px 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 11px;
            line-height: 1;
        }

        .table th,
        .table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 2px 2px;
            line-height: 1;
        }

        .table th {
            background-color: #4a4a4a;
            color: white;
            padding: 2px 2px;
        }

        .footer {
            text-align: center;
            padding: 10px;
            background-color: #f4f4f4;
            font-size: 14px;
            color: #666666;
        }

        .btn {
            display: inline-block;
            padding: 10px 15px;
            background-color: #4a4a4a;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }

        .btn:hover {
            background-color: #333333;
        }

        .image {
            text-align: center;
            margin: 10px 0;
        }

        .image img {
            max-width: 100%;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>Solicitud de vacaciones</h1>
        </div>
        <div class="content">
            <h3>Estimado/a:</h3>
            <p>Le informamos que <strong>{{ $data['empleado']['nombreCompleto'] }}</strong> hizo una solicitud de
                vacaciones, favor de
                dar seguimiento a la solicitud</p>
        </div>

        <div class="content">
            <h2>Detalles de la solicitud de vacaciones</h2>
            <p><strong>ID Solicitud:</strong> {{ $data['id'] }}</p>
            <p><strong>Empleado:</strong> {{ $data['empleado']['nombreCompleto'] }}</p>
            <p><strong>Puesto:</strong> {{ $data['puesto']['nombre'] }}</p>
            <p><strong>Sucursal:</strong> {{ $data['sucursal']['nombre'] }}</p>
            <p><strong>Vehículo Utilitario:</strong> {{ $data['vehiculo_utilitario'] }}</p>
            <p><strong>Periodo Correspondiente:</strong> {{ $data['periodo_correspondiente'] }}</p>
            <p><strong>Años Cumplidos:</strong> {{ $data['anios_cumplidos'] }}</p>
            <p><strong>Días del Periodo:</strong> {{ $data['dias_periodo'] }}</p>
            <p><strong>Subtotal Días:</strong> {{ $data['subtotal_dias'] }}</p>
            <p><strong>Días a Disfrutar:</strong> {{ $data['dias_disfrute'] }}</p>
            <p><strong>Días Pendientes:</strong> {{ $data['dias_pendientes'] }}</p>
            <p><strong>Fecha de Inicio:</strong> {{ \Carbon\Carbon::parse($data['fecha_inicio'])->format('d/m/Y') }}
            </p>
            <p><strong>Fecha de Término:</strong> {{ \Carbon\Carbon::parse($data['fecha_termino'])->format('d/m/Y') }}
            </p>
            <p><strong>Fecha de Regreso:</strong> {{ \Carbon\Carbon::parse($data['fecha_regreso'])->format('d/m/Y') }}
            </p>
            <p><strong>Comentarios:</strong> {{ $data['comentarios'] }}</p>

            <h3>Detalles del Empleado</h3>
            <img src="{{ $data['empleado']['picture'] }}" alt="Foto de {{ $data['empleado']['nombreCompleto'] }}"
                style="max-width: 100px;">
            <p><strong>Nombre:</strong> {{ $data['empleado']['nombreCompleto'] }}</p>
            <p><strong>Correo Institucional:</strong> {{ $data['empleado']['correo_institucional'] }}</p>
            <p><strong>Fecha de Ingreso:</strong>
                {{ \Carbon\Carbon::parse($data['empleado']['fecha_de_ingreso'])->format('d/m/Y') }}</p>

            <h3>Quien cubre</h3>
            <p>{{ $data['cubre_rel']['nombreCompleto'] }}</p>

            <h3>Información Adicional</h3>
            <p><strong>Creado el:</strong> {{ \Carbon\Carbon::parse($data['created_at'])->format('d/m/Y') }}</p>
            <p><strong>Actualizado el:</strong> {{ \Carbon\Carbon::parse($data['updated_at'])->format('d/m/Y') }}</p>

            <h3>Vacaciones cercanas:</h3>

            {{-- Vacaciones pasadas --}}
            <h4>Vacaciones en el último mes:</h4>
            @if ($vacaciones_pasadas->isEmpty())
                <p>No hay vacaciones en el último mes.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Término</th>
                            <th>Días</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vacaciones_pasadas as $v)
                            <tr>
                                <td>{{ $v->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($v->fecha_inicio)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($v->fecha_termino)->format('d/m/Y') }}</td>
                                <td>{{ $v->dias_disfrute }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Vacaciones futuras --}}
            <h4>Vacaciones en el siguiente mes:</h4>
            @if ($vacaciones_futuras->isEmpty())
                <p>No hay vacaciones en el siguiente mes.</p>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Término</th>
                            <th>Días</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($vacaciones_futuras as $v)
                            <tr>
                                <td>{{ $v->id }}</td>
                                <td>{{ \Carbon\Carbon::parse($v->fecha_inicio)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($v->fecha_termino)->format('d/m/Y') }}</td>
                                <td>{{ $v->dias_disfrute }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        <div class="footer">
            <p>Este correo es generado automáticamente. Por favor, no respondas a este mensaje.</p>
        </div>
    </div>
</body>

</html>

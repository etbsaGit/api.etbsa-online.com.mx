<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Visita</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 100%;
            padding: 0px;
            background: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 3px solid #447c1f;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            color: #447c1f;
            text-align: center;
            flex-grow: 1;
        }
        .avatar {
            width: 100px;
            height: 60px;
            object-fit: cover;
            margin-left: 15px;
        }

        .avatar2 {
            width: 120px;
            height: 60px;
            object-fit: cover;
            margin-left: 75%;
        }

        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            padding-top: 5px;
            margin-top: 15px;
        }

        .prospect-box {
            border: 2px solid #447c1f;
            /* Verde */
            margin-bottom: 20px;
            background-color: #ecf9f1;
            /* Verde claro */
            border-radius: 8px;
            overflow: hidden;
        }

        .prospect-box h3 {
            font-size: 20px;
            color: #fff;
            margin: 0;
            padding: 15px;
            background-color: #447c1f;
            /* Verde */
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background: #fff;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #447c1f;
            /* Verde */
            color: white;
            font-weight: bold;
        }

        .no-data {
            color: #888;
            font-style: italic;
            text-align: center;
        }

        p {
            margin: 5px 0;
        }

        .text-muted {
            color: #808080;
            font-style: italic;
        }

        .prospect-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .prospect-item {
            display: flex;
            gap: 5px;
            align-items: center;
        }

        .prospect-label {
            font-weight: bold;
            color: #ecf0f1;
        }

        .prospect-value {
            color: #bdc3c7;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Información del Empleado -->
        <div class="header-container">
            <img src="storage/images/logo40.png" alt="Avatar" class="avatar">
            <img src="storage/images/logo.png" alt="Avatar" class="avatar2">
            <div class="header">Reporte de Visitas del mes de {{$month}} del {{$year}}</div>
        </div>
        <div class="section-title">Información del Empleado</div>
        <p><strong>Nombre:</strong> {{ $empleado->nombreCompleto }}</p>
        <p><strong>Sucursal:</strong> {{ optional($empleado->sucursal)->nombre ?? 'No disponible' }}</p>

        <!-- Cuadro por cada prospecto -->
        @foreach ($empleado->prospects as $prospect)
            <div class="prospect-box">
                <h3>
                    <div class="prospect-info">
                        <div class="prospect-item">
                            <span class="prospect-label">Prospecto:</span>
                            <span class="prospect-value">{{ $prospect->nombre }}</span>
                        </div>
                        <div class="prospect-item">
                            <span class="prospect-label">Teléfono:</span>
                            <span class="prospect-value">{{ $prospect->telefono }}</span>
                        </div>
                        <div class="prospect-item">
                            <span class="prospect-label">Vendedor:</span>
                            <span class="prospect-value">{{ $prospect->vendedor?->nombreCompleto }}</span>
                        </div>
                    </div>
                </h3>

                <!-- Visitas -->
                <div class="section-title">Visitas Realizadas</div>
                <table>

                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Visita</th>
                            <th>Comentarios</th>
                            <th>Retroalimentacion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(optional($prospect->visits)->toArray() ?? [] as $visit)
                            <tr>
                                <td>{{ $visit['dia'] ?? 'No disponible' }}</td>
                                <td>{{ $visit['ubicacion'] ?? 'No disponible' }}</td>
                                <td>{{ $visit['comentarios'] ?? 'Sin comentarios' }}</td>
                                <td>{{ $visit['retroalimentacion'] ?? 'Sin retroalimentacion' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="no-data">No hay visitas registradas para este prospecto.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Cultivos -->
                @if ($prospect->prospectCultivo->isNotEmpty())
                    <div class="section-title">Cultivos</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Cultivo</th>
                                <th>Tipo de Cultivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prospect->prospectCultivo as $cultivo)
                                <tr>
                                    <td>{{ $cultivo->cultivo->name ?? 'No disponible' }}</td>
                                    <td>{{ $cultivo->tipoCultivo->name ?? 'No disponible' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Este prospecto no tiene cultivos.</p>
                @endif

                <!-- Riego -->
                @if ($prospect->prospectRiego->isNotEmpty())
                    <div class="section-title">Riego</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Riego</th>
                                <th>Marca</th>
                                <th>h. propias</th>
                                <th>h. rentadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prospect->prospectRiego as $riego)
                                <tr>
                                    <td>{{ $riego->riego->name ?? 'No disponible' }}</td>
                                    <td>{{ $riego->marca ?? 'No disponible' }}</td>
                                    <td>{{ $riego->hectareas_propias ?? 'No disponible' }}</td>
                                    <td>{{ $riego->hectareas_rentadas ?? 'No disponible' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Este prospecto no tiene riego.</p>
                @endif

                <!-- distribucion -->
                @if ($prospect->prospectDistribucion->isNotEmpty())
                    <div class="section-title">Distribucion</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Ubicacion</th>
                                <th>h. propias</th>
                                <th>h. rentadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prospect->prospectDistribucion as $dis)
                                <tr>
                                    <td>{{ $dis->nombre ?? 'No disponible' }}</td>
                                    <td>{{ $dis->ubicacion ?? 'No disponible' }}</td>
                                    <td>{{ $dis->hectareas_propias ?? 'No disponible' }}</td>
                                    <td>{{ $dis->hectareas_rentadas ?? 'No disponible' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Este prospecto no tiene distribucion de tierras.</p>
                @endif

                <!-- Maquinaria  -->
                @if ($prospect->prospectMaquina->isNotEmpty())
                    <div class="section-title">Maquinas</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Modelo</th>
                                <th>Año</th>
                                <th>Marca</th>
                                <th>Condicion</th>
                                <th>Clasificacion de equipo</th>
                                <th>Tipo de equipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prospect->prospectMaquina as $maquina)
                                <tr>
                                    <td>{{ $maquina->modelo ?? 'No disponible' }}</td>
                                    <td>{{ $maquina->anio ?? 'No disponible' }}</td>
                                    <td>{{ $maquina->marca->name ?? 'No disponible' }}</td>
                                    <td>{{ $maquina->condicion->name ?? 'No disponible' }}</td>
                                    <td>{{ $maquina->clasEquipo->name ?? 'No disponible' }}</td>
                                    <td>{{ $maquina->tipoEquipo->name ?? 'No disponible' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Este prospecto no tiene maquinaria.</p>
                @endif

                <!-- distribucion -->
                @if ($prospect->prospectAgp->isNotEmpty())
                    <div class="section-title">Agricultura de precisión</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Marca</th>
                                <th>Equipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prospect->prospectAgp as $agp)
                                <tr>
                                    <td>{{ $agp->marca ?? 'No disponible' }}</td>
                                    <td>{{ $agp->equipo ?? 'No disponible' }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Este prospecto no tiene equipos de agricultura de precisión.</p>
                @endif

                <!-- Servicio -->
                @if ($prospect->prospectServicio->isNotEmpty())
                    <div class="section-title">Servicios posventa</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Distribuidor</th>
                                <th>Ubicacion</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($prospect->prospectServicio as $agp)
                                <tr>
                                    <td>{{ $agp->distribuidor ?? 'No disponible' }}</td>
                                    <td>{{ $agp->ubicacion ?? 'No disponible' }}</td>

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="text-muted">Este prospecto no tiene proveedores de servicios de posventa</p>
                @endif
            </div>
        @endforeach
    </div>

</body>

</html>

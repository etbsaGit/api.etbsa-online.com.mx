<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>
        @if(is_null($service->status))
            Nuevo Servicio Creado
        @else
            Servicio Actualizado
        @endif
    </title>
</head>
<body style="font-family: 'Arial', sans-serif; background-color: #f4f4f4; margin: 0; padding: 0;">

    <table align="center" width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; margin-top: 30px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); overflow: hidden;">
        <tr>
            <td style="background-color: #4CAF50; color: #ffffff; padding: 20px; text-align: center;">
                <h1 style="margin: 0; font-size: 24px;">
                    @if(is_null($service->status))
                        Nuevo Servicio Creado
                    @else
                        Servicio Actualizado
                    @endif
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 20px;">
                <h2 style="color: #333333; font-size: 20px; margin-top: 0;">Detalles del Servicio</h2>
                <table width="100%" cellpadding="5" cellspacing="0" style="border-collapse: collapse;">

                    <tr>
                        <td style="font-weight: bold;">Descripción:</td>
                        <td>{{ $service->description }}</td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;">Tipo:</td>
                        <td style="font-weight: bold;">
                            {{ $service->estatus->nombre ?? 'N/A' }}
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;">Estatus:</td>
                        <td style="font-weight: bold; color:
                            @if($service->status === 1) #28a745
                            @elseif($service->status === 0) #FF0000
                            @else #FFA500
                            @endif
                        ">
                            @if(is_null($service->status))
                                En espera de autorización
                            @elseif($service->status === 1)
                                Autorizado
                            @elseif($service->status === 0)
                                Rechazado
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;">Kilometraje:</td>
                        <td>{{ $service->km }}</td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;">Vehículo:</td>
                        <td>
                            {{ $service->vehicle->estatus->nombre ?? 'N/A' }} - {{ $service->vehicle->placas ?? '' }}
                        </td>
                    </tr>

                    <tr>
                        <td style="font-weight: bold;">Empleado:</td>
                        <td>
                            {{ $service->empleado->nombreCompleto ?? $service->empleado->nombre ?? 'N/A' }}<br>
                            <img src="{{ $service->empleado->picture ?? '' }}" alt="Foto Empleado" width="80" style="border-radius: 50%; margin-top: 5px;">
                        </td>
                    </tr>

                </table>

                <p style="margin-top: 20px; font-size: 14px; color: #666666;">
                    Este correo es generado automáticamente por el sistema. No responda a este mensaje.
                </p>
            </td>
        </tr>
        <tr>
            <td style="background-color: #f4f4f4; text-align: center; padding: 10px; font-size: 12px; color: #999999;">
                &copy; {{ date('Y') }} ETBSA. Todos los derechos reservados.
            </td>
        </tr>
    </table>

</body>
</html>

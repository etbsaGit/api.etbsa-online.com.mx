<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Solicitud de permiso</title>
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
        <h2>📋 Solicitud de permiso</h2>

        <p>El empleado <strong>{{ $empleado->nombreCompleto }}</strong> ha solicitado un permiso.</p>

        <div class="data">
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($permiso->date)->format('d/m/Y') }}</p>
            <p><strong>Inicio:</strong>
                {{ $permiso->start ? \Carbon\Carbon::parse($permiso->start)->format('H:i') : 'N/A' }}</p>
            <p><strong>Fin:</strong> {{ $permiso->end ? \Carbon\Carbon::parse($permiso->end)->format('H:i') : 'N/A' }}
            </p>
            {{-- <p><strong>Almuerzo:</strong>
                {{ $permiso->lunch_start ? \Carbon\Carbon::parse($permiso->lunch_start)->format('H:i') : 'N/A' }}
                -
                {{ $permiso->lunch_end ? \Carbon\Carbon::parse($permiso->lunch_end)->format('H:i') : 'N/A' }}
            </p> --}}
            <p><strong>Descripción:</strong> {{ $permiso->description }}</p>
            <p><strong>Sucursal:</strong> {{ $permiso->sucursal->nombre ?? 'No asignada' }}</p>
            <p><strong>Estado:</strong>
                @if ($permiso->status === 1)
                    Autorizado
                @elseif($permiso->status === 0)
                    Rechazado
                @else
                    Pendiente
                @endif
            </p>
            <p><strong>Autorizado por:</strong> {{ $user->empleado?->nombreCompleto ?? 'No asignada' }}</p>

        </div>


        <div class="footer">
            <p>Este mensaje fue enviado automáticamente por el sistema <span class="brand">Corporativo ETBSA</span>.</p>
            <p>Por favor, no responda a este correo.</p>
        </div>
    </div>
</body>

</html>

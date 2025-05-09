<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Reporte Diario</title>
  <style>
     body {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            color: #333;
            background-color: #ffffff;
            margin: 0px;
            padding: 0px;
        }

        .container {
            background: white;
            padding: 0px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 100, 42, 0.2);
        }
    .section-title {
      font-size: 16px;
      font-weight: bold;
      margin-top: 20px;
      border-bottom: 1px solid #000;
      padding-bottom: 5px;
    }
    .pago {
      margin-left: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 5px;
      text-align: left;
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
            margin-left: 65%;
        }
  </style>
</head>
<body>
    <div class="container">
    <div class="header-container">
        <img src="storage/images/logo40.png" alt="Avatar" class="avatar">
        <img src="storage/images/logo.png" alt="Avatar" class="avatar2">
        <div class="header">Reporte de ventas por categorías</div>
    </div>

  <p>Fecha del reporte: {{ \Carbon\Carbon::parse(request()->route('fecha'))->format('d/m/Y') }}</p>

  @foreach ($data as $categoria)
    <div class="section-title">
      {{ $categoria->nombre }} - Total: ${{ number_format($categoria->monto, 2) }}
    </div>

    @if (count($categoria->pagos))
      <div class="pago">
        <table>
          <thead>
            <tr>
              <th>Monto</th>
              <th>Descripción</th>
              <th>Sucursal</th>
              <th>Cliente</th>
              <th>Factura</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($categoria->pagos as $pago)
              <tr>
                <td>${{ number_format($pago->monto, 2) }}</td>
                <td>{{ $pago->descripcion }}</td>
                <td>{{ $pago->sucursal->nombre ?? '-' }}</td>
                <td>{{ $pago->transaccion->cliente->nombre ?? '-' }}</td>
                <td>{{ $pago->transaccion->factura ?? 'N/A' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif
  @endforeach
</div>
</body>
</html>

<?php

namespace Database\Seeders;

use Carbon\Carbon;

use App\Models\Caja\CajaPago;
use Illuminate\Database\Seeder;
use App\Models\Caja\CajaTransaccion;

class CajaTransaccionSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $numeroPagos = rand(1, 5); // Número aleatorio de pagos

            // Datos comunes de la transacción
            $transaccion = CajaTransaccion::create([
                'factura' => (string) $i,
                'folio' => (string) $i,
                'serie' => (string) $i,
                'uuid' => uniqid(),
                'comentarios' => null,
                'validado' => 0,
                'cliente_id' => rand(1, 100),
                'user_id' => 269,
                'tipo_factura_id' => rand(1, 2),
                'cuenta_id' => 2,
                'fecha_pago' => Carbon::now()->subDays(rand(1, 30))->toDateString(),
                'tipo_pago_id' => rand(1, 6),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Crear pagos asociados (sin cuenta_id, tipo_pago_id ni fecha_pago)
            for ($j = 0; $j < $numeroPagos; $j++) {
                $monto = rand(1000, 10000) / 100; // $10.00 a $100.00

                CajaPago::create([
                    'monto' => $monto,
                    'descripcion' => "compra $i",
                    'sucursal_id' => rand(1, 5),
                    'categoria_id' => rand(1, 8),
                    'transaccion_id' => $transaccion->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}

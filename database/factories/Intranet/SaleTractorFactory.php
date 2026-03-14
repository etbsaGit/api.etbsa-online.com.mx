<?php

namespace Database\Factories\Intranet;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Intranet\Cliente;
use App\Models\Empleado;
use App\Models\Estatus;
use App\Models\Intranet\InvModel;
use App\Models\Intranet\MetodoPago;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class SaleTractorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $vendedor = Empleado::inRandomOrder()->first();
        $cliente = Cliente::inRandomOrder()->first();
        return [
                'order' => fake()->unique()->numerify('ORD-#####'),
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedor->id,
                'sucursal_id' => $vendedor->sucursal_id,
                'inv_model_id' => InvModel::inRandomOrder()->first()->id,
                'fecha' => fake()->dateTimeBetween('-6 months'),
                'total' => fake()->randomFloat(2, 1000, 100000),
                'condicion_pago_id' => 64,
                'estatus_id' => Estatus::where('clave', 'pedido')->where('tipo_estatus', 'sale')->first()->id,
        ];
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intranet\Cliente;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Faker\Factory as Faker;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Cliente::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [];

        $faker = Faker::create();

        for ($i = 0; $i < 150; $i++) {
            $data[] = [
                'nombre' => $faker->name,
                'equip' => rand(10000, 99999),
                'tipo' => 'fisica',
                'rfc' => $this->randomString(13),
                'telefono' => rand(1000000000, 9999999999),
                'email' => $this->randomEmail(),
                'state_entity_id' => rand(1, 10), // Ajusta el rango según tus necesidades
                'town_id' => rand(1, 100), // Ajusta el rango según tus necesidades
                'colonia' => $this->randomString(6),
                'calle' => $this->randomString(4),
                'codigo_postal' => rand(10000, 99999), // Código postal aleatorio de 5 dígitos
                'classification_id' => rand(1, 4), // Ajusta el rango según tus necesidades
                'segmentation_id' => rand(1, 5), // Ajusta el rango según tus necesidades
                'technological_capability_id' => rand(1, 4), // Ajusta el rango según tus necesidades
                'tactic_id' => rand(1, 4), // Ajusta el rango según tus necesidades
                'construction_classification_id' => rand(1, 5), // Ajusta el rango según tus necesidades
            ];
        }

        DB::table('clientes')->insert($data);
    }

    private function randomString($length)
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    // Función para generar correos electrónicos aleatorios
    private function randomEmail()
    {
        $domains = ['example.com', 'test.com', 'foo.com', 'bar.com'];
        return $this->randomString(rand(5, 10)) . '@' . $domains[rand(0, count($domains) - 1)];
    }
}

<?php

namespace Database\Seeders;

use App\Models\Intranet\Marca;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MarcaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Marca::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            'Acura',
            'Bison',
            'BMW',
            'Bobcat',
            'Buick',
            'Cadillac',
            'Case',
            'Caterpillar',
            'Chevrolet',
            'Chrysler',
            'Cimarron',
            'Deutz Fahr',
            'Dina',
            'Dodge',
            'Famaq',
            'Ford',
            'Freightliner',
            'GM',
            'GMC',
            'Hitachi',
            'Honda',
            'Hyundai',
            'Infiniti',
            'International',
            'Isuzu',
            'Jacto',
            'JCB',
            'Jeep',
            'John Deere',
            'Kelly',
            'Kenworth',
            'KIA',
            'Kimball',
            'Komatsu',
            'Kubota',
            'Lovol',
            'Mahindra',
            'Massey Ferguson',
            'Mazda',
            'McCORMICK',
            'Mercedes-Benz',
            'New Holland',
            'NISSAN',
            'Peugeot',
            'Renault',
            'Samsung',
            'Sany',
            'Scania',
            'Sembradoras del Bajio',
            'Shantui',
            'Sonalika',
            'Subaru',
            'Suzuki',
            'Swissmex',
            'Terramak',
            'Tesla',
            'Toyota',
            'Volkswagen',
            'Volvo',
            'Volvo Buses',
            'VW Camiones y Autobuses',
        ];

        foreach ($data as $item) {
            Marca::create(['name' => $item]);
        }
    }
}

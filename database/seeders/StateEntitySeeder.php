<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Intranet\StateEntity;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class StateEntitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        StateEntity::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $data = [
            ['id' => '01', 'name' => 'AGUASCALIENTES', 'abbreviation' => 'AS'],
            ['id' => '02', 'name' => 'BAJA CALIFORNIA', 'abbreviation' => 'BC'],
            ['id' => '03', 'name' => 'BAJA CALIFORNIA SUR', 'abbreviation' => 'BS'],
            ['id' => '04', 'name' => 'CAMPECHE', 'abbreviation' => 'CC'],
            ['id' => '05', 'name' => 'COAHUILA DE ZARAGOZA', 'abbreviation' => 'CL'],
            ['id' => '06', 'name' => 'COLIMA', 'abbreviation' => 'CM'],
            ['id' => '07', 'name' => 'CHIAPAS', 'abbreviation' => 'CS'],
            ['id' => '08', 'name' => 'CHIHUAHUA', 'abbreviation' => 'CH'],
            ['id' => '09', 'name' => 'CIUDAD DE MÉXICO', 'abbreviation' => 'DF'],
            ['id' => '10', 'name' => 'DURANGO', 'abbreviation' => 'DG'],
            ['id' => '11', 'name' => 'GUANAJUATO', 'abbreviation' => 'GT'],
            ['id' => '12', 'name' => 'GUERRERO', 'abbreviation' => 'GR'],
            ['id' => '13', 'name' => 'HIDALGO', 'abbreviation' => 'HG'],
            ['id' => '14', 'name' => 'JALISCO', 'abbreviation' => 'JC'],
            ['id' => '15', 'name' => 'MÉXICO', 'abbreviation' => 'MC'],
            ['id' => '16', 'name' => 'MICHOACÁN DE OCAMPO', 'abbreviation' => 'MN'],
            ['id' => '17', 'name' => 'MORELOS', 'abbreviation' => 'MS'],
            ['id' => '18', 'name' => 'NAYARIT', 'abbreviation' => 'NT'],
            ['id' => '19', 'name' => 'NUEVO LEÓN', 'abbreviation' => 'NL'],
            ['id' => '20', 'name' => 'OAXACA', 'abbreviation' => 'OC'],
            ['id' => '21', 'name' => 'PUEBLA', 'abbreviation' => 'PL'],
            ['id' => '22', 'name' => 'QUERÉTARO', 'abbreviation' => 'QT'],
            ['id' => '23', 'name' => 'QUINTANA ROO', 'abbreviation' => 'QR'],
            ['id' => '24', 'name' => 'SAN LUIS POTOSÍ', 'abbreviation' => 'SP'],
            ['id' => '25', 'name' => 'SINALOA', 'abbreviation' => 'SL'],
            ['id' => '26', 'name' => 'SONORA', 'abbreviation' => 'SR'],
            ['id' => '27', 'name' => 'TABASCO', 'abbreviation' => 'TC'],
            ['id' => '28', 'name' => 'TAMAULIPAS', 'abbreviation' => 'TS'],
            ['id' => '29', 'name' => 'TLAXCALA', 'abbreviation' => 'TL'],
            ['id' => '30', 'name' => 'VERACRUZ DE IGNACIO DE LA LLAVE', 'abbreviation' => 'VZ'],
            ['id' => '31', 'name' => 'YUCATÁN', 'abbreviation' => 'YN'],
            ['id' => '32', 'name' => 'ZACATECAS', 'abbreviation' => 'ZS'],
        ];

        foreach ($data as $item) {
            StateEntity::create([
                'name' => $item['name'],
                'abbreviation' => $item['abbreviation']
            ]);
        }

    }
}

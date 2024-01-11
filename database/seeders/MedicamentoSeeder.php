<?php

namespace Database\Seeders;

use App\Models\Medicamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Medicamento::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $medicamentos = [
            "Cetirizina",
            "Loratadina",
            "Difenhidramina",
            "Fexofenadina",
            "Desloratadina",
            "Levocetirizina",
            "Clorfenamina",
            "Cromoglicato de sodio",
            "Montelukast",
            "Fluticasona nasal",
            "Amlodipino",
            "Metformina",
            "Atorvastatina",
            "Losartán",
            "Omeprazol",
            "Salbutamol",
            "Warfarina",
            "Levothyroxine",
            "Insulina",
            "Enalapril"
        ];

        foreach ($medicamentos as $medicamento) {
            Medicamento::create(['nombre' => $medicamento]);
        }
    }
}

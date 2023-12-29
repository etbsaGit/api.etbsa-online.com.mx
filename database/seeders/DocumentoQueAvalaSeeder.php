<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentoQueAvala;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DocumentoQueAvalaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DocumentoQueAvala::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $documentosQueAvalan = [
            'Certificado',
            'Titulo',
            'Carta',
            'Comprobante'
        ];

        foreach ($documentosQueAvalan as $documentoQueAvala) {
            DocumentoQueAvala::create(['nombre' => $documentoQueAvala]);
        }
    }
}

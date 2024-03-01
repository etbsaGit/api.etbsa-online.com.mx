<?php

namespace Database\Seeders;

use App\Models\Requisito;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RequisitoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Requisito::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $requisitos = [
            'CV y/o solicitud de empledo',
            'Acta de nacimiento',
            'Credencial de elector',
            'CURP',
            'Constancia de situacion fiscal',
            'IMSS',
            'Comprobante de domicilio',
            'Croquis del domicilio',
            'Certificado de estudios',
            'Carta de antecedentes no penales',
            'Aviso de retencion credito infonavit',
            'Acta de matrimonio',
            'Acta de nacimiento de los hijos',
            'Certificado medico',
            'Licencia de manejo',
            'Carta de recomendacion',
            'Apertura de cuenta bancaria BBVA',
            'Reporte de semanas cotizadas',
            'Constancia de percepciones y retenciones de empledo anterior',
            'Documento interno alta',
            'Alta IMSS',
            'Baja IMSS'
        ];

        foreach ($requisitos as $requisito) {
            Requisito::create([
                'nombre' => $requisito,
                'descripcion' => 'Descripcion de ' . $requisito
            ]);
        }
    }
}

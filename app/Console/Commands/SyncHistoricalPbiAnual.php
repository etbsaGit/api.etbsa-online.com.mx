<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncHistoricalPbiAnual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'historico:sync-anual {--file= : Ruta opcional a un archivo CSV local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza la base de datos de histórico anual por departamento y cliente desde Google Sheets';

    const GOOGLE_SHEET_URL = 'https://docs.google.com/spreadsheets/d/1qsqJ3MisSGdWob3xxHnaHEoORzARGNrxbG2Af48ohyw/export?format=csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronización de histórico anual de ventas...');
        $filePath = $this->option('file');

        if ($filePath && file_exists($filePath)) {
            $this->info("Leyendo archivo local: $filePath");
            $handle = fopen($filePath, 'r');
        } else {
            $this->info('Descargando CSV desde Google Sheets...');
            $response = Http::timeout(180)->withHeaders([
                'User-Agent' => 'Mozilla/5.0'
            ])->get(self::GOOGLE_SHEET_URL);

            if (!$response->successful()) {
                $this->error('Error al descargar la hoja de cálculo. Código HTTP: ' . $response->status());
                return 1;
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'pbi_anual_');
            file_put_contents($tempFile, $response->body());
            $handle = fopen($tempFile, 'r');
        }

        if (!$handle) {
            $this->error('No se pudo abrir el archivo CSV.');
            return 1;
        }

        // Leer encabezado
        $header = fgetcsv($handle);
        if (!$header) {
            $this->error('El archivo CSV está vacío.');
            fclose($handle);
            return 1;
        }

        // Mapeo de columnas normalizadas
        $colIndex = [];
        foreach ($header as $i => $name) {
            $raw = trim($name);
            $normalized = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $name)));
            if (str_contains($raw, '%')) {
                $colIndex['PCT' . $normalized] = $i;
            } else {
                $colIndex[$normalized] = $i;
            }
        }

        $this->info('Limpiando tabla anterior...');
        DB::table('historical_pbi_anual')->truncate();

        $batch = [];
        $batchSize = 1500;
        $totalInserted = 0;
        $now = now();

        $this->info('Insertando registros en lotes...');

        while (($row = fgetcsv($handle)) !== false) {
            if (empty(array_filter($row))) {
                continue;
            }

            $getVal = function ($key) use ($row, $colIndex) {
                $k = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $key));
                if (isset($colIndex[$k]) && isset($row[$colIndex[$k]])) {
                    $val = trim($row[$colIndex[$k]]);
                    return mb_convert_encoding($val, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                }
                return null;
            };

            $parseNumber = function ($key) use ($getVal) {
                $val = $getVal($key);
                if (!$val) return 0.0;
                $clean = preg_replace('/[^\d.-]/', '', (string)$val);
                if (!is_numeric($clean)) return 0.0;
                $num = (float)$clean;
                if ($num > 9999999999999 || $num < -9999999999999 || is_nan($num) || is_infinite($num)) {
                    return 0.0;
                }
                return $num;
            };

            $anioRaw = $getVal('AO') ?? $getVal('ANIO') ?? $getVal('AÑO');
            $anio = is_numeric($anioRaw) ? (int)$anioRaw : null;

            $batch[] = [
                'clave_cliente' => $getVal('CLAVE CLIENTE'),
                'rfc' => $getVal('RFC COMPAIA') ?? $getVal('RFC COMPAÑIA'),
                'nombre_cliente' => $getVal('NOMBRE COMPLETO CLIENTE'),
                'nom_clie_equip' => $getVal('Nom Clie EQUIP'),
                'anio' => $anio,
                'id_sucursal' => $getVal('ID_SUCURSAL'),
                'sucursal' => $getVal('SUCURSAL'),
                'nip' => $getVal('NIP'),

                'venta_maquinaria' => $parseNumber('VENTA MAQUINARIA'),
                'venta_refacciones' => $parseNumber('VENTA REFACCIONES'),
                'venta_servicio' => $parseNumber('VENTA SERVICIO'),
                'venta_riego' => $parseNumber('VENTA RIEGO'),
                'venta_chevron' => $parseNumber('VENTA CHEVRON'),
                'venta_nuevas_tecnologias' => $parseNumber('VENTA NUEVAS TECNOLOGIAS'),
                'total_venta' => $parseNumber('TOTAL VENTA'),

                'costo_maquinaria' => $parseNumber('COSTO MAQUINARIA'),
                'costo_refacciones' => $parseNumber('COSTO REFACCIONES'),
                'costo_servicio' => $parseNumber('COSTO SERVICIO'),
                'costo_riego' => $parseNumber('COSTO RIEGO'),
                'costo_chevron' => $parseNumber('COSTO CHEVRON'),
                'costo_nuevas_tecnologias' => $parseNumber('COSTO NUEVAS TECNOLOGIAS'),
                'total_costo' => $parseNumber('TOTAL COSTO'),

                'margen_maquinaria' => $parseNumber('MARGEN MAQUINARIA'),
                'margen_refacciones' => $parseNumber('MARGEN REFACCIONES'),
                'margen_servicio' => $parseNumber('MARGEN SERVICIO'),
                'margen_riego' => $parseNumber('MARGEN RIEGO'),
                'margen_chevron' => $parseNumber('MARGEN CHEVRON'),
                'margen_nuevas_tecnologias' => $parseNumber('MARGEN NUEVAS TECNOLOGIAS'),
                'margen_total' => $parseNumber('MARGEN TOTAL'),

                'pct_margen_maquinaria' => $parseNumber('PCT MARGEN MAQUINARIA'),
                'pct_margen_refacciones' => $parseNumber('PCT MARGEN REFACCIONES'),
                'pct_margen_servicio' => $parseNumber('PCT MARGEN SERVICIO'),
                'pct_margen_riego' => $parseNumber('PCT MARGEN RIEGO'),
                'pct_margen_chevron' => $parseNumber('PCT MARGEN CHEVRON'),
                'pct_margen_nuevas_tecnologias' => $parseNumber('PCT MARGEN NUEVAS TECNOLOGIAS'),
                'pct_margen_total' => $parseNumber('PCT MARGEN TOTAL'),

                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('historical_pbi_anual')->insert($batch);
                $totalInserted += count($batch);
                $batch = [];
                $this->line("Insertadas $totalInserted filas...");
            }
        }

        if (count($batch) > 0) {
            DB::table('historical_pbi_anual')->insert($batch);
            $totalInserted += count($batch);
        }

        fclose($handle);
        if (isset($tempFile) && file_exists($tempFile)) {
            unlink($tempFile);
        }

        Cache::forever('historical_pbi_anual_last_sync', now()->toDateTimeString());

        $this->info("¡Sincronización anual completada con éxito! Total de registros: $totalInserted");
        return 0;
    }
}

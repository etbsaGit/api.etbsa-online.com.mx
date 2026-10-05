<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SyncHistoricalPbiVentas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'historico:sync-sheet {--file= : Ruta opcional a un archivo CSV local}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Descarga y sincroniza la base de datos de histórico Power BI desde Google Sheets';

    const GOOGLE_SHEET_URL = 'https://docs.google.com/spreadsheets/d/1wFeJNJhSfNUUPBadkWEJOcRKiZzEJQQjEkPYKrUZdgk/export?format=csv';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando sincronización de histórico de ventas...');
        $filePath = $this->option('file');

        if ($filePath && file_exists($filePath)) {
            $this->info("Leyendo archivo local: $filePath");
            $handle = fopen($filePath, 'r');
        } else {
            $this->info('Descargando CSV desde Google Sheets...');
            $response = Http::timeout(120)->withHeaders([
                'User-Agent' => 'Mozilla/5.0'
            ])->get(self::GOOGLE_SHEET_URL);

            if (!$response->successful()) {
                $this->error('Error al descargar la hoja de cálculo. Código HTTP: ' . $response->status());
                return 1;
            }

            $tempFile = tempnam(sys_get_temp_dir(), 'pbi_sync_');
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
                $colIndex['MARGEN_PCT'] = $i;
                $colIndex['PCTMARGEN'] = $i;
            } else {
                $colIndex[$normalized] = $i;
            }
        }

        $this->info('Limpiando tabla anterior...');
        DB::table('historical_pbi_ventas')->truncate();

        $batch = [];
        $batchSize = 1000;
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
                    // Asegurar codificación utf-8 limpia
                    return mb_convert_encoding($val, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                }
                return null;
            };

            $parseNumber = function ($key) use ($getVal) {
                $val = $getVal($key);
                if (!$val) return 0.0;
                $clean = preg_replace('/[^\d.-]/', '', (string)$val);
                return is_numeric($clean) ? (float)$clean : 0.0;
            };

            $mesNoRaw = $getVal('Mes N') ?? $getVal('MESNO');
            $mesNo = is_numeric($mesNoRaw) ? (int)$mesNoRaw : null;
            $anioRaw = $getVal('AO') ?? $getVal('ANIO') ?? $getVal('AÑO');
            $anio = is_numeric($anioRaw) ? (int)$anioRaw : null;

            $margenPct = $parseNumber('MARGEN_PCT');
            if ($margenPct > 999999999 || $margenPct < -999999999 || is_nan($margenPct) || is_infinite($margenPct)) {
                $margenPct = 0.0;
            }

            $batch[] = [
                'fecha_factura' => $getVal('FECHA FACTURA'),
                'id_sucursal' => $getVal('ID_SUCURSAL'),
                'sucursal' => $getVal('SUCURSAL'),
                'tipo_venta' => $getVal('TIPO DE VENTA'),
                'departamento' => $getVal('DEPARTAMENTO'),
                'clasificacion' => $getVal('CLASIFICACION') ?? $getVal('CLASIFICACIN'),
                'descripcion_corta' => $getVal('DESCRIPCION CORTA'),
                'categoria' => $getVal('CATEGORIA'),
                'depto_vendedor' => $getVal('DEPTO VENDEDOR'),
                'nip' => $getVal('NIP'),
                'modelo' => $getVal('MODELO'),
                'descripcion_producto' => $getVal('DESCRIPCION PRODUCTO'),
                'marca' => $getVal('MARCA'),
                'clave_vendedor' => $getVal('CLAVE VENDEDOR'),
                'clave_cliente' => $getVal('CLAVE CLIENTE'),
                'nombre_vendedor' => $getVal('NOMBRE VENDEDOR'),
                'nombre_cliente' => $getVal('NOMBRE CLIENTE'),
                'estado' => $getVal('ESTADO'),
                'ciudad' => $getVal('CIUDAD'),
                'cp' => $getVal('C.P.') ?? $getVal('CP'),
                'email' => $getVal('EMAIL'),
                'estatus_vendedor' => $getVal('ESTATUS VENDEDOR'),
                'precio_venta' => $parseNumber('PRECIO VENTA'),
                'total_costo' => $parseNumber('TOTAL COSTO'),
                'margen' => $parseNumber('MARGEN'),
                'margen_pct' => $margenPct,
                'total' => $parseNumber('TOTAL'),
                'mes_no' => $mesNo,
                'mes' => $getVal('MES'),
                'anio' => $anio,
                'ciclo_anio' => $getVal('CICLO AO') ?? $getVal('CICLO ANIO'),
                'ultima_compra' => $getVal('ULTIMA COMPRA'),
                'zona_sucursal' => $getVal('ZONA SUCURSAL'),
                'semestre' => $getVal('SEMESTRE'),
                'trimestre' => $getVal('TRIMESTRE'),
                'cat_seminuevos' => $getVal('CAT SEMINUEVOS'),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('historical_pbi_ventas')->insert($batch);
                $totalInserted += count($batch);
                $batch = [];
                $this->line("Insertadas $totalInserted filas...");
            }
        }

        if (count($batch) > 0) {
            DB::table('historical_pbi_ventas')->insert($batch);
            $totalInserted += count($batch);
        }

        fclose($handle);
        if (isset($tempFile) && file_exists($tempFile)) {
            unlink($tempFile);
        }

        Cache::forever('historical_pbi_last_sync', now()->toDateTimeString());

        $this->info("¡Sincronización completada con éxito! Total de registros: $totalInserted");
        return 0;
    }
}

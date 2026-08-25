<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Intranet\ProductCategory;
use App\Models\Intranet\ProductSubCategory;
use App\Models\Intranet\ProductBrand;
use App\Models\Intranet\ProductSupplier;
use App\Models\Intranet\Currency;
use App\Models\Intranet\NivelPartner;
use App\Models\Intranet\ProductsRiego;
use App\Models\Intranet\ProductRiegoImage;
use App\Models\Intranet\PrecioProdRiego;
use Illuminate\Support\Facades\DB;

class ProductRiegoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Obtener la categoría RIEGO existente
        $categoria = ProductCategory::where('name', 'RIEGO')->first();
        if (!$categoria) {
            $this->command->error("La categoría 'RIEGO' no existe en la base de datos.");
            return;
        }

        // 2. Obtener la marca RIVULIS existente
        $marca = ProductBrand::where('name', 'like', '%RIVULIS%')->first();
        if (!$marca) {
            $this->command->error("La marca 'RIVULIS' no existe en la base de datos.");
            return;
        }

        // 3. Obtener el proveedor RIVULIS existente
        $proveedor = ProductSupplier::where('name', 'like', '%RIVULIS%')->first();
        if (!$proveedor) {
            $this->command->error("El proveedor 'RIVULIS' no existe en la base de datos.");
            return;
        }

        // 4. Obtener la moneda MXN
        $moneda = Currency::where('name', 'MXN')->first() ?? Currency::first();
        if (!$moneda) {
            $this->command->error("La moneda 'MXN' no existe en la base de datos.");
            return;
        }

        // 5. Obtener las subcategorías existentes: CONECTORES, CINTILLA, ACOLCHADO
        $subcategorias = ProductSubCategory::whereIn(DB::raw('UPPER(name)'), ['CONECTORES', 'CINTILLA', 'ACOLCHADO'])
            ->get()
            ->keyBy(function ($item) {
                return strtoupper(trim($item->name));
            });

        if ($subcategorias->isEmpty()) {
            $this->command->error("No se encontraron las subcategorías 'CONECTORES', 'CINTILLA' o 'ACOLCHADO'.");
            return;
        }

        // 6. Obtener niveles de partner para generar precios
        $nivelesPartner = NivelPartner::all();

        // 7. Definición de los 30 productos con nombres, SKUs, descripciones y precios base
        $productosData = [
            // ==========================================
            // SUB-CATEGORÍA: CINTILLA (10 Productos)
            // ==========================================
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-TT-58-6M-20-10',
                'name' => 'Cintilla de Riego T-Tape 5/8" 6 Mil 20 cm 1.0 LPH',
                'description' => 'Cinta de goteo de alta precisión Rivulis T-Tape con laberinto de flujo turbulento para hortalizas y cultivos en hilera.',
                'base_price' => 2450.00,
                'images' => [
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-TT-58-8M-30-075',
                'name' => 'Cintilla de Riego T-Tape 5/8" 8 Mil 30 cm 0.75 LPH',
                'description' => 'Cinta de goteo de pared media con excelente resistencia mecánica y uniformidad de descarga en terrenos planos.',
                'base_price' => 2680.00,
                'images' => [
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-TT-78-8M-20-10',
                'name' => 'Cintilla de Riego T-Tape 7/8" 8 Mil 20 cm 1.0 LPH',
                'description' => 'Cinta de mayor diámetro para tiradas largas de riego con mínima pérdida de carga y alta durabilidad.',
                'base_price' => 3150.00,
                'images' => [
                    'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-RD-58-6M-10-05',
                'name' => 'Cintilla de Riego Ro-Drip 5/8" 6 Mil 10 cm 0.5 LPH',
                'description' => 'Cinta de goteo con laberinto patentado vortex antiobstrucciones, ideal para cultivos intensivos y suelos arenosos.',
                'base_price' => 2520.00,
                'images' => [
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-RD-58-8M-20-10',
                'name' => 'Cintilla de Riego Ro-Drip 5/8" 8 Mil 20 cm 1.0 LPH',
                'description' => 'Emisores moldeados de precisión continua para una distribución uniforme del agua y fertilizantes en hortalizas.',
                'base_price' => 2750.00,
                'images' => [
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-RD-58-10M-30-15',
                'name' => 'Cintilla de Riego Ro-Drip 5/8" 10 Mil 30 cm 1.5 LPH',
                'description' => 'Cinta de pared gruesa para múltiples temporadas o terrenos pedregosos de alta exigencia.',
                'base_price' => 2980.00,
                'images' => [
                    'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-D1K-58-6M-20-10',
                'name' => 'Cintilla de Riego D1000 5/8" 6 Mil 20 cm 1.0 LPH',
                'description' => 'Cinta de goteo con gotero plano insertado de bajo perfil con ranura de salida autoprotegida.',
                'base_price' => 2390.00,
                'images' => [
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-D1K-58-8M-30-10',
                'name' => 'Cintilla de Riego D1000 5/8" 8 Mil 30 cm 1.0 LPH',
                'description' => 'Gotero plano integrado con filtro de entrada amplio para resistir aguas con alta carga de sedimentos.',
                'base_price' => 2620.00,
                'images' => [
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-D5K-58-15M-30-15PC',
                'name' => 'Cintilla de Riego D5000 PC 5/8" 15 Mil 30 cm 1.5 LPH (Autocompensada)',
                'description' => 'Manguera de goteo de pared media autocompensante para pendientes pronunciadas y longitudes extensas.',
                'base_price' => 3890.00,
                'images' => [
                    'https://images.unsplash.com/photo-1589923188900-85dae523342b?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CINTILLA',
                'sku' => 'RIV-HYD-58-8M-20-12',
                'name' => 'Cintilla de Riego Hydrogol 5/8" 8 Mil 20 cm 1.2 LPH',
                'description' => 'Cinta de laberinto continuo y apertura slit para proteger la entrada de partículas externas al despresurizar.',
                'base_price' => 2580.00,
                'images' => [
                    'https://images.unsplash.com/photo-1592417817098-8f3d6910a451?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1563514227147-6d2ff665a6a0?auto=format&fit=crop&w=800&q=80',
                ]
            ],

            // ==========================================
            // SUB-CATEGORÍA: CONECTORES (10 Productos)
            // ==========================================
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-CON-IN-LF-58',
                'name' => 'Conector Inicial Cinta a Layflat con Anillo 5/8"',
                'description' => 'Conector de inserción rápida para manguera plana Layflat a cintilla de 5/8" con tuerca de bloqueo hermético.',
                'base_price' => 6.50,
                'images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-CON-IN-PVC-58',
                'name' => 'Conector Inicial Cinta a PVC con Goma Grommet 5/8"',
                'description' => 'Adaptador inicial con rosca y goma de sello para toma de agua directa desde tubería rígida de PVC.',
                'base_price' => 8.20,
                'images' => [
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-COP-CC-58',
                'name' => 'Cople Unión Cinta a Cinta 5/8" con Seguro',
                'description' => 'Cople de reparación rápida para unión de dos tramos de cintilla de 5/8" con anillo de compresión seguro.',
                'base_price' => 5.80,
                'images' => [
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-VAL-LF-58',
                'name' => 'Válvula de Paso Cinta a Layflat 5/8"',
                'description' => 'Mini válvula de bola para control manual e individual de surco desde manguera Layflat.',
                'base_price' => 18.50,
                'images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-VAL-PVC-58',
                'name' => 'Válvula de Paso Cinta a PVC 5/8" con Goma',
                'description' => 'Mini válvula de regulación por sector de hilera con conexión roscada/grommet a tubería de PVC.',
                'base_price' => 19.80,
                'images' => [
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-VAL-CC-58',
                'name' => 'Válvula de Paso Cinta a Cinta 5/8"',
                'description' => 'Mini válvula intermedia para seccionar o reparar tramos de cintilla de riego por goteo.',
                'base_price' => 16.90,
                'images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-TER-OCH-58',
                'name' => 'Terminal Final de Cinta Tipo Anillo (Ocho) 5/8"',
                'description' => 'Terminal económico tipo ocho para cierre y purga de final de línea en cintilla de 5/8".',
                'base_price' => 2.20,
                'images' => [
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-TER-TAP-58',
                'name' => 'Terminal Final de Cinta con Tapón Roscado 5/8"',
                'description' => 'Accesorio de cierre terminal con tapa roscada para fácil lavado y desagüe de fin de línea.',
                'base_price' => 7.90,
                'images' => [
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-ADP-RM-34-58',
                'name' => 'Adaptador Cinta 5/8" a Rosca Macho 3/4"',
                'description' => 'Transición de rosca NPT macho de 3/4" a cintilla de goteo de 5/8" con anillo sellador de alta presión.',
                'base_price' => 9.50,
                'images' => [
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'CONECTORES',
                'sku' => 'RIV-TEE-CC-58',
                'name' => 'Tee Derivación Cinta a Cinta 5/8"',
                'description' => 'Conector en T para derivación y bifurcación de líneas de cintilla en huertos y parcelas agrícolas.',
                'base_price' => 11.20,
                'images' => [
                    'https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                ]
            ],

            // ==========================================
            // SUB-CATEGORÍA: ACOLCHADO (10 Productos)
            // ==========================================
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-NN-120-100',
                'name' => 'Plástico Acolchado Negro/Negro 1.20 m x 1000 m Cal. 100',
                'description' => 'Película de acolchado opaco para control total de malezas, conservación de humedad y calentamiento de suelo.',
                'base_price' => 1850.00,
                'images' => [
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-NP-120-100',
                'name' => 'Plástico Acolchado Negro/Plata 1.20 m x 1000 m Cal. 100',
                'description' => 'Acolchado reflejante plata/negro que repele plagas (pulgones/trips), mantiene fresca la raíz y maximiza fotosíntesis.',
                'base_price' => 2150.00,
                'images' => [
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-NB-120-120',
                'name' => 'Plástico Acolchado Negro/Blanco 1.20 m x 1000 m Cal. 120',
                'description' => 'Acolchado de alta reflexión térmica ideal para zonas cálidas y cultivo de frutillas o chiles.',
                'base_price' => 2380.00,
                'images' => [
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-NP-140-120',
                'name' => 'Plástico Acolchado Negro/Plata 1.40 m x 1000 m Cal. 120',
                'description' => 'Película ancha con protección UV para camas de siembra anchas en hortalizas a campo abierto.',
                'base_price' => 2590.00,
                'images' => [
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-TR-120-90',
                'name' => 'Plástico Acolchado Transparente Térmico 1.20 m x 1000 m Cal. 90',
                'description' => 'Acolchado para solarización y rápido calentamiento de suelo en siembras tempranas de melón y sandía.',
                'base_price' => 1720.00,
                'images' => [
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-PER-NN-30',
                'name' => 'Plástico Acolchado Negro/Negro Perforado a 30 cm 1.20 m x 1000 m',
                'description' => 'Película preperforada a tresbolillo de fábrica para ahorro de mano de obra y precisión de marco de plantación.',
                'base_price' => 2050.00,
                'images' => [
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-PER-NP-25',
                'name' => 'Plástico Acolchado Negro/Plata Perforado a 25 cm 1.20 m x 1000 m',
                'description' => 'Acolchado bicolor con orificios calibrados de 2.5" cada 25 cm para trasplante directo de cebolla y ajo.',
                'base_price' => 2350.00,
                'images' => [
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-BIO-NN-120',
                'name' => 'Plástico Acolchado Biodegradable Negro 1.20 m x 800 m',
                'description' => 'Película de biopolímero compostable que se degrada en suelo al finalizar el ciclo sin generar residuos plásticos.',
                'base_price' => 3200.00,
                'images' => [
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-FS-CF-120',
                'name' => 'Plástico Acolchado Fotoselectivo Café/Negro 1.20 m x 1000 m',
                'description' => 'Acolchado que transmite radiación infrarroja para calentar suelo mientras bloquea luz visible evitando malezas.',
                'base_price' => 2290.00,
                'images' => [
                    'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                ]
            ],
            [
                'subcategoria' => 'ACOLCHADO',
                'sku' => 'RIV-ACO-NP-150-150',
                'name' => 'Plástico Acolchado Negro/Plata Extra Duración 1.50 m x 1000 m Cal. 150',
                'description' => 'Película reforzada con triple coextrusión para cultivos de ciclo largo como papaya, piña y espárrago.',
                'base_price' => 3100.00,
                'images' => [
                    'https://images.unsplash.com/photo-1625246333195-78d9c38ad449?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1574943320219-553eb213f72d?auto=format&fit=crop&w=800&q=80',
                ]
            ],
        ];

        DB::beginTransaction();
        try {
            $count = 0;

            foreach ($productosData as $data) {
                $subcatKey = strtoupper($data['subcategoria']);
                $subcat = $subcategorias->get($subcatKey);

                if (!$subcat) {
                    $this->command->warn("Subcategoría {$data['subcategoria']} no encontrada, omitiendo producto {$data['name']}");
                    continue;
                }

                // 1. Crear o actualizar el producto
                $producto = ProductsRiego::updateOrCreate(
                    ['sku' => $data['sku']],
                    [
                        'brand_id' => $marca->id,
                        'vendor_id' => $proveedor->id,
                        'category_id' => $categoria->id,
                        'subcategory_id' => $subcat->id,
                        'currency_id' => $moneda->id,
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'active' => 1,
                        'image_url' => $data['images'][0] ?? null,
                    ]
                );

                // 2. Asociar imágenes (2 o 3 por producto)
                $producto->imagenes()->delete();
                foreach ($data['images'] as $imgUrl) {
                    ProductRiegoImage::create([
                        'product_id' => $producto->id,
                        'image_url' => $imgUrl,
                    ]);
                }

                // 3. Asociar precios por nivel de partner
                $producto->precios()->delete();
                $basePrice = $data['base_price'];

                foreach ($nivelesPartner as $index => $nivel) {
                    // Descuentos escalonados según el nivel (0% para Bronce hasta ~20% para Diamante)
                    $descuentoFactor = 1.0 - ($index * 0.04);
                    $precioCalculado = round($basePrice * $descuentoFactor, 2);

                    PrecioProdRiego::create([
                        'producto_id' => $producto->id,
                        'nivel_partner_id' => $nivel->id,
                        'precio' => $precioCalculado,
                        'currency_id' => $moneda->id,
                    ]);
                }

                $count++;
            }

            DB::commit();
            $this->command->info("¡Seeder completado con éxito! Se crearon/actualizaron {$count} productos de riego.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error("Error ejecutando ProductRiegoSeeder: " . $e->getMessage());
            throw $e;
        }
    }
}

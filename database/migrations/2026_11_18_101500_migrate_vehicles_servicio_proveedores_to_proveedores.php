<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Migrar datos existentes si la tabla antigua y la nueva existen
        if (Schema::hasTable('vehicles_servicio_proveedores') && Schema::hasTable('proveedores')) {
            $oldProveedores = DB::table('vehicles_servicio_proveedores')->get();
            foreach ($oldProveedores as $prov) {
                $exists = DB::table('proveedores')->where('id', $prov->id)->exists();
                if (!$exists) {
                    DB::table('proveedores')->insert([
                        'id' => $prov->id,
                        'razon_social' => $prov->nombre,
                        'tipo_persona' => 'Moral',
                        'rfc' => null,
                        'domicilio' => null,
                        'correo' => null,
                        'telefono' => null,
                        'dias_credito' => null,
                        'descripcion' => 'Migrado automáticamente de vehicles_servicio_proveedores',
                        'created_at' => $prov->created_at ?? now(),
                        'updated_at' => $prov->updated_at ?? now(),
                    ]);
                }
            }
        }

        // 2. Modificar llave foránea en vehicles_detalles_servicio si aún apunta a vehicles_servicio_proveedores
        if (Schema::hasTable('vehicles_detalles_servicio')) {
            $fkDetails = DB::select("SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vehicles_detalles_servicio' AND COLUMN_NAME = 'proveedor_id' AND REFERENCED_TABLE_NAME IS NOT NULL");

            if (!empty($fkDetails)) {
                $currentRef = $fkDetails[0]->REFERENCED_TABLE_NAME;
                $currentConstraint = $fkDetails[0]->CONSTRAINT_NAME;
                if ($currentRef !== 'proveedores') {
                    Schema::table('vehicles_detalles_servicio', function (Blueprint $table) use ($currentConstraint) {
                        $table->dropForeign($currentConstraint);
                    });
                    Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
                        $table->foreign('proveedor_id')
                            ->references('id')
                            ->on('proveedores')
                            ->nullOnDelete();
                    });
                }
            } else {
                Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
                    $table->foreign('proveedor_id')
                        ->references('id')
                        ->on('proveedores')
                        ->nullOnDelete();
                });
            }
        }

        // 3. Asegurar llave foránea en vehicles_ordenes_servicio
        if (Schema::hasTable('vehicles_ordenes_servicio')) {
            $fkOrdenes = DB::select("SELECT CONSTRAINT_NAME, REFERENCED_TABLE_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vehicles_ordenes_servicio' AND COLUMN_NAME = 'proveedor_id' AND REFERENCED_TABLE_NAME IS NOT NULL");

            if (!empty($fkOrdenes)) {
                $currentRef = $fkOrdenes[0]->REFERENCED_TABLE_NAME;
                $currentConstraint = $fkOrdenes[0]->CONSTRAINT_NAME;
                if ($currentRef !== 'proveedores') {
                    Schema::table('vehicles_ordenes_servicio', function (Blueprint $table) use ($currentConstraint) {
                        $table->dropForeign($currentConstraint);
                    });
                    Schema::table('vehicles_ordenes_servicio', function (Blueprint $table) {
                        $table->foreign('proveedor_id')
                            ->references('id')
                            ->on('proveedores')
                            ->nullOnDelete();
                    });
                }
            } else {
                Schema::table('vehicles_ordenes_servicio', function (Blueprint $table) {
                    $table->foreign('proveedor_id')
                        ->references('id')
                        ->on('proveedores')
                        ->nullOnDelete();
                });
            }
        }

        // 4. Eliminar la tabla obsoleta vehicles_servicio_proveedores
        Schema::dropIfExists('vehicles_servicio_proveedores');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recrear vehicles_servicio_proveedores si no existe
        if (!Schema::hasTable('vehicles_servicio_proveedores')) {
            Schema::create('vehicles_servicio_proveedores', function (Blueprint $table) {
                $table->id();
                $table->string('nombre');
                $table->timestamps();
            });

            if (Schema::hasTable('proveedores')) {
                $provs = DB::table('proveedores')->get();
                foreach ($provs as $p) {
                    DB::table('vehicles_servicio_proveedores')->insert([
                        'id' => $p->id,
                        'nombre' => $p->razon_social,
                        'created_at' => $p->created_at ?? now(),
                        'updated_at' => $p->updated_at ?? now(),
                    ]);
                }
            }
        }

        // 2. Revertir foreign keys
        if (Schema::hasTable('vehicles_detalles_servicio')) {
            Schema::table('vehicles_detalles_servicio', function (Blueprint $table) {
                try {
                    $table->dropForeign(['proveedor_id']);
                } catch (\Throwable $e) {}
                $table->foreign('proveedor_id')
                    ->references('id')
                    ->on('vehicles_servicio_proveedores')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('vehicles_ordenes_servicio')) {
            Schema::table('vehicles_ordenes_servicio', function (Blueprint $table) {
                try {
                    $table->dropForeign(['proveedor_id']);
                } catch (\Throwable $e) {}
            });
        }
    }
};

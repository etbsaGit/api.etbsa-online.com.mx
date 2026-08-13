<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            // Usuario que recibe la notificación
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Usuario que originó la notificación (Admin, RH, Empleado, etc.)
            // Si es automática del sistema quedará NULL.
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Módulo de origen
            // tracking, vacaciones, permisos, viaticos...
            $table->string('module', 50);

            // Tipo de evento
            // created, approved, rejected...
            $table->string('type', 50);

            // Información mostrada al usuario
            $table->string('title');
            $table->text('body');

            // Datos para navegar dentro de la app
            $table->json('data')->nullable();

            // Fecha en que fue leída
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            // Eliminación lógica
            $table->softDeletes();

            // Índices
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'module']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
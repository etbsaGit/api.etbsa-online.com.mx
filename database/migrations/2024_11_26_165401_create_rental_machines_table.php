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
        Schema::create('rental_machines', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('picture')->nullable(); // pic
            $table->string('serial'); // Serial number
            $table->string('model'); // Machine model
            $table->text('description')->nullable(); // Description of the machine
            $table->integer('hours')->default(0); // Hours of use (default 0)
            $table->text('comments')->nullable(); // Additional comments
            $table->enum('status', ['available', 'rented', 'maintenance'])->default('available'); // Machine status
            $table->timestamps(); // created_at and updated_at
            $table->softDeletes(); // Deleted_at for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_machines');
    }
};

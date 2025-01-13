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
        Schema::table('incapacities', function (Blueprint $table) {
            $table->string('folio')->after('id');
            $table->boolean('inicial')->after('folio');

            $table->unsignedBigInteger('incapacity_id')->nullable();
            $table->foreign('incapacity_id')->references('id')->on('incapacities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incapacities', function (Blueprint $table) {
            $table->dropColumn(['folio', 'inicial', 'incapacity_id']);
        });
    }
};

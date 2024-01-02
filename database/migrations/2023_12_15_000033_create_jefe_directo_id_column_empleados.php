<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::table('empleados', function (Blueprint $table) {

            $table->unsignedBigInteger('jefe_directo_id')->nullable();
    
            $table->foreign('jefe_directo_id')->references('id')->on('empleados')->onDelete('set null');
        });
    }


    public function down()
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('jefeDirecto_id');
        });
    }
};

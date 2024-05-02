<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToTechniciansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->integer('antiguedad_minima')->nullable();
            $table->string('jobcode')->nullable();
            $table->string('levelcap')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('technicians', function (Blueprint $table) {
            $table->dropColumn('antiguedad_minima');
            $table->dropColumn('jobcode');
            $table->dropColumn('levelcap');
        });
    }
}


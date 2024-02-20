<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TipoDeSangreSeeder::class);
        $this->call(AntiguedadSeeder::class);
        $this->call(SucursalSeeder::class);
        $this->call(PuestoSeeder::class);
        $this->call(DepartamentoSeeder::class);
        $this->call(LineaSeeder::class);
        $this->call(RequisitoSeeder::class);
        $this->call(PlantillaSeeder::class);
        $this->call(EstadoCivilSeeder::class);
        $this->call(EscolaridadSeeder::class);
        $this->call(EstadosDelEstudioSeeder::class);
        $this->call(DocumentoQueAvalaSeeder::class);
        $this->call(ConstelacionSeeder::class);
        $this->call(TipoDeAsignacionSeeder::class);
        $this->call(UserSeeder::class);
        //$this->call(EmpleadoSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(MedicamentoSeeder::class);
        $this->call(EstatusSeeder::class);
        //$this->call(UserRoleSeeder::class);
        $this->call(ChangePasswordSeeder::class);
    }
}

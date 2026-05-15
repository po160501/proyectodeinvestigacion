<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cambia las columnas TIME a TIME(3) para soportar milisegundos.
     * MySQL TIME(3) guarda hasta 3 dígitos de fracción de segundo (= milisegundos).
     */
    public function up(): void
    {
        // Tabla alertas: columna 'hora'
        DB::statement('ALTER TABLE alertas MODIFY hora TIME(3) NOT NULL');

        // Tabla exposicion_ruido: columnas 'hora_inicio' y 'hora_fin'
        DB::statement('ALTER TABLE exposicion_ruido MODIFY hora_inicio TIME(3) NOT NULL');
        DB::statement('ALTER TABLE exposicion_ruido MODIFY hora_fin TIME(3) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE alertas MODIFY hora TIME NOT NULL');
        DB::statement('ALTER TABLE exposicion_ruido MODIFY hora_inicio TIME NOT NULL');
        DB::statement('ALTER TABLE exposicion_ruido MODIFY hora_fin TIME NOT NULL');
    }
};

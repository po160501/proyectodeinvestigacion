<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('trabajadores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('empresa');
            $table->string('area');
            $table->timestamps();
        });

        Schema::create('exposicion_ruido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trabajador_id')->constrained('trabajadores')->onDelete('cascade');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('tiempo_exposicion'); // minutos
            $table->decimal('decibeles', 5, 2);
            $table->date('fecha');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exposicion_ruido');
        Schema::dropIfExists('trabajadores');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->foreignId('trabajador_id')->nullable()->constrained('trabajadores')->nullOnDelete();
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
        });

        // PDR manual: valor patrón ingresado manualmente
        Schema::create('pdr_manual', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora');
            $table->decimal('patron_db', 5, 2);
            $table->decimal('iot_db', 5, 2)->nullable(); // se puede llenar también manual
            $table->string('nota')->nullable();
            $table->timestamps();
        });

        // ETAG manual: hora evento y hora alerta ingresados manualmente
        Schema::create('etag_manual', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora_evento');
            $table->time('hora_alerta');
            $table->integer('segundos')->storedAs('TIME_TO_SEC(TIMEDIFF(hora_alerta, hora_evento))');
            $table->string('nota')->nullable();
            $table->timestamps();
        });

        // TERC manual: hora inicio y hora fin ingresados manualmente
        Schema::create('terc_manual', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->integer('minutos')->storedAs('TIMESTAMPDIFF(MINUTE, CONCAT(fecha,\' \',hora_inicio), CONCAT(fecha,\' \',hora_fin))');
            $table->decimal('decibeles', 5, 2)->nullable();
            $table->string('nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('terc_manual');
        Schema::dropIfExists('etag_manual');
        Schema::dropIfExists('pdr_manual');
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropForeign(['trabajador_id']);
            $table->dropForeign(['obra_id']);
            $table->dropColumn(['trabajador_id', 'obra_id']);
        });
    }
};

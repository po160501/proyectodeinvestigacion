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
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropForeign(['sensor_id']);
            $table->dropColumn('sensor_id');
        });

        Schema::dropIfExists('mediciones_ruido');
        Schema::dropIfExists('sensores');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('sensores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ubicacion')->nullable();
            $table->decimal('nivel_actual', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('mediciones_ruido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained('sensores')->onDelete('cascade');
            $table->decimal('decibeles', 5, 2);
            $table->date('fecha');
            $table->time('hora', 3);
            $table->timestamps();
        });

        Schema::table('alertas', function (Blueprint $table) {
            $table->foreignId('sensor_id')->nullable()->constrained('sensores')->onDelete('cascade');
        });
    }
};

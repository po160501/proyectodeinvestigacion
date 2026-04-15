<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mediciones_ruido', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sensor_id')->constrained('sensores')->onDelete('cascade');
            $table->decimal('decibeles', 5, 2);
            $table->date('fecha');
            $table->time('hora');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediciones_ruido');
    }
};

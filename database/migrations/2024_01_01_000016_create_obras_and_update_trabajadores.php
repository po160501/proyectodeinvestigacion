<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->integer('limite_db')->default(85);
            $table->timestamps();
        });

        Schema::table('trabajadores', function (Blueprint $table) {
            $table->foreignId('obra_id')->nullable()->constrained('obras')->nullOnDelete();
            $table->string('telefono')->nullable();
            $table->string('token_sesion')->nullable()->unique();
            $table->time('jornada_inicio')->nullable();
            $table->time('jornada_fin')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropForeign(['obra_id']);
            $table->dropColumn(['obra_id', 'telefono', 'token_sesion', 'jornada_inicio', 'jornada_fin']);
        });
        Schema::dropIfExists('obras');
    }
};

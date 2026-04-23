<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('obra_id')->constrained('obras')->onDelete('cascade');
            $table->string('nombre');
            $table->timestamps();
        });

        Schema::table('trabajadores', function (Blueprint $table) {
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trabajadores', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });
        Schema::dropIfExists('areas');
    }
};

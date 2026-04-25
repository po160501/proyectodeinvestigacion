<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pdr_manual', function (Blueprint $table) {
            $table->string('fuente')->nullable()->after('nota');
        });
        Schema::table('etag_manual', function (Blueprint $table) {
            $table->string('fuente')->nullable()->after('nota');
        });
        Schema::table('terc_manual', function (Blueprint $table) {
            $table->string('fuente')->nullable()->after('nota');
        });
    }

    public function down(): void
    {
        Schema::table('pdr_manual', function (Blueprint $table) {
            $table->dropColumn('fuente');
        });
        Schema::table('etag_manual', function (Blueprint $table) {
            $table->dropColumn('fuente');
        });
        Schema::table('terc_manual', function (Blueprint $table) {
            $table->dropColumn('fuente');
        });
    }
};

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
        Schema::table('logbooks', function (Blueprint $table) {
            $table->decimal('lokasi_lat', 10, 8)->nullable()->after('lokasi');
            $table->decimal('lokasi_lng', 11, 8)->nullable()->after('lokasi_lat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logbooks', function (Blueprint $table) {
            $table->dropColumn(['lokasi_lat', 'lokasi_lng']);
        });
    }
};

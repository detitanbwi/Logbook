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
        Schema::create('kpi_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->decimal('target_angka', 14, 2)->nullable();
            $table->string('satuan', 100)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('status_aktif')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['status_aktif', 'deleted_at'], 'kpi_masters_status_deleted_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpi_masters');
    }
};

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
        Schema::create('logbook_kpi_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('logbook_id')->constrained('logbooks');
            $table->foreignUuid('kpi_id')->constrained('kpi_masters');
            $table->string('kpi_nama');
            $table->decimal('target_angka', 14, 2)->nullable();
            $table->string('satuan', 100)->nullable();
            $table->decimal('capaian_angka', 14, 2)->nullable();
            $table->string('lampiran_file')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['logbook_id', 'kpi_id'], 'logbook_kpi_details_logbook_kpi_idx');
            $table->index(['kpi_id', 'finished_at'], 'logbook_kpi_details_kpi_finished_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbook_kpi_details');
    }
};

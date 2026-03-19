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
            $table->boolean('is_finished')->default(false);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
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

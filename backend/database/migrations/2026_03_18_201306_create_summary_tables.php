<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_staff_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->date('tanggal');
            $table->unsignedInteger('total_logbooks')->default(0);
            $table->unsignedInteger('submitted_logbooks')->default(0);
            $table->unsignedInteger('accepted_logbooks')->default(0);
            $table->unsignedInteger('rejected_logbooks')->default(0);
            $table->unsignedInteger('total_work_minutes')->default(0);
            $table->unsignedInteger('total_kpi')->default(0);
            $table->decimal('target_angka_total', 14, 2)->default(0);
            $table->decimal('capaian_angka_total', 14, 2)->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'tanggal'], 'daily_staff_summaries_user_tanggal_unique');
            $table->index(['tanggal', 'accepted_logbooks'], 'daily_staff_summaries_tanggal_accepted_idx');
        });

        Schema::create('daily_kpi_summaries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('kpi_id')->constrained('kpi_masters');
            $table->date('tanggal');
            $table->string('kpi_nama');
            $table->string('satuan', 100)->nullable();
            $table->decimal('target_angka_total', 14, 2)->default(0);
            $table->decimal('capaian_angka_total', 14, 2)->default(0);
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->unsignedInteger('total_lampiran')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'kpi_id', 'tanggal'], 'daily_kpi_summaries_user_kpi_tanggal_unique');
            $table->index(['kpi_id', 'tanggal'], 'daily_kpi_summaries_kpi_tanggal_idx');
            $table->index(['tanggal', 'progress_percent'], 'daily_kpi_summaries_tanggal_progress_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_kpi_summaries');
        Schema::dropIfExists('daily_staff_summaries');
    }
};

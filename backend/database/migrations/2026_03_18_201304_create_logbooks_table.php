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
        Schema::create('logbooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users');
            $table->date('tanggal');
            $table->time('start_kerja');
            $table->time('end_kerja')->nullable();
            $table->text('lokasi')->nullable();
            $table->enum('status', ['SUBMITTED', 'ACCEPTED', 'REJECTED'])->default('SUBMITTED');
            $table->integer('rating')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('reviewer_comment')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['user_id', 'tanggal'], 'logbooks_user_tanggal_idx');
            $table->index(['status', 'tanggal'], 'logbooks_status_tanggal_idx');
            $table->index(['reviewed_by', 'status', 'tanggal'], 'logbooks_reviewer_status_tanggal_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};

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
            $table->timestamp('start_kerja');
            $table->timestamp('end_kerja')->nullable();
            $table->string('lokasi_start');
            $table->string('lokasi_end')->nullable();
            $table->jsonb('gambar_bukti')->nullable();
            $table->enum('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->default('DRAFT');
            $table->integer('rating')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
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

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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('npp')->unique();
            $table->string('password');
            $table->string('foto')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('nik', 16)->nullable();
            $table->string('npwp')->nullable();
            $table->text('alamat')->nullable();
            $table->enum('status_perkawinan', ['belum_menikah', 'menikah'])->default('belum_menikah');
            $table->text('riwayat_pendidikan')->nullable();
            $table->text('riwayat_karir')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->enum('role', ['super_admin', 'admin', 'staff'])->default('staff');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('supervisor_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};

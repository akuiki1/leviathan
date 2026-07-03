<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Riwayat jabatan ASN. Menjaga jejak mutasi/promosi tanpa memutus
     * keanggotaan tim (yang tetap menempel ke user_id yang sama).
     */
    public function up(): void
    {
        Schema::create('jabatan_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('jabatan_id')->constrained('jabatans');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable(); // null = jabatan aktif saat ini
            $table->timestamps();

            $table->index(['user_id', 'tanggal_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatan_histories');
    }
};

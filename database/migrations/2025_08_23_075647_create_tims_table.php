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
        // tabel tim
        Schema::create('tims', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tim');
            $table->text('keterangan');
            $table->string('sk_file');
            $table->foreignId('created_by')->constrained('users'); // siapa yg buat
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // ✅ admin approve/reject
            $table->timestamps();
        });


        // tabel pivot tim_user
        Schema::create('tim_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tim_id')->constrained('tims')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('jabatan')->nullable(); // jabatan dalam tim
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tims');
    }
};

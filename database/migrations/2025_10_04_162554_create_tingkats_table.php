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
        Schema::create('tingkats', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // Nama tingkat kelas, misal: "X", "XI", "XII"
            $table->string('kode')->unique(); // Kode unik tingkat, misal: "10", "11", "12"
            $table->enum('jenjang', ['SD', 'SMP', 'SMA']); // Jenjang pendidikan: SD, SMP, atau SMA
            $table->text('deskripsi')->nullable(); // Deskripsi tingkat kelas (opsional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tingkats');
    }
};

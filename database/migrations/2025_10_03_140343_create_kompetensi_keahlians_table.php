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
        Schema::create('kompt_ahlis', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_url_code')->unique()->comment('Kode unik untuk akses publik URL');
            $table->string('nama'); // Nama kompetensi keahlian/jurusan
            $table->string('kode')->nullable(); // Kode jurusan (opsional)
            $table->string('slug')->unique(); // Slug unik untuk URL
            $table->text('deskripsi')->nullable(); // Deskripsi jurusan (opsional)
            $table->unsignedBigInteger('kepala_jurusan_id')->nullable()->comment('Relasi ke guru sebagai kepala jurusan');
            $table->foreign('kepala_jurusan_id')->references('id')->on('gurus')->onDelete('set null');
            $table->boolean('is_aktif')->default(true); // Status aktif/tidak
            $table->unsignedBigInteger('created_by')->nullable(); // User yang membuat
            $table->unsignedBigInteger('updated_by')->nullable(); // User yang mengupdate
            $table->unsignedBigInteger('deleted_by')->nullable(); // User yang mengupdate
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kompt_ahli');
    }
};

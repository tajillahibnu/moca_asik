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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tingkat_id')->constrained('tingkats'); // Relasi ke tabel tingkats
            $table->string('nama'); // Nama kelas, misal: "A", "B", "C"
            $table->string('kode')->unique(); // Kode unik kelas, misal: "7A", "10B"
            $table->text('deskripsi')->nullable(); // Deskripsi kelas (opsional)
            $table->foreignId('kompt_ahli_id')->nullable()->constrained('kompt_ahlis')->comment('Relasi ke tabel kompt_ahlis, jika kelas ini untuk kompetensi ahli'); // FK ke kompt_ahlis
            $table->timestamps();
        });

        // Menambahkan kolom kelas_id pada tabel siswa untuk mengetahui kelas siswa saat ini
        Schema::table('siswas', function (Blueprint $table) {
            $table->foreignId('kelas_id')->nullable()->after('id')->constrained('kelas')->nullOnDelete()->comment('Kelas saat ini yang diikuti siswa');
        });

        // Membuat tabel siswa_kelas untuk relasi siswa dengan kelas (riwayat penempatan siswa di kelas)
        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswas')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->date('mulai')->nullable()->comment('Tanggal mulai masuk kelas');
            $table->date('selesai')->nullable()->comment('Tanggal keluar dari kelas');
            $table->boolean('is_aktif')->default(true)->comment('Status aktif di kelas ini');
            $table->timestamps();

            $table->unique(['siswa_id', 'kelas_id', 'mulai'], 'unique_siswa_kelas_mulai');
        });

        // Membuat tabel riwayat_walikelas untuk menyimpan riwayat wali kelas
        Schema::create('riwayat_walikelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');
            $table->date('mulai')->nullable()->comment('Tanggal mulai menjadi wali kelas');
            $table->date('selesai')->nullable()->comment('Tanggal selesai menjadi wali kelas');
            $table->boolean('is_aktif')->default(true)->comment('Menandakan apakah guru ini wali kelas aktif');
            $table->timestamps();

            $table->unique(['kelas_id', 'guru_id', 'mulai'], 'unique_kelas_guru_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop riwayat_walikelas table
        Schema::dropIfExists('riwayat_walikelas');

        // Drop siswa_kelas table
        Schema::dropIfExists('siswa_kelas');

        // Drop kelas_id column from siswas table
        Schema::table('siswas', function (Blueprint $table) {
            $table->dropForeign(['kelas_id']);
            $table->dropColumn('kelas_id');
        });

        // Drop kelas table
        Schema::dropIfExists('kelas');
    }
};

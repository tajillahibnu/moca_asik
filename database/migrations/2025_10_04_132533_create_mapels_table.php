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
        Schema::create('mapels', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 64)->unique();
            $table->string('nama', 64)->unique();
            $table->string('nama_report', 64)->unique();
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Relasi ke mapel induk');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('mapels')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mapels');
    }
};

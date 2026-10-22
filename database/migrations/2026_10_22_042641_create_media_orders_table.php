<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_orders', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking')->unique();
            $table->string('nama_pemohon');
            $table->string('fraksi');
            $table->string('no_whatsapp');
            $table->string('nama_kegiatan');
            $table->string('kategori');
            $table->date('tanggal_kegiatan');
            $table->time('waktu_mulai');
            $table->time('waktu_disetujui')->nullable();
            $table->string('jenis_publikasi');
            $table->string('file_dokumen')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan_admin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_orders');
    }
};

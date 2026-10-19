<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom pengajuan.daftar_penerima tidak dipakai lagi.
     *
     * Daftar penerima kini disimpan di tabel penerima_pengajuan — satu baris
     * per penerima — supaya bisa dicocokkan dengan catatan pembayaran di tabel
     * bendahara saat pencairan bertahap. Hal itu tidak mungkin dilakukan selama
     * daftarnya tersimpan sebagai satu gumpalan JSON.
     *
     * Kolomnya dibuang lewat migrasi tersendiri, bukan dengan menghapus migrasi
     * yang menambahkannya, karena migrasi itu mungkin sudah pernah dijalankan.
     * Pemeriksaan hasColumn membuat migrasi ini aman dijalankan di basis data
     * yang belum pernah punya kolom tersebut.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('pengajuan', 'daftar_penerima')) {
            return;
        }

        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn('daftar_penerima');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('pengajuan', 'daftar_penerima')) {
            return;
        }

        Schema::table('pengajuan', function (Blueprint $table) {
            $table->json('daftar_penerima')->nullable()->after('catatan_pengaju');
        });
    }
};

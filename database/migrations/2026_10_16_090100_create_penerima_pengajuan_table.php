<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Daftar penerima yang diusulkan pengaju.
     *
     * Satu SPJ bisa dibayarkan ke banyak orang (mis. honor petugas lapangan),
     * dan pengaju adalah pihak yang tahu daftar itu. Dengan daftar ini ikut
     * dikirim sejak pengajuan dibuat, Bendahara tidak perlu lagi mengetik ulang
     * nama dan nominal satu per satu — form pencairannya terisi dari sini.
     *
     * Tabelnya terpisah dari tabel bendahara karena keduanya berbeda arti:
     * - penerima_pengajuan : usulan pengaju, boleh berubah selama masih revisi.
     * - bendahara           : catatan pembayaran yang benar-benar terjadi.
     *
     * Keduanya tidak dipaksa sama. Bendahara boleh menyesuaikan daftar sebelum
     * membayar, dan usulan awal tetap tersimpan utuh sebagai pembanding.
     */
    public function up(): void
    {
        Schema::create('penerima_pengajuan', function (Blueprint $table) {
            $table->id('id_penerima_pengajuan');
            $table->unsignedBigInteger('id_pengajuan');

            // Nomor urut dari daftar yang diisi/diimpor pengaju, dipakai agar
            // urutan tampil sama dengan urutan di berkas asalnya.
            $table->unsignedInteger('urutan')->default(1);

            $table->string('nama_penerima', 150);
            $table->decimal('nominal', 15, 2);
            $table->timestamps();

            // Daftar ini tidak punya arti tanpa pengajuannya.
            $table->foreign('id_pengajuan')
                ->references('id_pengajuan')
                ->on('pengajuan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->index(['id_pengajuan', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penerima_pengajuan');
    }
};

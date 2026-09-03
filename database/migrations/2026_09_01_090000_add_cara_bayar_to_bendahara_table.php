<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Metode UP/TUP dapat dibayarkan lewat transfer maupun tunai.
     *
     * - cara_bayar  : membedakan kedua jalur tersebut.
     * - no_kwitansi : bukti pembayaran tunai (pengganti data rekening).
     *
     * Tanggal pembayaran tetap memakai kolom tgl_transfer yang sudah ada agar
     * seluruh laporan punya satu kolom "kapan uang keluar", baik transfer
     * maupun tunai. Di antarmuka, labelnya menyesuaikan cara bayar.
     */
    public function up(): void
    {
        Schema::table('bendahara', function (Blueprint $table) {
            $table->enum('cara_bayar', ['TRANSFER', 'TUNAI'])
                ->nullable()
                ->after('tahap');

            $table->string('no_kwitansi')
                ->nullable()
                ->after('cara_bayar');
        });

        // Data lama seluruhnya lewat transfer.
        DB::table('bendahara')
            ->whereIn('tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
            ->update(['cara_bayar' => 'TRANSFER']);
    }

    public function down(): void
    {
        Schema::table('bendahara', function (Blueprint $table) {
            $table->dropColumn(['cara_bayar', 'no_kwitansi']);
        });
    }
};

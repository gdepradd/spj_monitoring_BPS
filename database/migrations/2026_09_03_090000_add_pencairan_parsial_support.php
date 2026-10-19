<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pencairan dapat dilakukan penuh atau bertahap (parsial).
     *
     * - bendahara.nominal        : besaran uang yang dibayarkan pada satu catatan.
     * - bendahara.nama_penerima  : penerima uang pada pembayaran tersebut, karena
     *                              pembayaran parsial bisa jatuh ke orang berbeda.
     * - pengajuan.jenis_pencairan: PENUH atau PARSIAL, dipilih Bendahara.
     * - status PENCAIRAN_PARSIAL : menandai pengajuan yang pembayarannya belum lunas
     *                              sehingga mudah ditemukan kembali di dashboard.
     *
     * Nomor rekening dan nama bank tidak lagi diisi (cukup nama penerima dan
     * tanggal). Kolomnya dibiarkan ada supaya data lama tetap terbaca.
     */
    public function up(): void
    {
        Schema::table('bendahara', function (Blueprint $table) {
            $table->decimal('nominal', 15, 2)->nullable()->after('cara_bayar');
            $table->string('nama_penerima')->nullable()->after('nominal');
        });

        Schema::table('pengajuan', function (Blueprint $table) {
            $table->enum('jenis_pencairan', ['PENUH', 'PARSIAL'])->nullable()->after('metode_pembayaran');
        });

        // Status baru diletakkan tepat sebelum SELESAI.
        $urutanSelesai = (int) DB::table('status_pengajuan')->where('kode_status', 'SELESAI')->value('urutan');

        if ($urutanSelesai > 0) {
            DB::table('status_pengajuan')->where('kode_status', 'SELESAI')->update(['urutan' => $urutanSelesai + 1]);
        }

        DB::table('status_pengajuan')->updateOrInsert(
            ['kode_status' => 'PENCAIRAN_PARSIAL'],
            [
                'nama_status' => 'Pencairan Sebagian',
                'keterangan' => 'Sebagian nominal sudah dibayarkan, sisanya menunggu pembayaran berikutnya.',
                'urutan' => $urutanSelesai > 0 ? $urutanSelesai : 11,
            ]
        );

        // Pembayaran lama seluruhnya bersifat penuh.
        // Menggantikan query JOIN update yang tidak didukung SQLite
        $records = DB::table('bendahara as b')
            ->join('pengajuan as p', 'p.id_pengajuan', '=', 'b.id_pengajuan')
            ->whereIn('b.tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI'])
            ->whereNull('b.nominal')
            ->select('b.id_bendahara', 'b.atas_nama_rekening', 'p.total_nominal', 'p.nama_penerima')
            ->get();

        foreach ($records as $row) {
            DB::table('bendahara')
                ->where('id_bendahara', $row->id_bendahara)
                ->update([
                    'nominal' => $row->total_nominal,
                    'nama_penerima' => $row->atas_nama_rekening ?? $row->nama_penerima,
                ]);
        }

        DB::table('pengajuan')
            ->whereNull('jenis_pencairan')
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('bendahara')
                ->whereColumn('bendahara.id_pengajuan', 'pengajuan.id_pengajuan')
                ->whereIn('bendahara.tahap', ['PEMBAYARAN_LANGSUNG', 'KONFIRMASI']))
            ->update(['jenis_pencairan' => 'PENUH']);
    }

    public function down(): void
    {
        Schema::table('bendahara', function (Blueprint $table) {
            $table->dropColumn(['nominal', 'nama_penerima']);
        });

        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn('jenis_pencairan');
        });

        DB::table('status_pengajuan')->where('kode_status', 'PENCAIRAN_PARSIAL')->delete();

        $urutanSelesai = (int) DB::table('status_pengajuan')->where('kode_status', 'SELESAI')->value('urutan');

        if ($urutanSelesai > 1) {
            DB::table('status_pengajuan')->where('kode_status', 'SELESAI')->update(['urutan' => $urutanSelesai - 1]);
        }
    }
};

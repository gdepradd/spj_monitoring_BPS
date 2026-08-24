<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Dua perbaikan data:
     *
     * 1. Tabel status_pencairan tidak sinkron dengan nilai yang benar-benar
     *    ditulis PencairanService. Service menyimpan id_status 1 = ACC,
     *    2 = REVISI, 3 = TOLAK, sedangkan barisnya berisi 1 = MENUNGGU,
     *    2 = SESUAI, 3 = TIDAK_SESUAI. Akibatnya relasi statusPencairan
     *    mengembalikan nama status yang keliru (mis. proses ACC terbaca
     *    "Menunggu", permintaan revisi terbaca "Sesuai").
     *
     *    Baris diselaraskan berdasarkan id sehingga seluruh data lama ikut
     *    benar tanpa perlu menyentuh kolom id_status di tabel transaksi.
     *
     * 2. Kolom perihal tidak pernah terisi karena sebelumnya tidak fillable,
     *    padahal controller menurunkannya dari keterangan dan beberapa
     *    halaman menampilkannya. Baris lama diisi ulang dari keterangan
     *    dengan aturan yang sama seperti di controller.
     */
    public function up(): void
    {
        $statuses = [
            1 => ['kode_status' => 'ACC', 'nama_status' => 'ACC', 'keterangan' => 'Disetujui dan diteruskan ke tahap berikutnya.'],
            2 => ['kode_status' => 'REVISI', 'nama_status' => 'Revisi', 'keterangan' => 'Dikembalikan untuk diperbaiki pengaju.'],
            3 => ['kode_status' => 'DITOLAK', 'nama_status' => 'Ditolak', 'keterangan' => 'Pengajuan ditolak pada tahap pencairan.'],
            4 => ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai', 'keterangan' => 'Tahap pencairan selesai.'],
            5 => ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu', 'keterangan' => 'Menunggu proses pencairan.'],
        ];

        foreach ($statuses as $id => $status) {
            DB::table('status_pencairan')->updateOrInsert(
                ['id_status_pencairan' => $id],
                $status
            );
        }

        DB::table('pengajuan')
            ->whereNull('perihal')
            ->whereNotNull('keterangan')
            ->orderBy('id_pengajuan')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('pengajuan')
                        ->where('id_pengajuan', $row->id_pengajuan)
                        ->update([
                            'perihal' => Str::limit($row->keterangan, 100, '...'),
                        ]);
                }
            }, 'id_pengajuan');
    }

    public function down(): void
    {
        $statuses = [
            1 => ['kode_status' => 'MENUNGGU', 'nama_status' => 'Menunggu', 'keterangan' => 'Menunggu proses pencairan.'],
            2 => ['kode_status' => 'SESUAI', 'nama_status' => 'Sesuai', 'keterangan' => 'Data dinyatakan sesuai.'],
            3 => ['kode_status' => 'TIDAK_SESUAI', 'nama_status' => 'Tidak Sesuai', 'keterangan' => 'Data dinyatakan tidak sesuai.'],
            4 => ['kode_status' => 'SELESAI', 'nama_status' => 'Selesai', 'keterangan' => 'Tahap pencairan selesai.'],
        ];

        foreach ($statuses as $id => $status) {
            DB::table('status_pencairan')
                ->where('id_status_pencairan', $id)
                ->update($status);
        }

        DB::table('status_pencairan')->where('id_status_pencairan', 5)->delete();
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Divisi berganti nama menjadi "tim", dan satu pegawai boleh masuk lebih
     * dari satu tim.
     *
     * Keanggotaan yang semula satu kolom (users.id_divisi) dipindahkan ke tabel
     * pivot tim_user supaya jumlahnya tidak lagi dibatasi satu. Kolom lamanya
     * dibuang setelah isinya dipindahkan, bukan dibiarkan menganggur, agar tidak
     * ada dua sumber kebenaran untuk hal yang sama.
     *
     * pengajuan.id_tim ditambahkan karena pengaju yang punya banyak tim harus
     * menyatakan tim mana yang mengajukan. Kolom teks tim_pengaju tetap ada
     * sebagai cuplikan nama saat pengajuan dibuat — nama tim bisa diubah admin,
     * sedangkan dokumen SPJ yang sudah berjalan tidak boleh ikut berubah.
     *
     * Penggantian nama kolom memakai sintaks ALTER ... CHANGE, bukan
     * Schema::renameColumn(), karena renameColumn pada Laravel 10 menuntut
     * paket doctrine/dbal yang tidak dipasang di proyek ini.
     */
    public function up(): void
    {
        // Keanggotaan lama disimpan dulu: kolom sumbernya dibuang di langkah berikutnya.
        $keanggotaanLama = DB::table('users')
            ->whereNotNull('id_divisi')
            ->pluck('id_divisi', 'id_user');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_divisi']);
            $table->dropColumn('id_divisi');
        });

        Schema::rename('divisi', 'tim');

        DB::statement('ALTER TABLE tim CHANGE id_divisi id_tim BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE tim CHANGE nama_divisi nama_tim VARCHAR(100) NOT NULL');

        Schema::create('tim_user', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tim');
            $table->unsignedBigInteger('id_user');

            // Kunci gabungan sekaligus mencegah satu orang terdaftar dua kali
            // pada tim yang sama.
            $table->primary(['id_tim', 'id_user']);

            // Baris pivot tidak menyimpan apa pun selain keanggotaan, jadi
            // menghapus tim atau user cukup menghapus barisnya.
            $table->foreign('id_tim')
                ->references('id_tim')
                ->on('tim')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        foreach ($keanggotaanLama as $idUser => $idTim) {
            DB::table('tim_user')->insert([
                'id_tim' => $idTim,
                'id_user' => $idUser,
            ]);
        }

        Schema::table('pengajuan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tim')->nullable()->after('id_user');

            // nullOnDelete: menghapus tim tidak boleh menghapus pengajuan.
            // Nama timnya tetap terbaca dari kolom tim_pengaju.
            $table->foreign('id_tim')
                ->references('id_tim')
                ->on('tim')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        // Pengajuan lama hanya menyimpan nama tim. Dicocokkan ke id supaya
        // panel "pengajuan rekan satu tim" ikut menampilkan riwayat lama.
        foreach (DB::table('tim')->pluck('id_tim', 'nama_tim') as $nama => $idTim) {
            DB::table('pengajuan')
                ->whereNull('id_tim')
                ->where('tim_pengaju', $nama)
                ->update(['id_tim' => $idTim]);
        }
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropForeign(['id_tim']);
            $table->dropColumn('id_tim');
        });

        // Satu kolom hanya menampung satu tim, jadi yang dikembalikan adalah
        // tim dengan id terkecil milik tiap user. Keanggotaan selebihnya hilang —
        // konsekuensi yang tak terhindarkan saat kembali ke relasi satu-ke-satu.
        $keanggotaan = DB::table('tim_user')
            ->orderBy('id_tim')
            ->get()
            ->groupBy('id_user')
            ->map(fn ($baris) => $baris->first()->id_tim);

        Schema::dropIfExists('tim_user');

        DB::statement('ALTER TABLE tim CHANGE nama_tim nama_divisi VARCHAR(100) NOT NULL');
        DB::statement('ALTER TABLE tim CHANGE id_tim id_divisi BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        Schema::rename('tim', 'divisi');

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('id_divisi')->nullable()->after('id_role');

            $table->foreign('id_divisi')
                ->references('id_divisi')
                ->on('divisi')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        foreach ($keanggotaan as $idUser => $idDivisi) {
            DB::table('users')->where('id_user', $idUser)->update(['id_divisi' => $idDivisi]);
        }
    }
};

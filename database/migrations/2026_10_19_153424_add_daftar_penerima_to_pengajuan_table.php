<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            // Menyimpan daftar penerima sebagai JSON.
            // Null berarti pegawai tidak mengisi daftar (fitur bersifat opsional).
            $table->json('daftar_penerima')->nullable()->after('catatan_pengaju');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn('daftar_penerima');
        });
    }
};

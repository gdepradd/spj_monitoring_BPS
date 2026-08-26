<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nomor HP tidak dipakai sebagai identitas maupun jalur pemberitahuan di
 * aplikasi ini; email sudah memenuhi keduanya. Menyimpan data pribadi yang
 * tidak terpakai hanya menambah beban tanpa manfaat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('no_hp');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nilai lama tidak dapat dipulihkan; kolom dibuat nullable agar
            // rollback tidak gagal pada tabel yang sudah berisi data.
            $table->string('no_hp', 30)->nullable()->after('email');
        });
    }
};

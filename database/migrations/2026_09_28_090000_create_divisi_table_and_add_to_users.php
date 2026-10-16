<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisi', function (Blueprint $table) {
            $table->id('id_divisi');
            $table->string('nama_divisi', 100)->unique();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            // Nullable: tidak semua user harus punya divisi (mis. admin, bendahara,
            // atau pegawai yang divisinya belum ditentukan).
            $table->unsignedBigInteger('id_divisi')->nullable()->after('id_role');

            // nullOnDelete: menghapus divisi tidak boleh ikut menghapus user —
            // divisinya cukup dikosongkan lalu diisi ulang oleh admin.
            $table->foreign('id_divisi')
                ->references('id_divisi')
                ->on('divisi')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_divisi']);
            $table->dropColumn('id_divisi');
        });

        Schema::dropIfExists('divisi');
    }
};

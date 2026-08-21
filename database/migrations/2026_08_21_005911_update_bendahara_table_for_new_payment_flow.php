<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bendahara', function (Blueprint $table) {

            $table->unsignedBigInteger('id_user')
                ->nullable()
                ->after('id_pengajuan');

            $table->enum('tahap', [
                'PENGAJUAN_SPP',
                'PEMBAYARAN_LANGSUNG',
                'KONFIRMASI',
            ])
                ->nullable()
                ->after('id_user');

            $table->string('no_spp')
                ->nullable()
                ->after('tahap');

            $table->date('tgl_spp')
                ->nullable()
                ->after('no_spp');

            $table->string('no_spm')
                ->nullable()
                ->after('tgl_spp');

            $table->date('tgl_transfer')
                ->nullable()
                ->after('no_spm');

            $table->string('no_sp2d')
                ->nullable()
                ->after('tgl_transfer');

            $table->date('tgl_sp2d')
                ->nullable()
                ->after('no_sp2d');

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('bendahara', function (Blueprint $table) {

            $table->dropForeign(['id_user']);

            $table->dropColumn([
                'id_user',
                'tahap',
                'no_spp',
                'tgl_spp',
                'no_spm',
                'tgl_transfer',
                'no_sp2d',
                'tgl_sp2d',
            ]);
        });
    }
};
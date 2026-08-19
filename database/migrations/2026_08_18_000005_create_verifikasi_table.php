<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verifikasi', function (Blueprint $table) {
            $table->id('id_verifikasi');
            $table->unsignedBigInteger('id_pengajuan');
            $table->unsignedBigInteger('id_verifikator');
            $table->unsignedTinyInteger('tahap');
            $table->dateTime('tanggal_verifikasi');
            $table->unsignedBigInteger('id_status_verifikasi');
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_pengajuan')
                ->references('id_pengajuan')
                ->on('pengajuan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_verifikator')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_status_verifikasi')
                ->references('id_status_verifikasi')
                ->on('status_verifikasi')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->index(['id_pengajuan', 'tahap']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verifikasi');
    }
};

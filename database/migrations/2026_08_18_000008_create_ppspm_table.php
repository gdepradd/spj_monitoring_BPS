<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppspm', function (Blueprint $table) {
            $table->id('id_ppspm');
            $table->unsignedBigInteger('id_pengajuan');
            $table->dateTime('tanggal_proses');
            $table->unsignedBigInteger('id_status');
            $table->text('catatan')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_pengajuan')
                ->references('id_pengajuan')
                ->on('pengajuan')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_status')
                ->references('id_status_pencairan')
                ->on('status_pencairan')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppspm');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan', function (Blueprint $table) {
            $table->id('id_pengajuan');
            $table->string('no_pengajuan', 30)->unique();
            $table->unsignedBigInteger('id_user');
            $table->date('tanggal_pengajuan');
            $table->string('perihal');
            $table->text('keterangan');
            $table->decimal('total_nominal', 15, 2);
            $table->unsignedBigInteger('id_status');
            $table->text('catatan_pengaju')->nullable();
            $table->timestamps();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_status')
                ->references('id_status')
                ->on('status_pengajuan')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan');
    }
};

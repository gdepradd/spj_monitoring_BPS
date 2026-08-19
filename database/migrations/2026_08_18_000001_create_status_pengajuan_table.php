<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_pengajuan', function (Blueprint $table) {
            $table->id('id_status');
            $table->string('kode_status', 50)->unique();
            $table->string('nama_status');
            $table->string('keterangan')->nullable();
            $table->unsignedTinyInteger('urutan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_pengajuan');
    }
};

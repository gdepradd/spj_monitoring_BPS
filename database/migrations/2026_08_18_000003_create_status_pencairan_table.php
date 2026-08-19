<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_pencairan', function (Blueprint $table) {
            $table->id('id_status_pencairan');
            $table->string('kode_status', 30)->unique();
            $table->string('nama_status');
            $table->string('keterangan')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_pencairan');
    }
};

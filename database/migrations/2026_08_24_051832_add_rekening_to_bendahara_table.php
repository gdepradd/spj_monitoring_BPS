<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('bendahara', function (Blueprint $table) {
        $table->string('no_rekening')->nullable();
        $table->string('nama_bank')->nullable();
        $table->string('atas_nama_rekening')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bendahara', function (Blueprint $table) {
            //
        });
    }
};

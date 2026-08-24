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
    Schema::table('ppspm', function (Blueprint $table) {
        $table->string('no_spm')->nullable();
        $table->date('tgl_spm')->nullable();
        $table->string('no_sp2d')->nullable();
        $table->date('tgl_sp2d')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppspm', function (Blueprint $table) {
            //
        });
    }
};

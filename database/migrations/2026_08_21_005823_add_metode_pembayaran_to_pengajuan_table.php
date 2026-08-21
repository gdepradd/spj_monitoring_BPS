<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->enum('metode_pembayaran', [
                'LS_BENDAHARA',
                'LS_PIHAK_KETIGA',
                'UP_TUP',
            ])->nullable()->after('id_status');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan', function (Blueprint $table) {
            $table->dropColumn('metode_pembayaran');
        });
    }
};
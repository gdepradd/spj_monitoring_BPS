<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppspm', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')
                ->nullable()
                ->after('id_pengajuan');

            $table->date('tgl_ajukan_kemenkeu')
                ->nullable()
                ->after('id_status');

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('ppspm', function (Blueprint $table) {
            $table->dropForeign(['id_user']);

            $table->dropColumn([
                'id_user',
                'tgl_ajukan_kemenkeu',
            ]);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppk', function (Blueprint $table) {
            $table->unsignedBigInteger('id_user')
                ->nullable()
                ->after('id_pengajuan');

            $table->string('no_spm')
                ->nullable()
                ->after('id_status');

            $table->date('tgl_spm')
                ->nullable()
                ->after('no_spm');

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('ppk', function (Blueprint $table) {
            $table->dropForeign(['id_user']);

            $table->dropColumn([
                'id_user',
                'no_spm',
                'tgl_spm',
            ]);
        });
    }
};
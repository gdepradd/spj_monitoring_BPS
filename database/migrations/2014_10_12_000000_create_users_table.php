<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('nama_lengkap');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('no_hp', 30);
            $table->unsignedBigInteger('id_role');
            $table->boolean('status_aktif')->default(true);
            $table->unsignedTinyInteger('urutan_verifikator')->nullable();
            $table->timestamps();

            $table->foreign('id_role')
                ->references('id_role')
                ->on('roles')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

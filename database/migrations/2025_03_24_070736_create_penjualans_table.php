<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penjualan', function (Blueprint $table) {
            $table->id('id_penjualan');
            $table->unsignedBigInteger('id_member')->nullable();
            $table->integer('total_item');
            $table->integer('total_harga');
            $table->integer('bayar');
            $table->integer('diterima');
            $table->unsignedBigInteger('id_user');
            $table->timestamps();

            // Relasi opsional jika ada tabel member & user
            // $table->foreign('id_member')->references('id')->on('members')->onDelete('set null');
            // $table->foreign('id_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualan');
    }
};

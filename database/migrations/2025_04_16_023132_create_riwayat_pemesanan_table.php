<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('riwayat_pemesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasir_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('metode');
            $table->integer('total_item');
            
            $table->integer('total_harga');
            $table->integer('bayar');
            $table->integer('kembalian');
            $table->timestamps(); // created_at dan updated_at
        });
    }

    public function down()
    {
        Schema::dropIfExists('riwayat_pemesanan');
    }
};
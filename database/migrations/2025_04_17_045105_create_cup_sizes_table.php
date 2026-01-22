<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCupSizesTable extends Migration
{
    public function up()
    {
        Schema::create('cup_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // small, medium, large
            $table->integer('extra_price')->default(0); // harga tambahan
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cup_sizes');
    }
}

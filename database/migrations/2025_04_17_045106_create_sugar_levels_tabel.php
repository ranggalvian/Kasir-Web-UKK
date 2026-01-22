<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sugar_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // less sugar, normal, more sugar
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sugar_levels');
    }
};

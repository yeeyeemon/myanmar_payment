<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCBPaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('c_b_pays', function (Blueprint $table) {
            $table->id();
            $table->string('msg');
            $table->string('transStatus');
            $table->string('bankTransId');
            $table->string('transAmount');
            $table->string('transCurrency');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('c_b_pays');
    }
}

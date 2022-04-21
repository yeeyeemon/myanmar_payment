<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpgs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transaction_id');
            $table->string('funding_method');
            $table->text('customer_note');
            $table->text('description');
            $table->string('name_on_card');
            $table->string('pan');
            $table->string('card_type');
            $table->string('browser');
            $table->string('ip_address');
            $table->integer('total_amount');
            $table->string('currency');
            $table->string('status');
            $table->datetime('creation_time');
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
        Schema::dropIfExists('mpgs');
    }
}

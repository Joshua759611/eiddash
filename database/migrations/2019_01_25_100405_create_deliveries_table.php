<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliveriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('original_id')->nullable();
            $table->biginteger('kit_id');
            $table->tinyInteger('testtype');
            $table->tinyInteger('lab_id');
            $table->tinyInteger('quarter');
            $table->integer('year');
            $table->tinyInteger('source')->nullable();
            $table->tinyInteger('labfrom')->nullable();
            $table->string('lotno', 20)->nullable();
            $table->date('expiry')->nullable();
            $table->integer('received')->default(0);
            $table->integer('damaged')->default(0);
            $table->integer('receivedby');
            $table->date('datereceived');
            $table->integer('enteredby');
            $table->date('dateentered');
            $table->tinyInteger('synched');
            $table->date('datesynched');
            $table->softDeletes();
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
        Schema::dropIfExists('deliveries');
    }
}

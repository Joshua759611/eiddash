<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralbatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viralbatches', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('original_batch_id')->unsigned()->index();
            $table->boolean('highpriority')->default(false)->nullable();
            $table->boolean('input_complete')->default(false)->nullable();
            $table->boolean('batch_full')->default(false)->nullable();            

            $table->tinyInteger('batch_complete')->default(0)->nullable();
            $table->tinyInteger('site_entry')->unsigned()->default(0)->nullable()->index();
            $table->boolean('sent_email')->default(false)->nullable();

            $table->integer('printedby')->unsigned()->nullable();
            $table->integer('user_id')->unsigned()->nullable()->index();
            $table->integer('received_by')->unsigned()->nullable();

            $table->tinyInteger('lab_id')->unsigned()->index();
            $table->integer('facility_id')->unsigned()->nullable()->index();

            $table->date('datedispatchedfromfacility')->nullable();
            $table->date('datereceived')->nullable()->index();
            $table->date('datedispatched')->nullable()->index();
            $table->date('dateindividualresultprinted')->nullable();
            $table->date('datebatchprinted')->nullable();

            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();
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
        Schema::dropIfExists('viralbatches');
    }
}

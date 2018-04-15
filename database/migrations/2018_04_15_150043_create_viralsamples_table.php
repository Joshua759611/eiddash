<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralsamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viralsamples', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->unsigned()->index();
            $table->integer('batch_id')->unsigned()->index();
            $table->tinyInteger('amrs_location')->nullable();
            $table->string('provider_identifier', 50)->nullable();
            $table->string('order_no', 30)->nullable();
            $table->tinyInteger('vl_test_request_no')->nullable();
            $table->tinyInteger('receivedstatus')->unsigned()->nullable()->index();

            // This will be used instead
            $table->double('age', 5, 2)->unsigned()->nullable();
            $table->tinyInteger('agecategory')->unsigned()->default(0)->index();
            $table->tinyInteger('justification')->unsigned()->nullable()->index();
            $table->string('other_justification')->nullable();
            $table->tinyInteger('sampletype')->unsigned()->nullable()->index();
            $table->tinyInteger('prophylaxis')->unsigned()->index();
            $table->tinyInteger('regimenline')->unsigned()->index();
            $table->tinyInteger('pmtct')->unsigned()->index()->default(3);

            $table->tinyInteger('dilutionfactor')->unsigned()->nullable();
            $table->tinyInteger('dilutiontype')->unsigned()->nullable();

            $table->string('comments', 100)->nullable();
            $table->string('labcomment', 100)->nullable();
            $table->integer('parentid')->unsigned()->default(0);
            // $table->tinyInteger('spots')->unsigned()->nullable();
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();
            $table->string('reason_for_repeat')->nullable();
            $table->tinyInteger('rcategory')->unsigned()->nullable()->index();

            $table->string('result', 50)->nullable()->index();
            $table->string('units', 30)->nullable();
            $table->string('interpretation', 50)->nullable();

            $table->integer('worksheet_id')->unsigned()->nullable();
            $table->boolean('inworksheet')->default(false);

            $table->tinyInteger('flag')->unsigned()->nullable();
            $table->tinyInteger('run')->unsigned()->default(1);
            $table->tinyInteger('repeatt')->unsigned()->nullable();
            $table->tinyInteger('eqa')->unsigned()->nullable();

            $table->integer('approvedby')->unsigned()->nullable();
            $table->integer('approvedby2')->unsigned()->nullable();

            $table->date('datecollected')->nullable()->index();
            $table->date('datetested')->nullable()->index();
            $table->date('datemodified')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();

            $table->tinyInteger('tat1')->unsigned()->nullable();
            $table->tinyInteger('tat2')->unsigned()->nullable();
            $table->tinyInteger('tat3')->unsigned()->nullable();
            $table->tinyInteger('tat4')->unsigned()->nullable();

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
        Schema::dropIfExists('viralsamples');
    }
}

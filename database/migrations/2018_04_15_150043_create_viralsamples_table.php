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
            $table->bigIncrements('id');
            $table->bigInteger('original_sample_id')->unsigned()->nullable()->index();
            $table->bigInteger('patient_id')->unsigned()->index();
            $table->bigInteger('batch_id')->unsigned()->index();
            $table->tinyInteger('amrs_location')->nullable();
            $table->string('provider_identifier', 50)->nullable();
            $table->string('order_no', 30)->nullable();
            $table->tinyInteger('vl_test_request_no')->nullable();
            $table->tinyInteger('receivedstatus')->unsigned()->nullable();

            // This will be used instead 
            $table->float('age', 5, 2)->unsigned()->nullable();
            $table->tinyInteger('age_category')->unsigned()->default(0);
            $table->tinyInteger('justification')->unsigned()->nullable();
            $table->string('other_justification', 50)->nullable();
            $table->tinyInteger('sampletype')->unsigned()->nullable();
            $table->tinyInteger('prophylaxis')->unsigned()->nullable();
            $table->tinyInteger('regimenline')->unsigned()->nullable();
            $table->tinyInteger('pmtct')->unsigned()->nullable()->default(3);

            $table->tinyInteger('dilutionfactor')->unsigned()->nullable();
            $table->tinyInteger('dilutiontype')->unsigned()->nullable();

            $table->string('comments', 30)->nullable();
            $table->string('labcomment', 50)->nullable();
            $table->bigInteger('parentid')->unsigned()->nullable()->default(0);
            // $table->tinyInteger('spots')->unsigned()->nullable();
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();
            $table->string('reason_for_repeat', 50)->nullable();
            $table->tinyInteger('rcategory')->unsigned()->nullable();

            $table->string('result', 30)->nullable();
            $table->string('units', 20)->nullable();
            $table->string('interpretation', 100)->nullable();

            $table->integer('worksheet_id')->unsigned()->nullable();
            // $table->boolean('inworksheet')->default(false);

            $table->tinyInteger('flag')->unsigned()->default(1)->nullable();
            $table->tinyInteger('run')->unsigned()->default(1)->nullable();
            $table->tinyInteger('repeatt')->unsigned()->default(0)->nullable();
            // $table->tinyInteger('eqa')->unsigned()->default(0)->nullable();

            $table->integer('approvedby')->unsigned()->nullable();
            $table->integer('approvedby2')->unsigned()->nullable();

            $table->date('datecollected')->nullable();
            $table->date('datetested')->nullable()->index();
            $table->date('datemodified')->nullable();
            $table->date('dateapproved')->nullable();
            $table->date('dateapproved2')->nullable();

            $table->tinyInteger('tat1')->unsigned()->default(0)->nullable();
            $table->tinyInteger('tat2')->unsigned()->default(0)->nullable();
            $table->tinyInteger('tat3')->unsigned()->default(0)->nullable();
            $table->tinyInteger('tat4')->unsigned()->default(0)->nullable();

            $table->tinyInteger('previous_nonsuppressed')->default(0)->nullable();

            $table->tinyInteger('synched')->default(0)->nullable();
            $table->date('datesynched')->nullable();
            $table->timestamps();

            $table->index(['datetested', 'justification'], 'vl_sample_date_just_index');
            $table->index(['datetested', 'rcategory'], 'vl_sample_date_res_index');
            $table->index(['datetested', 'age_category'], 'vl_sample_date_age_index');
            $table->index(['datetested', 'sampletype'], 'vl_sample_date_stype_index');
            $table->index(['datetested', 'prophylaxis'], 'vl_sample_date_proph_index');
            // $table->index(['datetested', 'justification'], 'vl_sample_date_just_index');
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

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSamplesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('original_sample_id')->unsigned()->nullable()->index();
            $table->bigInteger('patient_id')->unsigned()->index();
            $table->bigInteger('batch_id')->unsigned()->index();
            $table->tinyInteger('amrs_location')->nullable();
            $table->string('provider_identifier', 50)->nullable();
            $table->string('order_no', 30)->nullable();
            $table->string('sample_type', 30)->nullable();

            $table->tinyInteger('mother_age')->unsigned()->nullable();
            $table->string('mother_last_result', 30)->nullable();
            $table->tinyInteger('mother_last_rcategory')->unsigned()->nullable();

            $table->tinyInteger('receivedstatus')->unsigned()->nullable();
            $table->float('age', 7, 4)->unsigned()->nullable();
            
            $table->boolean('redraw')->default(false)->nullable();
            $table->tinyInteger('pcrtype')->unsigned()->nullable();
            $table->tinyInteger('regimen')->unsigned()->nullable();
            $table->tinyInteger('mother_prophylaxis')->unsigned()->nullable();
            $table->tinyInteger('feeding')->unsigned()->nullable();
            $table->tinyInteger('spots')->unsigned()->nullable();
            $table->string('comments', 30)->nullable();
            $table->string('labcomment', 50)->nullable();
            $table->bigInteger('parentid')->unsigned()->nullable()->default(0);
            $table->tinyInteger('rejectedreason')->unsigned()->nullable();
            $table->tinyInteger('reason_for_repeat')->unsigned()->nullable();
            $table->string('interpretation', 100)->nullable();
            $table->tinyInteger('result')->unsigned()->nullable();

            $table->integer('worksheet_id')->unsigned()->nullable();
            // $table->boolean('inworksheet')->default(false);

            $table->tinyInteger('hei_validation')->unsigned()->default(0)->nullable();
            $table->string('enrollment_ccc_no', 25)->nullable();
            $table->tinyInteger('enrollment_status')->unsigned()->default(0)->nullable();
            $table->tinyInteger('referredfromsite')->unsigned()->nullable();
            $table->string('otherreason', 50)->nullable(); 

            $table->tinyInteger('flag')->unsigned()->default(1)->nullable();
            $table->tinyInteger('run')->unsigned()->default(1)->nullable();
            $table->tinyInteger('repeatt')->unsigned()->default(0)->nullable();
            $table->tinyInteger('eqa')->unsigned()->default(0)->nullable();

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

            $table->tinyInteger('previous_positive')->default(0)->nullable();

            $table->tinyInteger('synched')->default(0)->nullable();
            $table->date('datesynched')->nullable();
            $table->timestamps();

            // $table->index(['datetested', 'batch_id', 'result'], 'vl_patient_unq_index');
            // $table->index(['datetested', 'result'], 'eid_sample_date_res_index');
            // $table->index(['datetested', 'pcrtype', 'result'], 'eid_sample_date_pcr_res_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('samples');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateViralpatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('viralpatients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('original_patient_id')->unsigned()->index()->nullable();
            $table->string('patient', 25);
            $table->string('patient_name', 50)->nullable();
            $table->integer('facility_id')->unsigned()->index();
            // $table->string('fullnames', 50)->nullable();
            $table->string('caregiver_phone', 15)->nullable();
            $table->tinyInteger('sex')->unsigned();
            $table->date('dob')->nullable();
            $table->date('initiation_date')->nullable();
            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'patient'], 'vl_patient_unq_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('viralpatients');
    }
}

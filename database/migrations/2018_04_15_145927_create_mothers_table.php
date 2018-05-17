<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMothersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mothers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('original_mother_id')->unsigned()->index(); 

            // The id on viralpatients table
            $table->bigInteger('patient_id')->unsigned()->nullable()->index();
            $table->string('ccc_no', 25)->nullable();
            $table->date('mother_dob')->nullable();
            
            $table->integer('facility_id')->unsigned()->index();
            $table->tinyInteger('hiv_status')->unsigned()->nullable();
            $table->tinyInteger('synched')->default(0);
            $table->date('datesynched')->nullable();
            $table->timestamps();

            $table->index(['facility_id', 'ccc_no'], 'mother_unq_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mothers');
    }
}

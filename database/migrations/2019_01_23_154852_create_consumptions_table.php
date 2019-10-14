<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsumptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('consumptions', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->bigInteger('original_id')->nullable();
        //     $table->tinyInteger('month')->nullable();
        //     $table->integer('year')->nullable();
        //     $table->tinyInteger('testtype')->nullable();
        //     $table->integer('kit_id');
        //     $table->integer('ending')->default(0);
        //     $table->integer('wasted')->default(0);
        //     $table->integer('issued')->default(0);
        //     $table->integer('pos')->default(0);
        //     $table->integer('request')->default(0);
        //     $table->date('datesubmitted')->nullable();
        //     $table->integer('submittedby')->nullable();
        //     $table->tinyInteger('lab_id')->nullable();
        //     $table->text('comments')->nullable();
        //     $table->text('issuedcomments')->nullable();
        //     $table->tinyInteger('approve')->default(0);
        //     $table->text('disapprovereason')->nullable();
        //     $table->tinyInteger('synched')->default(0);
        //     $table->date('datesynched')->nullable();
        //     $table->tinyInteger('test')->default(0);
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('consumptions');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateViralsampletypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            //
            DB::table('viralsampletype')->where('id','=',1)->update(['name'=>'Plasma in PPT']);
            DB::table('viralsampletype')->where('id','=',2)->update(['flag'=>0]);
            DB::table('viralsampletype')->where('id','=',3)->update(['flag'=>0]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

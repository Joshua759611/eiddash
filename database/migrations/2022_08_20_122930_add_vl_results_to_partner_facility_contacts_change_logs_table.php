<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVlResultsToPartnerFacilityContactsChangeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('partner_facility_contacts_change_logs', function (Blueprint $table) {
            //
            $table->boolean('vl_results');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('partner_facility_contacts_change_logs', function (Blueprint $table) {
            //
        });
    }
}

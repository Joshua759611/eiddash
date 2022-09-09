<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEidResultsToPartnerFacilityContactsChangeLogsTable extends Migration
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
            $table->boolean('eid_results');
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

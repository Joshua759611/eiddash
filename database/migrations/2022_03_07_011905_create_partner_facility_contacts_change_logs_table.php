<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartnerFacilityContactsChangeLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_facility_contacts_change_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('partner_contact_id');
            $table->string('county_id');
            $table->string('subcounty_id');
            $table->string('partner_id');
            $table->string('name');
            $table->string('email');
            $table->string('telephone');
            $table->string('type');
            $table->string('critical_results');
            $table->string('contact_change_date');
            $table->string('contact_deleted_at');
            $table->string('contact_updated_by');
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
        Schema::dropIfExists('partner_facility_contacts_change_logs');
    }
}

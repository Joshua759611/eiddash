<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSampleViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
        CREATE OR REPLACE VIEW samples_view AS
        (
          SELECT s.*, b.original_batch_id, b.highpriority, b.datereceived, b.datedispatched, b.site_entry, b.lab_id, b.facility_id,
          p.original_patient_id, p.patient, p.sex, p.dob, p.mother_id, p.entry_point

          FROM samples s
            JOIN batches b ON b.id=s.batch_id
            JOIN patients p ON p.id=s.patient_id

        );
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS samples_view');
    }
}

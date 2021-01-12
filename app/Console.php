<?php

namespace App;


class Console 
{



	public static function all_eid_outcomes($year)
	{
		ini_set('memory_limit', '-1');
        $table = 'sample_complete_view';
        $selectStr = "$table.id, $table.patient, $table.original_batch_id, IF(lab.name IS NULL, poclab.name, lab.name) as labdesc, view_facilitys.county, view_facilitys.subcounty, view_facilitys.partner, view_facilitys.name as facility, view_facilitys.facilitycode, $table.gender_description, $table.dob, $table.age, pcrtype.alias as pcrtype, $table.enrollment_ccc_no, $table.datecollected, $table.datereceived, $table.datetested, $table.datedispatched";

        $selectStr .= ",$table.regimen_name as infantprophylaxis, $table.receivedstatus_name as receivedstatus, $table.labcomment, $table.reason_for_repeat, $table.spots, $table.feeding_name, entry_points.name as entrypoint, ir.name as infantresult, $table.mother_prophylaxis_name as motherprophylaxis, mr.name as motherresult, $table.mother_age, $table.mother_ccc_no, $table.mother_last_result";

        $excelColumns = ['System ID','Sample ID', 'Batch', 'Lab Tested In', 'County', 'Sub-County', 'Partner', 'Facilty', 'Facility Code', 'Gender', 'DOB', 'Age (Months)', 'PCR Type', 'Enrollment CCC No', 'Date Collected', 'Date Received', 'Date Tested', 'Date Dispatched', 'Infant Prophylaxis', 'Received Status', 'Lab Comment', 'Reason for Repeat', 'Spots', 'Feeding', 'Entry Point', 'Result', 'PMTCT Intervention', 'Mother Result', 'Mother Age', 'Mother CCC No', 'Mother Last VL'];


        $model = SampleCompleteView::selectRaw($selectStr)
                ->leftJoin('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
                ->where("$table.facility_id", '<>', 7148);
        $model = $model->leftJoin('labs as lab', 'lab.id', '=', "$table.lab_id");
        $model = $model->leftJoin('entry_points', 'entry_points.id', '=', "$table.entry_point");
        $model = $model->leftJoin('mothers', 'mothers.id', '=', "$table.mother_id")->leftJoin('results as mr', 'mr.id', '=', 'mothers.hiv_status');
        $model = $model->leftJoin('view_facilitys as poclab', 'poclab.id', '=', "$table.lab_id");
        $model = $model->leftJoin('pcrtype', 'pcrtype.id', '=', "$table.pcrtype");
        $model = $model->leftJoin('results as ir', 'ir.id', '=', "$table.result");
        $model = $model->whereRaw("YEAR(datetested)={$year}");

        $data = $model->get()->toArray();

        $filename = 'eid_all_outcomes_for' . $year . '.csv';
        \Maatwebsite\Excel\Facades\Excel::store(new \App\Exports\ReportExport($data, $excelColumns), $filename);
	}
}

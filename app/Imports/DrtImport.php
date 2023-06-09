<?php

namespace App\Imports;

use App\Facility;
use App\Viralpatient;
use App\Viralsample;

use Carbon\Carbon;

use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DrtImport implements OnEachRow, WithHeadingRow
{
	public $drt_rows;
	public $header_row;

	public function __construct()
	{
		$this->drt_rows = [];
		$this->header_row = [];
	}

    public function onRow(Row $row)
    {
    	$row = $row->toArray();
    	if(!$this->header_row){
    		foreach ($row as $key => $value) {
    			$this->header_row[] = $key;
    		}
    		$this->header_row[] = 'Error';
    	}

    	// if(sizeof($this->drt_rows) == 475) \App\Random::drt_export($this->header_row, $this->drt_rows);

    	try {
	    	$date = Carbon::createFromFormat('Y.m.d', $row['date_of_drt_requested'])->toDateString();    		
    	} catch (\Exception $e) {
    		$date = '2020-08-01';    		
    	}

    	$facility = Facility::where('facilitycode', $row['facility_code'])->first();
    	if(!$facility){
    		$row['Error'] = 'Facility Not Found';
    		$this->drt_rows[] = array_values($row);
    		if(sizeof($this->drt_rows) == 475) \App\Random::drt_export($this->header_row, $this->drt_rows);
    		return;
    	}
    	$ccc = trim(str_after($row['ccc_number'], 'CCC'));
    	$patient = Viralpatient::where(['facility_id' => $facility->id, 'patient' => $ccc])->first();
    	if(!$patient){
    		$row['Error'] = 'Patient Not Found';
    		$this->drt_rows[] = array_values($row);
    		if(sizeof($this->drt_rows) == 475) \App\Random::drt_export($this->header_row, $this->drt_rows);
    		return;
    	}

    	if(!is_numeric($row['vl_before_transistion'])){
    		$sample = $patient->sample()->where('datetested', '<', $date)
    			->where(['repeatt' => 0])
    			->orderBy('datetested', 'DESC')
    			->first();

    		if($sample) $row['vl_before_transistion'] = $sample->result;
    	}


		$sample = $patient->sample()->where('datetested', '>', $date)
			->where(['repeatt' => 0])
			->orderBy('datetested', 'DESC')
			->first();


		if($sample) $row['vl_after_transistion'] = $sample->result;
		else{
			$row['vl_after_transistion'] = 'Sample Found';
		}
		$this->drt_rows[] = array_values($row);

		if(sizeof($this->drt_rows) == 475) \App\Random::drt_export($this->header_row, $this->drt_rows);
    }
}

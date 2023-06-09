<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\ViewFacility;
use App\SampleCompleteView;
use App\ViralsampleCompleteView;
use App\Sample;
use App\Viralsample;
use App\SampleView;
use App\ViralsampleView;
use App\Batch;
use App\Viralbatch;
use App\Patient;
use App\Viralpatient;
use Mpdf\Mpdf;
use App\Lookup;
use App\Lab;

class GenerealController extends Controller
{
	public static $columns = array(
				array( 'db' => 'patient','dt' => 1 ),
				array( 'db' => 'facility', 'dt' => 2 ),
				array( 'db' => 'lab', 'dt' => 3 ),
				array( 'db' => 'original_batch_id', 'dt' => 4 ),
				array( 'db' => 'receivedstatus_name', 'dt' => 5 ),
				array( 'db' => 'datecollected', 'dt' => 6),
				array( 'db' => 'datereceived', 'dt' => 7),
				array( 'db' => 'datetested', 'dt' => 8),
				array( 'db' => 'datedispatched', 'dt' => 9),
				array( 'db' => 'result', 'dt' => 10)
			);

    public $dtg_2022_sites = [13805, 14058, 14110, 14121, 14103, 13738, 13704, 11258, 11259, 18267, 11936, 12177, 12438, 12488, 12911, 13220, 12973, 18743, 17719, 13191, 18219, 15965, 16073, 15862, 16030, 16141, 13703, 15282, 13608, 13667, 13777, 13798, 13841, 14130, 13488, 13604, 13468, 13939, 16662, 13781, 13864, 13921, 14012, 14020, 14106, 14120, 13476, 13507, 14080, 14156, 14175, 12366, 12455, 12457, 10485, 10903, 13019, 10539, 10603, 10825, 10973, 10520, 10808, 10890, 11976, 12492, 10777, 13023, 11970, 13156, 11094, 12881, 12905, 12904, 12929, 13028, 13030, 13050, 13165, 13074];

    public function patientSearch(Request $request) {
		$usertype = auth()->user()->user_type_id;
        $level = ($usertype == 8) ? auth()->user()->facility_id : auth()->user()->level;
    	$search = $request->input('search');
    	$mergeData = [];

    	$eidPatients = Patient::select('patients.id', 'patients.patient')
    					->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'patients.facility_id')
            			->whereRaw("(patients.patient like '%" . $search . "%')")
		    			->when($usertype, function($query) use ($usertype, $level){
		                    if ($usertype == 2 || $usertype == 3)
		                        return $query->where('view_facilitys.partner_id', '=', $level);
		                    if ($usertype == 4)
		                        return $query->where('view_facilitys.county_id', '=', $level);
		                    if ($usertype == 5)
		                        return $query->where('view_facilitys.subcounty_id', '=', $level);
		                    if ($usertype == 7)
		                        return $query->where('view_facilitys.partner_id', '=', $level);
                            if ($usertype == 8)
                                return $query->where('view_facilitys.id', '=', $level);
		                })->paginate(10);

		$vlPatients = Viralpatient::select('viralpatients.id', 'viralpatients.patient')
    					->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralpatients.facility_id')
            			->whereRaw("(viralpatients.patient like '%" . $search . "%')")
		    			->when($usertype, function($query) use ($usertype, $level){
		                    if ($usertype == 2 || $usertype == 3)
		                        return $query->where('view_facilitys.partner_id', '=', $level);
		                    if ($usertype == 4)
		                        return $query->where('view_facilitys.county_id', '=', $level);
		                    if ($usertype == 5)
		                        return $query->where('view_facilitys.subcounty_id', '=', $level);
		                    if ($usertype == 7)
		                        return $query->where('view_facilitys.partner_id', '=', $level);
                            if ($usertype == 8)
                                return $query->where('view_facilitys.id', '=', $level);
                            if ($usertype == 17)
                                return $query->whereIn('facilitycode',$this->dtg_2022_sites);
		                })->paginate(10);
		foreach ($eidPatients as $key => $patient) {
        	$mergeData[] = (object)[
        						'type' => 'EID',
        						'id' => $patient->id,
        						'patient' => $patient->patient
        					];
        }

        foreach ($vlPatients as $key => $patient) {
        	$mergeData[] = (object)[
        						'type' => 'VL',
        						'id' => $patient->id,
        						'patient' => $patient->patient
        					];
        }
        $eidPatients = json_decode(json_encode($eidPatients));
        $vlPatients = json_decode(json_encode($vlPatients));

        $from = max([$eidPatients->from, $vlPatients->from]);
        $to = max([$eidPatients->to, $vlPatients->to]);
        $total = max([$eidPatients->total, $vlPatients->total]);

        $returnData = [
                        'current_page' => $eidPatients->current_page ?? $vlPatients->current_page,
                        'data' => $mergeData,
                        'first_page_url' => $eidPatients->first_page_url ?? $vlPatients->first_page_url,
                        'from' => $from,
                        'last_page' => $eidPatients->last_page ?? $vlPatients->last_page,
                        'last_page_url' => $eidPatients->last_page_url ?? $vlPatients->last_page_url,
                        'next_page_url' => $eidPatients->next_page_url ?? $vlPatients->next_page_url,
                        'path' => $eidPatients->path ?? $vlPatients->path,
                        'per_page' => $eidPatients->per_page ?? $vlPatients->per_page,
                        'prev_page_url' => $eidPatients->prev_page_url ?? $vlPatients->prev_page_url,
                        'to' => $to,
                        'total' => $total
                    ];

        echo json_encode($returnData);
    }

    public function batchSearch(Request $request){
    	$usertype = auth()->user()->user_type_id;
        $level = ($usertype == 8) ? auth()->user()->facility_id : auth()->user()->level;
    	$search = $request->input('search');
        $mergeBatches = [];

    	$eidBatches = Batch::select('batches.id as id', 'batches.original_batch_id as batch_id', 'view_facilitys.facilitycode as code')
    			->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'batches.facility_id')
            	->whereRaw("(batches.original_batch_id like '%" . $search . "%')")
    			->when($usertype, function($query) use ($usertype, $level){
                    if ($usertype == 2 || $usertype == 3)
                        return $query->where('view_facilitys.partner_id', '=', $level);
                    if ($usertype == 4)
                        return $query->where('view_facilitys.county_id', '=', $level);
                    if ($usertype == 5)
                        return $query->where('view_facilitys.subcounty_id', '=', $level);
                    if ($usertype == 7)
                        return $query->where('view_facilitys.partner_id', '=', $level);
                    if ($usertype == 8)
                        return $query->where('view_facilitys.id', '=', $level);
                    if ($usertype == 17)
                        return $query->whereIn('view_facilitys.facilitycode', $this->dtg_2022_sites);
                })->paginate(10);

        $vlBatches = Viralbatch::select('viralbatches.id as id', 'viralbatches.original_batch_id as batch_id', 'view_facilitys.facilitycode as code')
             ->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralbatches.facility_id')
             ->whereRaw("(viralbatches.original_batch_id like '%" . $search . "%')")
             ->when($usertype, function($query) use ($usertype, $level){
                    if ($usertype == 2 || $usertype == 3)
                        return $query->where('view_facilitys.partner_id', '=', $level);
                    if ($usertype == 4)
                        return $query->where('view_facilitys.county_id', '=', $level);
                    if ($usertype == 5)
                        return $query->where('view_facilitys.subcounty_id', '=', $level);
                    if ($usertype == 7)
                        return $query->where('view_facilitys.partner_id', '=', $level);
                    if ($usertype == 8)
                        return $query->where('view_facilitys.id', '=', $level);
                    if ($usertype == 17)
                        return $query->whereIn('view_facilitys.facilitycode', $this->dtg_2022_sites);
                })->paginate(10);
        foreach ($eidBatches as $key => $value) {
            $mergeBatches[] = [
                             'type' => 'EID',
                             'id' => $value->id,
                             'name' => $value->batch_id,
                             'code' => $value->code
                         ];
        }

        foreach ($vlBatches as $key => $value) {
            $mergeBatches[] = [
                             'type' => 'VL',
                             'id' => $value->id,
                             'name' => $value->batch_id,
                             'code' => $value->code
                         ];
        }
        $eidBatches = json_decode(json_encode($eidBatches));
        $vlBatches = json_decode(json_encode($vlBatches));

        $from = max([$eidBatches->from, $vlBatches->from]);
        $to = max([$eidBatches->to, $vlBatches->to]);
        $total = max([$eidBatches->total, $vlBatches->total]);

        $returnData = [
                        'current_page' => $eidBatches->current_page ?? $vlBatches->current_page,
                        'data' => $mergeBatches,
                        'first_page_url' => $eidBatches->first_page_url ?? $vlBatches->first_page_url,
                        'from' => $from,
                        'last_page' => $eidBatches->last_page ?? $vlBatches->last_page,
                        'last_page_url' => $eidBatches->last_page_url ?? $vlBatches->last_page_url,
                        'next_page_url' => $eidBatches->next_page_url ?? $vlBatches->next_page_url,
                        'path' => $eidBatches->path ?? $vlBatches->path,
                        'per_page' => $eidBatches->per_page ?? $vlBatches->per_page,
                        'prev_page_url' => $eidBatches->prev_page_url ?? $vlBatches->prev_page_url,
                        'to' => $to,
                        'total' => $total
                    ];
        // return $mergeBatches;
    	// return $returnData;
        echo json_encode($returnData);
    }

    public function countySearch(Request $request)
    {
        $search = $request->input('search');
        $county = DB::table('countys')->select('id', 'name', 'letter as facilitycode')
            ->whereRaw("(name like '%" . $search . "%')")
            ->paginate(10);
        return $county;
    }

    public function facilitySearch(Request $request) {
    	$usertype = auth()->user()->user_type_id;
    	$level = auth()->user()->level;
    	$search = $request->input('search');

    	return ViewFacility::select('ID as id', 'name', 'facilitycode', 'county')
            	->whereRaw("(name like '%" . $search . "%' OR  facilitycode like '" . $search . "%')")
				->when($usertype, function($query) use ($usertype, $level){
                    if ($usertype == 2 || $usertype == 3)
                        return $query->where('partner_id', '=', $level);
                    if ($usertype == 4)
                        return $query->where('county_id', '=', $level);
                    if ($usertype == 5)
                        return $query->where('subcounty_id', '=', $level);
                    if ($usertype == 7)
                        return $query->where('partner_id', '=', $level);
                    if ($usertype == 17)
                        return $query->whereIn('facilitycode',$this->dtg_2022_sites);
                })->paginate(10);
    }

    public function patientresult($testtype,$patient) {
    	if (null !== session('searchParams'))
    		session(['searchParams'=>null]);
    	$testingSystem = strtolower($testtype);
    	if ($testingSystem == 'eid')
    		$patient = Patient::where('id', '=', $patient)->first();
    	if ($testingSystem == 'vl')
    		$patient = Viralpatient::where('id', '=', $patient)->first();

    	session(['searchParams'=>['patient_id'=>$patient->id]]);
    	return view('tables.searchresults', compact('testingSystem'))->with('pageTitle', "$testingSystem patient : $patient->patient");
    }

    public function batchresult($testtype,$batch) {
    	if (null !== session('searchParams'))
    		session(['searchParams'=>null]);
    	$testingSystem = strtolower($testtype);
    	if ($testingSystem == 'eid')
    		$batch = Batch::where('id', '=', $batch)->first();
    	if ($testingSystem == 'vl')
    		$batch = Viralbatch::where('id', '=', $batch)->first();

    	session(['searchParams'=>['batch_id'=>$batch->id]]);
    	return view('tables.searchresults', compact('testingSystem'))->with('pageTitle', "$testingSystem batch : $batch->original_batch_id");
    }

    public function facilityresult($facility) {
    	if (null !== session('searchParams'))
    		session(['searchParams'=>null]);
    	$facility = ViewFacility::where('id', '=', $facility)->first();
    	session(['searchParams'=>['facility_id'=>$facility->id]]);
    	return view('tables.searchresults')->with('pageTitle', "$facility->name");
    }

    public function print_individual($testSysm,$id) {
        $sampleid = intval($id);
        $testSysm = strtoupper($testSysm);
        if ($testSysm == 'VL') {
            $samples = Viralsample::join('viralbatches', 'viralbatches.id', '=', 'viralsamples.batch_id')->where('viralsamples.id', '=', $sampleid)->whereNotNull('viralbatches.datereceived')->first();
            $data = Lookup::get_viral_lookups();
            $relationships = ['patient', 'approver', 'batch.lab', 'batch.view_facility', 'batch.receiver', 'batch.creator'];
        } else if ($testSysm == 'EID') {
            $samples = Sample::join('batches', 'batches.id', '=', 'samples.batch_id')->where('samples.id', '=', $sampleid)->whereNotNull('batches.datereceived')->first();
            $data = Lookup::get_eid_lookups();
            $relationships = ['patient.mother', 'batch.lab', 'batch.view_facility', 'batch.receiver', 'batch.creator'];
        } else {
            return back();
        }
        $samples = $samples->load($relationships);
        $data['sample'] = $samples;
        $data['testingSys'] = $testSysm;

        // return view('reports.individualresult', $data);
        $facility = $samples->batch->view_facility->name;
        $datereceived = date('d-M-Y', strtotime($samples->datereceived));
        $fileName = $testSysm. " Individual Samples Report for $facility Received on $datereceived";
        // dd($data);
        $mpdf = new Mpdf();
        $view_data = view('reports.individualresult', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($fileName.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        // return view('reports.individualresult', $data);
    }

    public function print_batch_individual($testingSystem,$batch) {
        $testingSystem = strtoupper($testingSystem);
        if ($testingSystem == 'EID') {
            $data = Lookup::get_eid_lookups();
            $batch = Batch::with('sample')->where('id', '=', $batch)->first();
            $data['testingSys'] = 'EID';
            $relationships = ['patient.mother', 'batch.lab', 'batch.view_facility', 'batch.receiver', 'batch.creator'];
        } else if ($testingSystem == 'VL') {
            $data = Lookup::get_viral_lookups();
            $batch = Viralbatch::with('sample')->where('id', '=', $batch)->first();
            $data['testingSys'] = 'VL';
            $relationships = ['patient', 'approver', 'batch.lab', 'batch.view_facility', 'batch.receiver', 'batch.creator'];
        }
        $batch = $batch->load('view_facility');
        $samples = $batch->sample;
        $data['samples'] = $samples->load($relationships);
        // $data = (object)$data;
        $facility = $batch->view_facility->name;
        $datereceived = date('d-M-Y', strtotime($batch->datereceived));
        $fileName = $testingSystem. " Individual Samples Report for $facility Received on $datereceived";
        // return view('reports.individualbatch', $data);
        $mpdf = new Mpdf();
        $view_data = view('reports.individualbatch', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($fileName.'.pdf', \Mpdf\Output\Destination::DOWNLOAD);
    }

    public function print_batch_summary($testingSystem, $batch) {
        $testingSystem = strtoupper($testingSystem);
        if ($testingSystem == 'EID') {
            $data = Lookup::get_eid_lookups();
            $data['testingSys'] = 'EID';
            $data['batches'] = Batch::with(['sample.patient.mother', 'view_facility', 'lab', 'receiver', 'creator'])->where('id', '=', $batch)->get();
            $id = $data['batches']->first()->original_batch_id;
        } else if ($testingSystem == 'VL') {
            $data = Lookup::get_viral_lookups();
            $data['testingSys'] = 'VL';
            $data['batches'] = Viralbatch::with(['sample.patient', 'view_facility', 'lab', 'receiver', 'creator'])->where('id', '=', $batch)->get();
            $id = $data['batches']->first()->original_batch_id;
        }

        $mpdf = new Mpdf(['format' => 'A4-L']);
        $view_data = view('reports.summarybatch', $data)->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output("$testingSystem Batch $id summary.pdf", \Mpdf\Output\Destination::DOWNLOAD);
    }

    public function eidresults(Request $request) {
    	$recordsTotal = 0;
    	$recordsFiltered = 0;
    	$modelCount = null;
    	$model = self::results('eid', $modelCount, $recordsTotal);
    	$model = self::filter('eid',$model,$request,$modelCount,$recordsFiltered);
    	$model = self::order('eid',$model,$request);
    	$model = self::limit($model,$request);
    	$data = self::data_output('eid',$model,$request,$recordsTotal,$recordsFiltered);
    	echo json_encode($data);
    }

    public function vlresults(Request $request) {
    	$recordsTotal = 0;
    	$recordsFiltered = 0;
    	$modelCount = null;
    	$model = self::results('vl', $modelCount, $recordsTotal);
    	$model = self::filter('vl',$model,$request,$modelCount,$recordsFiltered);
    	$model = self::order('vl',$model,$request);
    	$model = self::limit($model,$request);
    	$data = self::data_output('vl',$model,$request,$recordsTotal,$recordsFiltered);
    	echo json_encode($data);
    }

    public static function results($testingSystem,&$modelCount, &$Total) {
    	$parameter = (object)session('searchParams');
    	if ($testingSystem == 'eid') {
    		$table = "sample_complete_view";
    		$model = SampleCompleteView::select('sample_complete_view.id','sample_complete_view.batch_id','sample_complete_view.original_batch_id','sample_complete_view.patient_id', 'sample_complete_view.patient','view_facilitys.name as facility', 'labs.name as lab','sample_complete_view.datecollected','sample_complete_view.datereceived','sample_complete_view.datedispatched','sample_complete_view.datetested','results.name as result','sample_complete_view.receivedstatus_name','rejectedreasons.name as rejectedreason')
    						->leftJoin('labs', 'labs.id', '=', 'sample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'sample_complete_view.result')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'sample_complete_view.rejectedreason');
    		$modelCount = SampleCompleteView::selectRaw("count(*) as totals")
    						->leftJoin('labs', 'labs.id', '=', 'sample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'sample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'sample_complete_view.result')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'sample_complete_view.rejectedreason');
    	} else if ($testingSystem == 'vl') {
    		$table = "viralsample_complete_view";
    		$model = ViralsampleCompleteView::select('viralsample_complete_view.id','viralsample_complete_view.batch_id','viralsample_complete_view.original_batch_id','viralsample_complete_view.patient_id', 'viralsample_complete_view.patient','view_facilitys.name as facility', 'labs.name as lab','viralsample_complete_view.datecollected','viralsample_complete_view.datereceived','viralsample_complete_view.datedispatched','viralsample_complete_view.datetested','viralsample_complete_view.result','viralsample_complete_view.units','viralsample_complete_view.interpretation','viralsample_complete_view.receivedstatus_name','rejectedreasons.name as rejectedreason')
    						->leftJoin('labs', 'labs.id', '=', 'viralsample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'viralsample_complete_view.rejectedreason');

    		$modelCount = ViralsampleCompleteView::selectRaw("count(*) as totals")
    						->leftJoin('labs', 'labs.id', '=', 'viralsample_complete_view.lab_id')
    						->leftJoin('view_facilitys', 'view_facilitys.id', '=', 'viralsample_complete_view.facility_id')
    						->leftJoin('results', 'results.id', '=', 'viralsample_complete_view.result')
    						->leftJoin('rejectedreasons', 'rejectedreasons.id', '=', 'viralsample_complete_view.rejectedreason');
    	}

    	$model = $model->when($parameter, function($query, $parameter) use ($table){
    						if(isset($parameter->facility_id))
    							return $query->where("$table.facility_id", '=', $parameter->facility_id);
    						if(isset($parameter->batch_id))
    							return $query->where("$table.batch_id", '=', $parameter->batch_id);
    						if(isset($parameter->patient_id))
    							return $query->where("$table.patient_id", '=', $parameter->patient_id);
    					})
    					->where("$table.repeatt", '=', 0)
    					->where("$table.flag", '=', 1);
    	$modelCount = $modelCount->when($parameter, function($query, $parameter) use ($table){
    						if(isset($parameter->facility_id))
    							return $query->where("$table.facility_id", '=', $parameter->facility_id);
    						if(isset($parameter->batch_id))
    							return $query->where("$table.batch_id", '=', $parameter->batch_id);
    						if(isset($parameter->patient_id))
    							return $query->where("$table.patient_id", '=', $parameter->patient_id);
    					})
    					->where("$table.repeatt", '=', 0)
    					->where("$table.flag", '=', 1);

    	$Total = $modelCount->get()->first()->totals;

    	return $model;
    }

    public static function data_output($testingSystem,$model,$request,$recordsTotal,$recordsFiltered){
    	$data = [];
    	$count = 1;
    	$dataSet = $model->get();
        $sessionData = (object)session('searchParams');

    	foreach ($dataSet as $key => $value) {
            $action = "<a href='". url("printindividualresult/$testingSystem/$value->id") ."'>
                        <img src='".asset('img/print.png')."' />&nbsp;Result</a>&nbsp;|&nbsp;
                    <a href='". url("printbatchsummary/$testingSystem/$value->batch_id") ."'>
                        <img src='".asset('img/print.png')."' />&nbsp;Summary</a>&nbsp;|&nbsp;
                    <a href='". url("printindividualbatch/$testingSystem/$value->batch_id") ."'>
                        <img src='".asset('img/print.png')."' />&nbsp;Batch-Individual</a>";
            if (auth()->user()->user_type_id == 3 || auth()->user()->user_type_id == 8) // Edit only available to partners and facility users 
                $action .= "&nbsp;|&nbsp;<a href='". url("sample/$testingSystem/$value->id/edit") ."'>Edit</a>";

            if ($testingSystem == 'eid') {
                if ($value->result == "Negative") {
                    $result = "<span class='label label-success'>$value->result</span>";
                } else {
                    $result = "<span class='label label-danger'>$value->result</span>";
                }
            } else if ($testingSystem == 'vl'){
                $copies = 'copies/ml';
                if (($value->result == '< LDL copies/ml') || (intval($value->result) < 1000)) {
                    $span = 'success';
                    if ($value->result == '< LDL copies/ml')
                        $copies = '';
                } else {
                    $span = 'danger';
                }
                $result = "<span class='label label-{$span}'>$value->result {$copies}</span>";
                // if ($value->result == '< LDL copies/ml') {
                //     $result = "<span class='label label-success'>$value->result</span>";
                // } else if (intval($value->result) < 1000) {
                //     $result = "<span class='label label-success'>$value->interpretation</span>";
                // } else {
                //     $result = "<span class='label label-danger'>$value->interpretation</span>";
                // }
            }
    		$data[] = [
    					$count, $value->patient,
    					$value->facility, $value->lab,
    					"<a href='". url("batchsearchresult/$testingSystem/$value->batch_id") ."'>".$value->original_batch_id."</a>",
                        $value->receivedstatus_name,
    					($value->datecollected) ? date('d-M-Y', strtotime($value->datecollected)) : '',
                        ($value->datereceived) ? date('d-M-Y', strtotime($value->datereceived)) : '',
                        ($value->datetested) ? date('d-M-Y', strtotime($value->datetested)) : '',
                        ($value->datedispatched) ? date('d-M-Y', strtotime($value->datedispatched)) : '',
    					$result, $action
    				];
    		$count++;
    	}

    	return array(
					"draw"            => isset ( $request['draw'] ) ?
						intval( $request['draw'] ) :
						0,
					"recordsTotal"    => intval( $recordsTotal ),
					"recordsFiltered" => intval( $recordsFiltered ),
					"data"            => $data
				);
    }

    public static function limit($model,$request) {
    	$offset = (int) $request['start'];
    	$limit = (int) $request['length'];

    	if ( isset($offset) && $limit != -1 ) {
    		$model = $model->offset($offset)->limit($limit);
		}

		return $model;
    }

    public static function order($testingSystem,$model,$request) {
    	if ($testingSystem == 'eid')
    		$table = "sample_complete_view";
    	if ($testingSystem == 'vl')
    		$table = "viralsample_complete_view";

    	$order = $request['order'] ?? null;
    	$dbcolumns = self::$columns;
		$dtColumns = self::pluck($dbcolumns,'dt');

    	if (isset($order) && count($order)) {
    		foreach ($order as $key => $value) {
    			$columnIdx = array_search( $value['column'], $dtColumns );
    			$dbcolumn = $dbcolumns[ $columnIdx ];
    			$column = $dbcolumn['db'];
    			$direction = $value['dir'];
    			$model = $model->orderBy("$table.$column",$direction);
    		}
    	}
    	return $model;
    }

    public static function filter($testingSystem,$model,$request,$modelCount,&$Total) {
    	$dbcolumns = self::$columns;
    	$requestColumns = $request['columns'];
    	$search = $request['search'] ?? null;
    	$searchstr = $search['value'] ?? null;
    	$dtColumns = self::pluck($dbcolumns,'dt');

		if ($testingSystem == 'eid')
    		$table = "sample_complete_view";
    	if ($testingSystem == 'vl')
    		$table = "viralsample_complete_view";

    	if (isset($search) && $search['value'] != '') {
    		$str = "%$searchstr%";
    		foreach ($requestColumns as $key => $value) {
    			$columnIdx = array_search( $value['data'], $dtColumns );
    			$dbcolumn = $dbcolumns[ $columnIdx ];
				$dbcol = [];
				if ($value['searchable'] == 'true'){
					$searchable = false;
					$searchable = in_array($value['data'], $dtColumns);
					if ($searchable) {
						$columnIdx = array_search( $value['data'], $dtColumns );
						$column = $dbcolumns[$columnIdx];
						$column = $column['db'];
						if ($column == 'facility'){
							$table = "view_facilitys";
							$column = "name";
						}
						if ($column == 'lab') {
							$table = "labs";
							$column = "name";
						}
						if($key == 1) {
							$model = $model->where("$table.$column", 'like', $str);
							$modelCount = $modelCount->where("$table.$column", 'like', $str);
						} else {
							$model = $model->orWhere("$table.$column", 'like', $str);
							$modelCount = $modelCount->orWhere("$table.$column", 'like', $str);
						}
						if ($testingSystem == 'eid')
				    		$table = "sample_complete_view";
				    	if ($testingSystem == 'vl')
				    		$table = "viralsample_complete_view";
					}
				}
    		}
    	}

    	$Total = $modelCount->get()->first()->totals;

    	return $model;
    }

    static function pluck ( $a, $prop )
	{
		$out = array();

		for ( $i=0, $len=count($a) ; $i<$len ; $i++ ) {
			$out[] = $a[$i][$prop];
		}

		return $out;
	}

    static function getEIDResultName($param=null){
        $results = Cache::rememberForever('results', function () {
            return DB::table('results')->get();
        });
        foreach ($results as $key => $value) {
            if($value->id == $param)
                return $value->name;
        }
    }

    static function rejected_reason($param=null, $resultType) {
        $rejectedReasons = Cache::rememberForever($resultType.'rejectedreasons', function ($resultType) {
            if($resultType == 'VL')
                return DB::table('viralrejectedreasons')->get();
            if($resultType == 'EID')
                return DB::table('rejectedreasons')->get();
        });
        foreach ($rejectedReasons as $key => $value) {
            if($value->id == $param)
                return $value->name;
        }
    }
}
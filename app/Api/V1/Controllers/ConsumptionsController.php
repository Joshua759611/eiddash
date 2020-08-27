<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Api\V1\Requests\BlankRequest;
use App\Api\V1\Requests\CommodityRequest;
use App\Api\V1\Requests\CovidConsumptionRequest;
use App\Api\V1\Requests\CovidConsumptionPullRequest;

use App\Consumption;
use App\ConsumptionDetail;
use App\ConsumptionDetailBreakdown;
use App\CovidConsumption;
use App\CovidConsumptionDetail;
use App\CovidKit;
use App\Kits;
use App\Machine;
use DB;
/**
 * 
 */
class ConsumptionsController extends Controller
{

	private $api_machines;
	private $machine_check = ['taqman' => 'taqman', 'abbott' => 'realtime'];
	private $testtypes = [['id' => 1, 'testtype' => 'EID'], ['id' => 2, 'testtype' => 'VL']];
	private $generalAddings = ['opening','consumed', 'qty_received','wasted','issued_out','issued_in','closing','requested'];

	public function __construct(){
		$this->api_machines = \App\Machine::get()->transform(function ($machine, $key) {
							    $machine->machine = strtolower($machine->machine);
							    return $machine;
							});
	}

	public function create(BlankRequest $request) {
		$consumptions_array = [];
		$consumptions = json_decode($request->input('consumptions'));
		foreach ($consumptions as $key => $consumption) {
			$existing = Consumption::existing($consumption->year, $consumption->month, $consumption->testtype, $consumption->kit_id)->first();
			if ($existing){
				$consumptions_array[] = ['original_id' => $consumption->id, 'national_id' => $existing->id ];
				continue;
			}

			// New consumption to be saved
			$saveconsumption = new Consumption();
			$consumptions_data = get_object_vars($consumption);
            $saveconsumption->fill($consumptions_data);
            $saveconsumption->original_id = $consumption->id;
            $saveconsumption->synched = 1;
            $saveconsumption->datesynched = date('Y-m-d');

            // Unset the ID so that it auto-increments and the national id because it does not exist at national
            unset($saveconsumption->id);
            unset($saveconsumption->national_id);
            $saveconsumption->save();
            $consumptions_array[] = ['original_id' => $saveconsumption->original_id, 'national_id' => $saveconsumption->id ];
		}
		return response()->json([
            'status' => 'ok',
            'consumptions' => $consumptions_array,
        ], 201);
	}

	public function api_create(CommodityRequest $request) {
		$r = $this->dump_log('consumption_api');
		if (isset($r))
			$request = $r;
		// print_r($r);die();
		$testtype = null;
		$response = [
				'message' => 'Consumption Data save failed',
				'status' => 406,
			];
		$machine = $this->getMachine($request, $testtype);
		if ($machine->isEmpty())
			return response()->json([
				'status' => 403,
				'error' => 'Forbidden action',
				'message' => 'Machine provided does not exist'
			]);
		foreach ($machine as $key => $value) {
			$machine = $value;
		}

		if($testtype->isEmpty())
			return response()->json([
				'status' => 403,
				'error' => 'Forbidden action',
				'message' => 'Test type provided does not exist'
			]);
		foreach ($testtype->toArray() as $key => $value) {
			$testtype = $value;
		}
		
		$consumption = $this->saveAPIConsumption($machine, $testtype, $request);
		if(null !== $consumption) 
			$response = [
				'message' => 'Consumption Data saved successfully to '.session('lab')->name,
				'status' => 201,
			];
		return response()->json($response);
	}

	public function covid_api_create(CovidConsumptionRequest $request)
	{
		// Presence of the parameters will be checked at the request class
		$platforms = $request->input('platforms');
		$insertData = [];
		foreach ($platforms as $key => $platform) {
			$machine = Machine::where('machine', 'like', $platform['name'])->get();
			if (!$machine->isEmpty()) {
				$data = $this->covid_get_values($machine->first(), $platform);
				if (!$data)
					return response()->json([
							'error' => true,
							'message' => 'Bad Request. The values provided indicate a negative ending balance. Please ensure that the values give a positive ending balance'
						], 400);
				$insertData[$key] = [
						'machine' => $machine->first()->machine,
						'tests' => $platform['tests'],
					];
			}
			$insertData[$key]['details'] = $data;

		}
		
		try {
			$existing = CovidConsumption::where('start_of_week', date('Y-m-d', strtotime($request->input('start_of_week'))))->where('end_of_week', date('Y-m-d', strtotime($request->input('end_of_week'))))->where('lab_id', session('lab')->id)->get();
			if ($existing->isEmpty()){
				$consumption = new CovidConsumption;
			} else {
				$consumption = $existing->first();
				foreach ($consumption->details as $key => $detail) {
					$detail->delete();
				}
			}

			$consumption->start_of_week = date('Y-m-d', strtotime($request->input('start_of_week')));
			$consumption->end_of_week = date('Y-m-d', strtotime($request->input('end_of_week')));
			$consumption->week = date('W', strtotime($request->input('start_of_week')));
			$consumption->lab_id = session('lab')->id;
			if (env('APP_ENV') == 'local' || env('APP_ENV') == 'development')
				$consumption->deleted_at = date('Y-m-d H:i:s');
			$consumption->save();
			$tests = [];
			foreach ($insertData as $key => $data) {
				if (isset($data['machine']))
					$tests[$data['machine']] = $data['tests'];
				
				foreach ($data['details'] as $key => $detail) {
					$details = new CovidConsumptionDetail;
					$details->fill($detail);
					$details->consumption_id = $consumption->id;
					$details->save();
				}
			}
			$consumption->tests = json_encode($tests);
			$consumption->synced = 1;
			$consumption->datesynced = date('Y-m-d');
			$consumption->save();
		} catch (Exception $e) {
			return response()->json([
						'error' => true,
						'message' => $e
					], 500);
		}
		
		return response()->json($consumption->load('details'), 200);
	}

	private function covid_get_values($machine, $platform_data)
	{
		$kits = $machine->covid_kits;
		$data = [];
		foreach ($kits as $key => $kit) {
			$begining_balance = $platform_data['begining_balance'];
			$received = $platform_data['received'];
			$used = $platform_data['used'];
			$positive_adjustment = $platform_data['positive_adjustment'];
			$negative_adjustment = $platform_data['negative_adjustment'];
			$wastage = $platform_data['wastage'];
			$requested = $platform_data['requested'];
			$ending = (((float)$begining_balance[$kit->material_no]+(float)$received[$kit->material_no]+(float)$positive_adjustment[$kit->material_no]) - ((float)$used[$kit->material_no]+(float)$negative_adjustment[$kit->material_no]+(float)$wastage[$kit->material_no]));
			if ($ending < 0)
				return false;
			$data[$kit->id] = [
					'kit_id' => $kit->id,
					'begining_balance' => $begining_balance[$kit->material_no],
					'received' => $received[$kit->material_no],
					'kits_used' => $used[$kit->material_no],
					'positive' => $positive_adjustment[$kit->material_no],					
					'negative' => $negative_adjustment[$kit->material_no],
					'wastage' => $wastage[$kit->material_no],
					'ending' => $ending,
					'requested' => $requested[$kit->material_no],
				];
		}
		
		return $data;
	}

	public function create_covid(BlankRequest $request)
	{
		$consumptions = json_decode($request->input('consumptions'));
		// $consumptions = $request->all();
		// return response()->json($consumptions);
		$consumptions_array = [];
		foreach ($consumptions as $key => $consumption) {
			$consumption = (object) $consumption;
			$existing = CovidConsumption::existing($consumption->start_of_week, $consumption->lab_id)->first();
			if ($existing){
				$consumptions_array[] = ['original_id' => $consumption->id, 'national_id' => $existing->id ];
				continue;
			}
						
			DB::beginTransaction();
			try
			{
				// Inserting the covid consumptions
				$db_consumption = new CovidConsumption;
				$consumptions_data = get_object_vars($consumption);
				$db_consumption->fill($consumptions_data);
				$db_consumption->original_id = $consumption->id;
				$db_consumption->synced = 1;
				$db_consumption->datesynced = date('Y-m-d');
				unset($db_consumption->id);
				unset($db_consumption->details);
				$db_consumption->save();

				// Inserting the covid details
				foreach ($consumption->details as $key => $detail) {
					$detail = (object)$detail;
					if (null !== $detail->kit) {
						$detailKit = (object)$detail->kit;
						$kit = CovidKit::where('material_no', $detailKit->material_no)->first();
						$db_detail = new CovidConsumptionDetail;
						$detail_data = get_object_vars($detail);
						$db_detail->fill($detail_data);
						$db_detail->consumption_id = $db_consumption->id;
						$db_detail->kit_id = $kit->material_no;
						$db_detail->original_id = $detail->id;
						$db_detail->synced = 1;
						$db_detail->datesynced = date('Y-m-d');
						unset($db_detail->id);
						unset($db_detail->kit);
						$save = $db_detail->save();
					}
				}
				DB::commit();				
				$consumptions_array[] = ['original_id' => $db_consumption->original_id, 'national_id' => $db_consumption->id ];
			} catch (Exception $e) {
				DB::rollback();
				return response()->json([
						'error' => true,
						'message' => 'Insert failed: Unexpected error occured while inserting lab' . json_decode($request->input('lab')) . ' data.',
						'code' => 500,
						'detailed' => $e
					], 500);
			}
		}
		return response()->json($consumptions_array);
	}

	public function getCovidConsumptions(CovidConsumptionPullRequest $request)
	{
		$consumptions = CovidConsumption::when($request, function ($query) use ($request){
													if ($request->has('start_of_week'))
														return $query->whereDate('start_of_week', $request->input('start_of_week'));
												})->get();
		
		$data = [];													
		foreach ($consumptions as $conskey => $consumption) {
			$data[$conskey] = [
					'consumption' => $consumption->id,
					'labid' => $consumption->lab->id ?? '',
					'labname' => $consumption->lab->name ?? '',
					'start_of_week' => $consumption->start_of_week,
					'end_of_week' => $consumption->end_of_week,
					'week' => $consumption->week,
					'tests' => json_decode($consumption->tests)
				];
			foreach ($consumption->details as $key => $detail) {
				$data[$conskey]['details'][] = [
								'material_no' => $detail->kit->material_no ?? '',
								'product_description' => $detail->kit->product_description ?? '',
								'begining_balance' => $detail->begining_balance,
								'received' => $detail->received,
								'used' => $detail->kits_used,
								'positive' => $detail->positive,
								'negative' => $detail->negative,
								'wastage' => $detail->wastage,
								'ending' => $detail->ending,
								'requested' => $detail->requested,
							];
			}
		}
		return response()->json([
						'consumptions' => $data
					], 200);
	}

	private function saveAPIConsumption($machine, $testtype, $request) {
		$response = false;
		$date = explode(" ", $request->month_end_date);
		$date = str_replace('/', '-', $date);
		$date = explode("-", $date[0]);
		if (empty($date))
			return null;
		$existing = Consumption::existing($date[2], $date[1], session('lab')->id)
						->when((strpos(env('APP_URL'), "lab-2.test.nascop.org")), function($query){
							return $query->where('test', 1);
						})->get();
		if ($existing->isEmpty()) {
			$consumption = new Consumption;
			$consumption->year = $date[2];
            $consumption->month = $date[1];
            $consumption->submittedby = $request->reported_by;
            $consumption->datesubmitted = date('Y-m-d');
            $consumption->lab_id = session('lab')->id;
			if (strpos(env('APP_URL'), "lab-2.test.nascop.org"))
            	$consumption->test = 1;
            $consumption->apisave();
		} else {
			$consumption = $existing->first();
		}
		
        $details = $this->saveAPIConsumptionDetails($consumption, $testtype, $machine, $request);
		if ($details)
			$response = true;
		return $response;
	}

	private function saveAPIConsumptionDetails($consumption, $testtype, $machine, $request) {
		$response = false;
		$testtype = (object) $testtype;
		$existing = ConsumptionDetail::existing($consumption->id, $testtype->id, $machine->id)->get();
		if ($existing->isEmpty()){
			$consumption_details = new ConsumptionDetail;
			$consumption_details->consumption_id = $consumption->id;
			$consumption_details->testtype = $testtype->id;
			$consumption_details->machine_id = $machine->id;
			$consumption_details->apisave();
		} else
			$consumption_details = $existing->first();

		$details_breakdown = $this->saveAPIConsumptionDetailsBreakdown($consumption_details, $machine, $request, $testtype);
		if ($details_breakdown)
			$response = true;
		return $response;
	}

	private function saveAPIConsumptionDetailsBreakdown($details, $machine, $request, $testtype) {
		$response = false;
		$testtypename = $testtype->testtype;
		$kits = Kits::where('machine_id', '=', $machine->id)->get();
		$qualkit = 0;
		foreach ($kits as $key => $kit) {
			$existing = ConsumptionDetailBreakdown::existing($details->id, $kit->id, \App\Kits::class)->get();
			if ($existing->isEmpty()){
				$factor = json_decode($kit->factor);
				$test_factor = json_decode($kit->testFactor);
				if (is_object($factor))
					$factor = $factor->$testtypename;
				if (is_object($test_factor))
					$test_factor = $test_factor->$testtypename;
				$breakdown = new ConsumptionDetailBreakdown;
				$breakdown->consumption_details_id = $details->id;
				$breakdown->consumption_breakdown_id = $kit->id;
				$breakdown->consumption_breakdown_type = \App\Kits::class;
				foreach ($this->generalAddings as $keyAddings => $value) {
					// Consumed are calculated from the test count
					if ($value == 'consumed'){
						if ($kit->alias == 'qualkit'){
							$qualkit = ($request->sample_count/$test_factor);
						}
						$breakdown->$value = ($factor * $qualkit);
					} else {
						$breakdown->$value = ($factor * (int)$this->getcomputedkitvalue($value, $request));
					}

				}
				$breakdown->apisave();
			} else 
				$breakdown = $existing->first();
		}
		if ($breakdown)
			$response = true;
		return $response;
	}

	private function getcomputedkitvalue($adding, $request) {
		$return_value = 0;
		if ($adding == 'opening')
			$return_value = $request->opening_balance ?? 0;
		if ($adding == 'wasted')
			$return_value = $request->loss ?? 0;
		if ($adding == 'issued_out')
			$return_value = $request->neg_adjust ?? 0;
		if ($adding == 'issued_in')
			$return_value = $request->pos_adjust ?? 0;
		if ($adding == 'closing')
			$return_value = $request->closing_balance ?? 0;
		if ($adding == 'requested')
			$return_value = $request->qty_requested ?? $request->gty_requested;
		if ($adding == 'qty_received')
			$return_value = $request->qty_received ?? 0;
		return $return_value;
	}

	private function getMachine($request, &$testtype) {
		$commodity = strtolower($request->commodity_name);
		$machine_type = '';
		$name = explode(" ", $commodity);
		foreach ($this->machine_check as $key => $machine_check) {
			if (in_array($machine_check, $name))
				$machine_type = $key;
			continue;
		}
		$request_testtype = '';

		if (in_array('qualitative', $name))
			$request_testtype = 'eid';
		else if (in_array('quantitative', $name))
			$request_testtype = 'vl';

		$testtype = collect($this->testtypes)->where('testtype', strtoupper($request_testtype));
		return $this->api_machines->where('machine', $machine_type)->flatten(2);
	}
}
?>
	


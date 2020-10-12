<?php

namespace App;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\TestMail;
use App\Mail\CustomMail;
use GuzzleHttp\Client;
use Carbon\Carbon;
use DB;

class Common
{
	// public static $sms_url = 'http://sms.southwell.io/api/v1/messages';
	// public static $sms_url = 'http://sms.southwell.io/api/v1/messages';
    public static $sms_url = 'https://api.vaspro.co.ke/v3/BulkSMS/api/create';
    // public static $sms_url = 'https://mysms.celcomafrica.com/api/services/sendsms/';
    public static $sms_callback = 'http://vaspro.co.ke/dlr';
    // public static $mlab_url = 'http://197.248.10.20:3001/api/results/results';
    public static $mlab_url = 'https://api.mhealthkenya.co.ke/api/vl_results';


    public static function test_email()
    {
        Mail::to(['joelkith@gmail.com'])->send(new TestMail());
    }

    public static function custom_mail()
    {
        Mail::to(['joelkith@gmail.com'])->send(new CustomMail());
    }
    
	public static function csv_download($data, $file_name='page-data-export', $first_row=true, $save_file=false)
	{
		if(!$data) return;

		if($save_file){
			$fp = fopen(storage_path("exports/{$file_name}.csv"), 'w');
		}else{
			header('Content-Description: File Transfer');
			header('Content-Type: application/csv');
			header("Content-Disposition: attachment; filename={$file_name}.csv");
			// header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			
			$fp = fopen('php://output', 'w');			
		}
		// ob_clean();

		if($first_row){

			$first = [];

			foreach ($data[0] as $key => $value) {
				$first[] = $key;
			}
			fputcsv($fp, $first);

		}

		foreach ($data as $key => $value) {
			fputcsv($fp, $value);
		}
		// ob_flush();
		fclose($fp);
	}


	public static function get_days($start, $finish, $with_holidays=true)
	{
		if(!$start || !$finish) return null;
		// $workingdays= self::working_days($start, $finish);
		$s = Carbon::parse($start);
		$f = Carbon::parse($finish);
		$totaldays = $s->diffInWeekdays($f);

		if($totaldays < 0) return null;
		
		$start_time = strtotime($start);
		$month = (int) date('m', $start_time);
		$holidays = self::get_holidays($month);

		if($with_holidays) $totaldays -= $holidays;
		if ($totaldays < 1)		$totaldays=1;
		return $totaldays;
	}

	public static function working_days($startDate,$endDate){

	    //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
	    //We add one to inlude both dates in the interval.
	    $days = (strtotime($endDate) - strtotime($startDate)) / 86400 + 1;

	    $no_full_weeks = floor($days / 7);

	    $no_remaining_days = fmod($days, 7);

	    //It will return 1 if it's Monday,.. ,7 for Sunday
	    $the_first_day_of_week = date("N",strtotime($startDate));

	    $the_last_day_of_week = date("N",strtotime($endDate));
	    // echo              $the_last_day_of_week;
	    //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
	    //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
	    if ($the_first_day_of_week <= $the_last_day_of_week){
	        if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
	        if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
	    }

	    else{
	        if ($the_first_day_of_week <= 6) {
	        //In the case when the interval falls in two weeks, there will be a Sunday for sure
	            $no_remaining_days--;
	        }
	    }

	    //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
		//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
	   	$workingDays = $no_full_weeks * 5;
	    if ($no_remaining_days > 0 )
	    {
	      $workingDays += $no_remaining_days;
	    }

	    //We subtract the holidays
		/*    foreach($holidays as $holiday){
	        $time_stamp=strtotime($holiday);
	        //If the holiday doesn't fall in weekend
	        if (strtotime($startDate) <= $time_stamp && $time_stamp <= strtotime($endDate) && date("N",$time_stamp) != 6 && date("N",$time_stamp) != 7)
	            $workingDays--;
	    }*/

	    return $workingDays;
	}

    public static function get_holidays($month)
	{
		switch ($month) {
			case 0:
				$totalholidays=10;
				break;
			case 1:
				$totalholidays=1;
				break;
			case 4:
				$totalholidays=2;
				break;
			case 5:
				$totalholidays=1;
				break;
			case 6:
				$totalholidays=1;
				break;
			case 8:
				$totalholidays=1;
				break;
			case 10:
				$totalholidays=1;
				break;
			case 12:
				$totalholidays=3;
				break;
			default:
				$totalholidays=0;
				break;
		}
		return $totalholidays;
	}

	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public static function save_tat($view_model, $sample_model, $batch_id = NULL)
	{
		$samples = $view_model::where(['synched' => 2])
		->when($batch_id, function($query) use ($batch_id){
			return $query->where(['batch_id' => $batch_id]);
		})
		->get();

		foreach ($samples as $key => $sample) {
			$tat1 = self::get_days($sample->datecollected, $sample->datereceived);
			$tat2 = self::get_days($sample->datereceived, $sample->datetested);
			$tat3 = self::get_days($sample->datetested, $sample->datedispatched);
			$tat4 = self::get_days($sample->datecollected, $sample->datedispatched);
			// $tat4 = $tat1 + $tat2 + $tat3;
			$data = ['tat1' => $tat1, 'tat2' => $tat2, 'tat3' => $tat3, 'tat4' => $tat4];

			if($sample_model == "App\\Viralsample"){
				$viral_data = [
					'age_category' => self::set_age_cat($sample->age),
				];
				// $viral_data = array_merge($viral_data, $this->set_rcategory($sample->result, $sample->repeatt));
				$data = array_merge($data, $viral_data);
				if($sample->synched == 1 || $sample->synched == 0) $data['synched'] = 2;				
			}
			$sample_model::where('id', $sample->id)->update($data);
		}
	}



	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public function compute_tat($view_model, $sample_model)
	{
        ini_set("memory_limit", "-1");
        $offset_value = 0;
        while(true){

			$samples = $view_model::where(['batch_complete' => 1])
			->limit(5000)->offset($offset_value)
			->get();
			if($samples->isEmpty()) break;

			foreach ($samples as $key => $sample) {
				$tat1 = self::get_days($sample->datecollected, $sample->datereceived);
				$tat2 = self::get_days($sample->datereceived, $sample->datetested);
				$tat3 = self::get_days($sample->datetested, $sample->datedispatched);
				$tat4 = self::get_days($sample->datecollected, $sample->datedispatched);
				// $tat4 = $tat1 + $tat2 + $tat3;
				$data = ['tat1' => $tat1, 'tat2' => $tat2, 'tat3' => $tat3, 'tat4' => $tat4];

				if($sample_model == "App\\Viralsample"){
					$viral_data = [
						'age_category' => $this->set_age_cat($sample->age),
					];
					// $viral_data = array_merge($viral_data, $this->set_rcategory($sample->result, $sample->repeatt));	
					$data = array_merge($data, $viral_data);				
				}
				$sample_model::where('id', $sample->id)->update($data);
			}
	        $offset_value += 5000;
			echo "Completed clean at {$offset_value} " . date('d/m/Y h:i:s a', time()). "\n";
        }
	}



	// $view_model will be \App\SampleView::class || \App\ViralsampleView::class
	// $sample_model will be \App\Sample::class || \App\Viralsample::class
	public function compute_tat_sample($view_model, $sample_model, $sample_id=null)
	{
        ini_set("memory_limit", "-1");
        $offset_value = 0;

        $sample = $view_model::find($sample_id);

		$tat1 = self::get_days($sample->datecollected, $sample->datereceived);
		$tat2 = self::get_days($sample->datereceived, $sample->datetested);
		$tat3 = self::get_days($sample->datetested, $sample->datedispatched);
		$tat4 = self::get_days($sample->datecollected, $sample->datedispatched);
		// $tat4 = $tat1 + $tat2 + $tat3;
		$data = ['tat1' => $tat1, 'tat2' => $tat2, 'tat3' => $tat3, 'tat4' => $tat4];

		if($sample_model == "App\\Viralsample"){
			$viral_data = [
				'age_category' => $this->set_age_cat($sample->age),
			];
			$viral_data = array_merge($viral_data, $this->set_rcategory($sample->result, $sample->repeatt));	
			$data = array_merge($data, $viral_data);				
		}
		$sample_model::where('id', $sample->id)->update($data);

		dd($data);
	}

	public static function set_age($type)
	{
		ini_set('memory_limit', -1);
		if($type == 'eid'){
			$view_model = \App\SampleView::class;
			$sample_model = \App\Sample::class;
			$dob_function = 'calculate_age';
		}else{
			$view_model = \App\ViralsampleView::class;
			$sample_model = \App\Viralsample::class;
			$dob_function = 'calculate_viralage';
		}
		$samples = $view_model::whereNotNull('dob')->where(['age' => 0])->where('datetested', '>', '2017-01-01')->get();

		foreach ($samples as $key => $sample) {
			$age = \App\Lookup::$dob_function($sample->datecollected, $sample->dob);
			$update_array = ['age' => $age];
			if($type == 'vl'){
				$age_category = self::set_age_cat($age);
				$update_array = array_merge($update_array, ['age_category' => $age_category]);
			}
			$sample_model::where(['id' => $sample->id])->update($update_array);
		}
	}

    public static function set_age_cat($age = null)
    {
        if($age > 0.00001 && $age < 2) return 6; 
        else if($age >= 2 && $age < 10) return 7; 
        else if($age >= 10 && $age < 15) return 8; 
        else if($age >= 15 && $age < 20) return 9; 
        else if($age >= 19 && $age < 25) return 10;
        else if($age >= 25) return 11;
        else{ return 0; }
    }

    public static function add_missing_facilities()
    {
    	$facilities = \App\Facility::whereRaw("id NOT IN (select id from apidb.facilitys) ")->get();
    	// dd($facilities);

    	foreach ($facilities as $fac) {
	        $fac_array = $fac->toArray();
	        unset($fac_array['poc']);
	        unset($fac_array['has_gene']);
	        unset($fac_array['has_alere']);
	        unset($fac_array['eid_lab_id']);
	        unset($fac_array['vl_lab_id']);

	        DB::table("apidb.facilitys")->insert($fac_array);
    	}
    }

    public static function facility_lab()
    {
		ini_set('memory_limit', -1);
    	$facilities = \App\Facility::all();

    	$min_date = date('Y-m-d', strtotime("-1 year -6 months"));

    	foreach ($facilities as $key => $facility) {
    		$eid = \App\Batch::selectRaw("lab_id, count(id) as my_count")->where(['facility_id' => $facility->id])->where('datereceived', '>', $min_date)->groupBy('lab_id')->orderBy('my_count', 'desc')->first()->lab_id ?? 0;
    		$vl = \App\Viralbatch::selectRaw("lab_id, count(id) as my_count")->where(['facility_id' => $facility->id])->where('datereceived', '>', $min_date)->groupBy('lab_id')->orderBy('my_count', 'desc')->first()->lab_id ?? 0;

    		if($eid > 10) $eid = 11;
    		if($vl > 10) $vl = 11;

    		$facility->eid_lab_id = $eid;
    		$facility->vl_lab_id = $vl;
    		$facility->save();
    	}
    }

	public static function sms($recepient, $message)
	{
		/*$response = $client->request('post', '', [
			// 'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
			'http_errors' => false,
			'json' => [
				// 'sender' => env('SMS_SENDER_ID'),
                'apiKey' => env('SMS_KEY'),
                'shortCode' => env('SMS_SENDER_ID'),
				'recipient' => $recepient,
				'message' => $message,
                'callbackURL' => self::$sms_callback,
                'enqueue' => 0,
			],
		]);*/

		/****** CELCO *******/
		$client = new Client(['base_uri' => env('SMS_CELCO_URL')]);
		$response = $client->request('post', '', [
			// 'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
			'http_errors' => false,
			// 'debug' => true,
			'json' => [
                'apikey' => env('SMS_KEY'),
                'shortcode' => env('SMS_SENDER_ID'),
                'partnerID' => env('SMS_PARTNER_ID'),
				'mobile' => $recepient,
				'message' => $message,
			],
		]);
		$body = json_decode($response->getBody());
		print_r($body);

		if($response->getStatusCode() > 399) {
        	return false;
        } else if($response->getStatusCode() == 200){
        	if (null !== $body->{"respose-code"}){
        		if ($body->{"respose-code"} == 1006)
        			return false;
        	}
        	if (null !== $body->responses && $body->responses[0]->{"response-code"} == 200){
        		return true;	
        	}
        	return false;
        }
        else{
        	// die();
        	echo "Status Code is " . $response->getStatusCode();
        	echo $response->getBody();
        	return false;
        }
        /****** CELCO *******/


		/******* New SOUTHWELL *******/
// 		$client = new Client(['base_uri' => env("SMS_SOUTHWELL_URL")]);
// 		$response = $client->request('post', '', [
// 			// 'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
// 			'http_errors' => false,
// 			'debug' => true,
// 			'json' => [
//                 'apiKey' => env('SMS_KEY_SOUTHWELL'),
//                 'shortCode' => env('SMS_SENDER_ID_SOUTHWELL'),
//                 'recipient' => $recepient,
// 				'message' => $message,
// 				"callbackURL" => "",
// 				"enqueue" => 0,
// 			],
// 		]);

// // 		"apiKey": "4c5373c0eab01c79f0de1d183db0ee70",
// // "shortCode": "VL_EIDKenya",
// // "message": " Message Here",
// // "recipient": "254725227833 ",
// // "callbackURL": "",
// // "enqueue":0


// 		$body = json_decode($response->getBody());
// 		print_r($body);
// 		if($response->getStatusCode() > 399) {
// 			return false;
// 		} else {
// 			if ($body->code == "Success") {
// 				// Log::channel('shortcode')->info(json_encode($body));
// 				if ($body->data->data->sms_reporting->failed == 1){
// 					// Mail::to(['baksajoshua09@gmail.com', 'tngugi@gmail.com'])->send(new TestMail(null, "Southwell SMS not going through for the reason " . $body->data->data->remaining_balance));
// 					return false;
// 				} else {
// 					return true;
// 				}
// 			} else {
// 				Log::channel('shortcode_error')->info(json_encode($body));
// 				// $data = (object)['body' => json_encode($body)];
// 				// Mail::to(['baksajoshua09@gmail.com', 'tngugi@gmail.com'])->send(new TestMail(null, "Southwell SMS not going through for the reason " . $data));
// 			}
// 		}
// 		return false;
        /******* New SOUTHWELL *******/

	}

	public static function test_sms()
	{
		self::sms("254725455925", "This is a test email sent at ". date('Y-m-d H:i:s'));
	}

}

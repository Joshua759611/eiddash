<?php

namespace App;

use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Client;
use DB;

class Dhis 
{
	public static $base = 'https://hiskenya.org/api/28/dataValueSets';
	public static $kmhfl_base = 'http://api.kmhfltest.health.go.ke/';
	// public static $base = 'https://test.hiskenya.org/dhiske/';

	public static function send_yearly_data()
	{
		for ($months=1; $months < 12; $months++) { 
			$y = date('Y', strtotime("-{$months} month"));
			if($y < date('Y')) break;

			self::send_data($months);
		}
	}

	public static function send_data($months=null)
	{		
		ini_set('memory_limit', '-1');
        $client = new Client(['base_uri' => self::$base]);

        if(!$months) $months = 1;

        $facilities = Facility::all();

        foreach ($facilities as $key => $fac) {
        	$row = DB::connection('api')->table('vl_site_dhis')
        		->where(['year' => date('Y', strtotime("-{$months} month")), 'month' => date('m', strtotime("-{$months} month")), 'facility' => $fac->id])
        		->first();

        	if(!$row || !$fac->DHIScode){
        		echo "Facility {$fac->id}  {$fac->name} missing";
        		continue;
        	}

        	$payload = [
				'dataSet' => 'c39u6D2m0au',
				'completeDate' => date('Y-m-d'),
				'period' => date('Ym', strtotime("-{$months} month")),
				'orgUnit' => $fac->DHIScode,
				// 'attributeOptionCombo' => 'aocID',
				'dataValues' => [
					// Male Suppressed
					[
						// 0-1 Male Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						'categoryOptionCombo' => 'Z9cc22tdFtK',
						'value' => $row->male_below_1_suppressed,
						'comment' => 'comment',
					],
					[
						// 1-9 Male Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'HvUBUwhQeSa',
						'categoryOptionCombo' => 'Yg1zqrAT9mS',
						'value' => $row->male_below_10_suppressed,
						'comment' => 'comment',
					],
					[
						// 10-14 Male Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'mwIrZsIrqtQ',
						'categoryOptionCombo' => 'gKLYDFU4mSV',
						'value' => $row->male_below_15_suppressed,
						'comment' => 'comment',
					],
					[
						// 15-19 Male Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'PSbJFRX51oB',
						'categoryOptionCombo' => 'zIYfBTRncpm',
						'value' => $row->male_below_20_suppressed,
						'comment' => 'comment',
					],
					[
						// 20-24 Male Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'UgmXHXhYULO',
						'categoryOptionCombo' => 'GFh2Bue6iwS',
						'value' => $row->male_below_25_suppressed,
						'comment' => 'comment',
					],
					[
						// > 25 Male Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'H4g4XCGjy3h',
						'categoryOptionCombo' => 'CbHPjpgsMT0',
						'value' => $row->male_above_25_suppressed,
						'comment' => 'comment',
					],

					// Female Suppressed
					[
						// 0-1 Female Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						'categoryOptionCombo' => 'fQaGWZ8R25L',
						'value' => $row->female_below_1_suppressed,
						'comment' => 'comment',
					],
					[
						// 1-9 Female Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'uyQ3KVohPar',
						'categoryOptionCombo' => 'gTzKy4LS0l7',
						'value' => $row->female_below_10_suppressed,
						'comment' => 'comment',
					],
					[
						// 10-14 Female Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'o0mJRFDoQnk',
						'categoryOptionCombo' => 'KwQazknW2Lt',
						'value' => $row->female_below_15_suppressed,
						'comment' => 'comment',
					],
					[
						// 15-19 Female Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'hrqb0jfAF4G',
						'categoryOptionCombo' => 'IqQ9Pgr5mZI',
						'value' => $row->female_below_20_suppressed,
						'comment' => 'comment',
					],
					[
						// 20-24 Female Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'BpkVIsemRRn',
						'categoryOptionCombo' => 'hipCW8yhAri',
						'value' => $row->female_below_25_suppressed,
						'comment' => 'comment',
					],
					[
						// > 25 Female Suppressed
						'dataElement' => 'H3JxUdxxNoe',
						// 'categoryOptionCombo' => 'g1Jm3gy8j3O',
						'categoryOptionCombo' => 'HlFzCSip1te',
						'value' => $row->female_above_25_suppressed,
						'comment' => 'comment',
					],

					

					// Male Non Suppressed
					[
						// 0-1 Male Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						'categoryOptionCombo' => 'Z9cc22tdFtK',
						'value' => $row->male_below_1_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 1-9 Male Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'HvUBUwhQeSa',
						'categoryOptionCombo' => 'Yg1zqrAT9mS',
						'value' => $row->male_below_10_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 10-14 Male Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'mwIrZsIrqtQ',
						'categoryOptionCombo' => 'gKLYDFU4mSV',
						'value' => $row->male_below_15_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 15-19 Male Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'PSbJFRX51oB',
						'categoryOptionCombo' => 'zIYfBTRncpm',
						'value' => $row->male_below_20_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 20-24 Male Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'UgmXHXhYULO',
						'categoryOptionCombo' => 'GFh2Bue6iwS',
						'value' => $row->male_below_25_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// > 25 Male Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'H4g4XCGjy3h',
						'categoryOptionCombo' => 'CbHPjpgsMT0',
						'value' => $row->male_above_25_nonsuppressed,
						'comment' => 'comment',
					],

					// Female Non Suppressed
					[
						// 0-1 Female Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						'categoryOptionCombo' => 'fQaGWZ8R25L',
						'value' => $row->female_below_10_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 1-9 Female Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'uyQ3KVohPar',
						'categoryOptionCombo' => 'gTzKy4LS0l7',
						'value' => $row->female_below_10_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 10-14 Female Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'o0mJRFDoQnk',
						'categoryOptionCombo' => 'KwQazknW2Lt',
						'value' => $row->female_below_15_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 15-19 Female Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'hrqb0jfAF4G',
						'categoryOptionCombo' => 'IqQ9Pgr5mZI',
						'value' => $row->female_below_20_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// 20-24 Female Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'BpkVIsemRRn',
						'categoryOptionCombo' => 'hipCW8yhAri',
						'value' => $row->female_below_25_nonsuppressed,
						'comment' => 'comment',
					],
					[
						// > 25 Female Non Suppressed
						'dataElement' => 'VyR4Qnz4Qeq',
						// 'categoryOptionCombo' => 'g1Jm3gy8j3O',
						'categoryOptionCombo' => 'HlFzCSip1te',
						'value' => $row->female_above_25_nonsuppressed,
						'comment' => 'comment',
					],
				],
			];
			// dd($payload);

			$response = $client->request('post', '', [
	            'auth' => [env('DHIS_USERNAME'), env('DHIS_PASSWORD')],
				'http_errors' => false,
				'verify' => false,
				'debug' => false,
				'json' => $payload,
			]);       
			
			$body = json_decode($response->getBody());

			$status_code = $response->getStatusCode();
			if($status_code > 399) echo 'Failed';
			else{
				DB::connection('api')->table('vl_site_dhis')->where('ID', $row->ID)->update(['time_sent_to_dhis' => date('Y-m-d H:i:s')]);
			}

			// dd($body);

        }
	}

	public static function kmhfl_login()
	{
		Cache::forget('kmhfl_token');
        $client = new Client(['base_uri' => self::$kmhfl_base]);

		$response = $client->request('post', 'o/token/', [
            // 'auth' => [env('KMHFL_USERNAME'), env('KMHFL_PASSWORD')],
            'auth' => [env('KMHFL_CLIENT_ID'), env('KMHFL_CLIENT_SECRET')],
			'http_errors' => false,
			'verify' => false,
			'debug' => true,
			'form_params' => [
				'grant_type' => 'password',
				'scope' => 'read',
				'username' => env('KMHFL_USERNAME'),
				'password' => env('KMHFL_PASSWORD'),
			],
		]);   
		$body = json_decode($response->getBody());

		$status_code = $response->getStatusCode();  

		if($status_code > 399) dd($body);
		Cache::put('kmhfl_token', $body->access_token, 60);
		return $body->access_token; 

		echo "Status code is {$status_code} \n"; 
		dd($body);
	}

	public static function get_kmhfl_token()
	{
		if!(Cache::has('kmhfl_token')) self::kmhfl_login();
		return Cache::get('kmhfl_token');
	}



	public static function kmhfl_facilities()
	{
        $client = new Client(['base_uri' => self::$kmhfl_base]);
        $page = 1;


        while(true){

			$response = $client->request('post', 'api/facilities/facilities', [
	            'headers' => [
	            	'Authorization' => 'Bearer ' . self::get_kmhfl_token(),
	            ],
				'http_errors' => false,
				'verify' => false,
				'debug' => true,
				'query' => [
					'format' => 'json',
					'page' => $page,
				],
			]); 




			$body = json_decode($response->getBody());  
			$status_code = $response->getStatusCode(); 
			if($status_code > 399) dd($body);

			foreach ($body->results as $key => $result) {
				# code...
			}

			if($page >= $body->current_page) break;

			$page++;

		}

		echo "Status code is {$status_code} \n"; 
		dd($body);

	}

}

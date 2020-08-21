<?php

namespace App;

use GuzzleHttp\Client;
use DB;

class Dhis 
{
	// public static $base = 'https://hiskenya.org/api/';
	public static $base = 'https://test.hiskenya.org/dhiske/';

	public static function send_data()
	{		
        $client = new Client(['base_uri' => self::$base]);

        /*$response = $client->request('get', $url, [
            'auth' => [env('DHIS_USERNAME'), env('DHIS_PASSWORD')],
            // 'http_errors' => false,
        ]);*/

        /*$base_object = [
        	'dataSet' => ''
        ];*/

        $facilities = Facility::all();

        foreach ($facilities as $key => $fac) {
        	$row = DB::connection('api')->table('vl_site_dhis')->where(['year' => date('Y', strtotime('-1 month')), 'month' => date('m', strtotime('-1 month')), 'facility' => $fac->id])->first();

        	if(!$row || !$fac->dhiscode){
        		echo "Facility {$fac->id} missing";
        		continue;
        	}

			$response = $client->request('post', '', [
	            'auth' => [env('DHIS_USERNAME'), env('DHIS_PASSWORD')],
				'http_errors' => false,
				'verify' => false,
				'json' => [
					'dataSet' => '',
					'completeDate' => date('Y-m-d'),
					'period' => date('Ym', strtotime('-1 month')),
					'orgUnit' => $fac->dhiscode,
					'attributeOptionCombo' => 'aocID',
					'dataValues' => [
						// Male Suppressed
						[
							// 1-9 Male Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'HvUBUwhQeSa',
							'value' => $row->male_below_10_suppressed,
							'comment' => 'comment',
						],
						[
							// 10-14 Male Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'mwIrZsIrqtQ',
							'value' => $row->male_below_15_suppressed,
							'comment' => 'comment',
						],
						[
							// 15-19 Male Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'PSbJFRX51oB',
							'value' => $row->male_below_20_suppressed,
							'comment' => 'comment',
						],
						[
							// 20-24 Male Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'UgmXHXhYULO',
							'value' => $row->male_below_25_suppressed,
							'comment' => 'comment',
						],
						[
							// > 25 Male Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'H4g4XCGjy3h',
							'value' => $row->male_above_25_suppressed,
							'comment' => 'comment',
						],

						// Female Suppressed
						[
							// 1-9 Female Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'uyQ3KVohPar',
							'value' => $row->female_below_10_suppressed,
							'comment' => 'comment',
						],
						[
							// 10-14 Female Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'o0mJRFDoQnk',
							'value' => $row->female_below_15_suppressed,
							'comment' => 'comment',
						],
						[
							// 15-19 Female Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'hrqb0jfAF4G',
							'value' => $row->female_below_20_suppressed,
							'comment' => 'comment',
						],
						[
							// 20-24 Female Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'BpkVIsemRRn',
							'value' => $row->female_below_25_suppressed,
							'comment' => 'comment',
						],
						[
							// > 25 Female Suppressed
							'dataElement' => 'H3JxUdxxNoe',
							'categoryOptionCombo' => 'g1Jm3gy8j3O',
							'value' => $row->female_above_25_suppressed,
							'comment' => 'comment',
						],

						

						// Male Non Suppressed
						[
							// 1-9 Male Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'HvUBUwhQeSa',
							'value' => $row->male_below_10_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// 10-14 Male Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'mwIrZsIrqtQ',
							'value' => $row->male_below_15_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// 15-19 Male Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'PSbJFRX51oB',
							'value' => $row->male_below_20_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// 20-24 Male Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'UgmXHXhYULO',
							'value' => $row->male_below_25_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// > 25 Male Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'H4g4XCGjy3h',
							'value' => $row->male_above_25_nonsuppressed,
							'comment' => 'comment',
						],

						// Female Non Suppressed
						[
							// 1-9 Female Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'uyQ3KVohPar',
							'value' => $row->female_below_10_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// 10-14 Female Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'o0mJRFDoQnk',
							'value' => $row->female_below_15_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// 15-19 Female Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'hrqb0jfAF4G',
							'value' => $row->female_below_20_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// 20-24 Female Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'BpkVIsemRRn',
							'value' => $row->female_below_25_nonsuppressed,
							'comment' => 'comment',
						],
						[
							// > 25 Female Non Suppressed
							'dataElement' => 'VyR4Qnz4Qeq',
							'categoryOptionCombo' => 'g1Jm3gy8j3O',
							'value' => $row->female_above_25_nonsuppressed,
							'comment' => 'comment',
						],
					],
				],
			]);       
			
			$body = json_decode($response->getBody());

			$status_code = $response->getStatusCode();
			if($status_code > 399)
				echo $body;

			// dd($body);

        }
	}
}

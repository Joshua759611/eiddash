<?php

namespace App;


use App\Mail\CriticalResults;

use DB;
use Exception;
use GuzzleHttp\Client;

use Illuminate\Support\Facades\Mail;

use App\Mail\TestMail;

use App\Mail\EidPartnerPositives;
use App\Mail\EidCountyPositives;
use App\Mail\VlPartnerNonsuppressed;
use App\Mail\VlCountyNonsuppressed;
use App\Mail\PasswordEmail;
use App\Mail\VlSummary;

class Report
{
    public static $my_classes = [
        'eid' => [
            'sampleview_class' => \App\SampleView::class,
            'view_table' => 'samples_view',
        ],

        'vl' => [
            'sampleview_class' => \App\ViralsampleView::class,
            'view_table' => 'viralsamples_view',
        ],
    ];

	public static $email_array = ['rosaga@healthit.uonbi.ac.ke', 'sinjiri@healthit.uonbi.ac.ke', 'baksajoshua09@gmail.com'];


    public static function test_email()
    {
        Mail::to(['baksajoshua09@gmail.com', 'joelkith@gmail.com'])->send(new TestMail());
    }

	public static function clean_emails($base = 'https://api.mailgun.net/v3/nascop.or.ke/complaints', $iter=0)
	{
		// $base = 'https://api.mailgun.net/v3/nascop.or.ke/complaints';
		$client = new Client(['base_uri' => $base]);
		$response = $client->request('get', '', [
			'auth' => ['api', env('MAIL_API_KEY')],
		]);
		$body = json_decode($response->getBody());
		if($response->getStatusCode() > 399) return false;
		// dd($body);

		// $emails = [];

		foreach ($body->items as $key => $value) {
			// $emails[] = $value->address;
			BlockedEmail::firstOrCreate(['email' => $value->address]);
		}
		// if($iter == 1) dd($body);

		if($iter > 30) die();
		self::clean_emails($body->paging->next, $iter++);
	}

	public static function my_string_contains($str, $search_array)
	{
		if(!is_array($search_array)) return str_contains($str, $search_array);
		foreach ($search_array as $value) {
			if(str_contains($str, $value)) return true;
		}
		return false;
	}

	public static function eid_partner($partner_contact=null)
	{
		$partner_contacts = EidPartner::when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 1)
            ->get();

		foreach ($partner_contacts as $key => $contact) {

			// echo "Eid Partner contact {$contact->id} \n";

	        $cc_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact->toArray() as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(self::my_string_contains($column_name, ['ccc', 'bcc', 'mainrecipientmail'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "\t\t Removed blocked email {$value} from column {$column_name} \n";
	        			continue;
	        		}
	        	}
	        	else{}
    			// echo "\t\t Column {$column_name} Value {$value} \n";	

	        	if(self::my_string_contains($column_name, ['ccc', 'mainrecipientmail']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $cc_array[] = $value;
	        	/*else if(self::my_string_contains($column_name, 'ccc') && !filter_var($value, FILTER_VALIDATE_EMAIL)){
		        	echo "\t\t Email {$column_name} {$value} is invalid \n";	        		
	        	}*/
	        	else{}

	        	if(self::my_string_contains($column_name, ['bcc']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $bcc_array[] = $value;
	        	/*else if(self::my_string_contains($column_name, 'bcc') && !filter_var($value, FILTER_VALIDATE_EMAIL)){
		        	echo "\t\t Email {$column_name} {$value} is invalid \n";	        		
	        	}*/
	        	else{}
	        }



	        if(env('APP_LOCATION') == 'server'){
		        try {
			        Mail::to($cc_array)->bcc($bcc_array)->send(new EidPartnerPositives($contact->id));
			        DB::table('eid_partner_contacts_for_alerts')->where('id', $contact->id)->update(['lastalertsent' => date('Y-m-d')]);
		        } catch (Exception $e) {
		        	echo $e->getMessage();
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new EidPartnerPositives($contact->id));
		    }

		}
	}

	public static function eid_county($county_id=null)
	{
		$county_contacts = EidUser::when($county_id, function($query) use ($county_id){
                return $query->where('partner', $county_id);
            })->where(['flag' => 1, 'account' => 7])->where('id', '>', 384)->get();

		foreach ($county_contacts as $key => $contact) {

	        $mail_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact->toArray() as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(self::my_string_contains($column_name, ['email'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "Removed blocked email {$value} \n";
	        			continue;
	        		}
	        	}

	        	if(self::my_string_contains($column_name, ['email']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $mail_array[] = trim($value);
	        }

	        if(env('APP_LOCATION') == 'server'){
		        try {
			        DB::table('eid_users')->where('id', $contact->id)->update(['datelastsent' => date('Y-m-d')]);
			     	Mail::to($mail_array)->bcc($bcc_array)->send(new CountyEidPositivesMailBuilder($contact->id));
		        } catch (Exception $e) {
		        	echo $e->getMessage();		        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new CountyEidPositivesMailBuilder($contact->id));
		    }
		}
	}

	public static function vl_partner($partner_contact=null)
	{
		$partner_contacts = VlPartner::when($partner_contact, function($query) use ($partner_contact){
                return $query->where('id', $partner_contact);
            })->where('active', 2)->get();

		foreach ($partner_contacts as $key => $contact) {

	        $cc_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact->toArray() as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(self::my_string_contains($column_name, ['ccc', 'bcc', 'mainrecipientmail'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "Removed blocked email {$value} \n";
	        			continue;
	        		}
	        	}

	        	if(self::my_string_contains($column_name, ['ccc', 'mainrecipientmail']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $cc_array[] = trim($value);
	        	if(self::my_string_contains($column_name, ['bcc']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $bcc_array[] = trim($value);
	        }
	        if(env('APP_LOCATION') == 'server'){
		        try {
			        Mail::to($cc_array)->bcc($bcc_array)->send(new VlPartnerNonsuppressed($contact->id));
			        DB::table('vl_partner_contacts_for_alerts')->where('id', $contact->id)->update(['lastalertsent' => date('Y-m-d')]);
		        } catch (Exception $e) {
		        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new VlPartnerNonsuppressed($contact->id));
		    }
		}
	}

	/**
	function can help extract mails from the hr growing table on db as quick fix
     */
    public static function contacts_for_alerts($testType)
    {
        $partner_contacts = [];
        if ($testType === 'vl') {
            $partner_contacts = VlPartner::where('active', 2)->get();

        } else {
            if ($testType === 'eid') {
                $partner_contacts = EidPartner::where('active', 1)->get();

            }
        }
        $cc_array = [];
        $bcc_array = [];
        foreach ($partner_contacts as $key => $contact) {
            foreach ($contact->toArray() as $column_name => $value) {
                $value = trim($value);

                // Check if email address is blocked
                if (self::my_string_contains($column_name, ['ccc', 'bcc', 'mainrecipientmail'])) {
                    $b = BlockedEmail::where('email', $value)->first();
                    if ($b) {
                        $contact->$column_name = null;
                        $contact->save();
                        echo "Removed blocked email {$value} \n";
                        continue;
                    }
                }
                $partner_name = DB::table('partners')->where('id', $contact->partner)->first()->name ?? '';
                $county = DB::table('countys')->where('id', $contact->county)->first()->name ?? '';

                if (self::my_string_contains($column_name, ['ccc', 'mainrecipientmail']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $cc_array[] = [trim($value), $contact->partner, $partner_name, $county, $contact->active, $contact->lastalertsent];
                if (self::my_string_contains($column_name, ['bcc']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $bcc_array[] = trim($value);
            }

        }
        $filename = "alert_vl_.csv";

        $handle = fopen($filename, 'w');
        fputcsv($handle, array('Mail', 'eid_system_partner_id', 'Partner Name', 'county', 'notification_status', 'date_last_alert_sent'));
        foreach ($cc_array as $k_arr => $cc_val) {
            fputcsv($handle, $cc_val, $separator = ",", $enclosure = '"', $escape = "\\");
        }
        fclose($handle);
        $headers = array(
            'Content-Type' => 'text/csv',
        );

    }
	public static function vl_county($county_id=null)
	{
		$county_contacts = EidUser::when($county_id, function($query) use ($county_id){
                return $query->where('partner', $county_id);
            })->where(['flag' => 1, 'account' => 7])->where('id', '>', 384)->get();

		foreach ($county_contacts as $key => $contact) {

	        $mail_array = [];
	        $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        foreach ($contact->toArray() as $column_name => $value) {
	        	$value = trim($value);

	        	// Check if email address is blocked
	        	if(self::my_string_contains($column_name, ['email'])){
	        		$b = BlockedEmail::where('email', $value)->first();
	        		if($b){
	        			$contact->$column_name=null;
	        			$contact->save();
	        			echo "Removed blocked email {$value} \n";
	        			continue;
	        		}
	        	}

	        	if(self::my_string_contains($column_name, ['email']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $mail_array[] = trim($value);
	        }
	        if(env('APP_LOCATION') == 'server'){
		        try {
			        DB::table('eid_users')->where('id', $contact->id)->update(['datelastsent' => date('Y-m-d')]);
			     	Mail::to($mail_array)->bcc($bcc_array)->send(new VlCountyNonsuppressed($contact->id));
		        } catch (Exception $e) {
		        	echo $e->getMessage();			        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new VlCountyNonsuppressed($contact->id));
		    }
		}
	}

	public static function vl_summary()
	{
		// $county_contacts = EidUser::when($county_id, function($query) use ($county_id){
        //         return $query->where('partner', $county_id);
        //     })->where(['flag' => 1, 'account' => 7])->where('id', '>', 384)->get();
		$county_contacts = DB::table('partner_facility_contacts')
		->where('lab_summary','=',1)
		->whereNull('deleted_at')
		->get();
		// ['rosaga@healthit.uonbi.ac.ke','jmugah@healthit.uonbi.ac.ke','mnjatha@healthit.uonbi.ac.ke','elvokip@gmail.com'];

		foreach ($county_contacts as $key => $contact) {
	        $mail_array = [$contact->email];
	        // $bcc_array = ['joel.kithinji@dataposit.co.ke', 'joshua.bakasa@dataposit.co.ke', 'tngugi@clintonhealthaccess.org'];

	        // foreach ($contact->toArray() as $column_name => $value) {
	        // 	$value = trim($value);

	        // 	// Check if email address is blocked
	        // 	if(self::my_string_contains($column_name, ['email'])){
	        // 		$b = BlockedEmail::where('email', $value)->first();
	        // 		if($b){
	        // 			$contact->$column_name=null;
	        // 			$contact->save();
	        // 			echo "Removed blocked email {$value} \n";
	        // 			continue;
	        // 		}
	        // 	}

	        // 	if(self::my_string_contains($column_name, ['email']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $mail_array[] = trim($value);
	        // }
	        if(env('APP_LOCATION') == 'server'){
		        try {
			        // DB::table('eid_users')->where('id', $contact->id)->update(['datelastsent' => date('Y-m-d')]);
			     	// Mail::to($mail_array)->bcc($bcc_array)->send(new VlSummary($contact->id));
			     	Mail::to($mail_array)->send(new VlSummary($contact->id));

		        } catch (Exception $e) {
		        	echo $e->getMessage();			        	
		        }
		    }
		    else{
		    	Mail::to(self::$email_array)->send(new VlSummary($contact->id));
		    }
		}
	}


    public static function send_communication()
    {
        $emails = \App\Email::where('sent', false)->where('time_to_be_sent', '<', date('Y-m-d H:i:s'))->get();

        foreach ($emails as $email) {
        	$email->dispatch();
        }
    }

    public static function delete_folder($path)
    {
        if(!ends_with($path, '/')) $path .= '/';
        $files = scandir($path);
        if(!$files) rmdir($path);
        else{
            foreach ($files as $file) {
            	if($file == '.' || $file == '..') continue;
            	$a=true;
                if(is_dir($path . $file)) self::delete_folder($path . $file);
                else{
                	unlink($path . $file);
                }              
            }
            rmdir($path);
        }
    }


	public static function test()
	{
        $totals = \App\SampleAlertView::selectRaw("facility_id, enrollment_status, facilitycode, facility, county, subcounty, partner, count(distinct patient_id) as total")
            ->whereIn('pcrtype', [1, 2, 3])
            ->where(['result' => 2, 'repeatt' => 0, 'county_id' => 1])
            ->whereYear('datetested', date('Y'))
            ->groupBy('facility_id', 'enrollment_status')
            ->orderBy('facility_id')
            ->get();

        return $totals;
	}

	// public static function send_password()
	// {
	// 	$users = \App\User::where('user_type_id', '<>', 8)->where('user_type_id', '<>', 2)->where('user_type_id', '<>', 10)->whereNull('deleted_at')->whereRaw("email like '%@%'")->whereRaw("email not like '%example%'")->whereNull('email_sent')->get();
		
	// 	foreach ($users as $key => $value) {
	// 		$user = \App\User::find($value->id);
	// 		Mail::to($value->email)->send(new PasswordEmail($value->id));
	// 		if( count(Mail::failures()) > 0 ) {
	// 		   echo "==>There was one or more failures. They were: <br />";
	// 		   foreach(Mail::failures() as $email_address) {
	// 		   		$user->email_sent = NULL;
	// 		   		$user->save();
	// 		       	echo " - $email_address <br />";
	// 		    }
	// 		} else {
	// 		    echo "==> No errors, all sent successfully!</br>";
	// 		    $user->email_sent = date('Y-m-d H:i:s');
	// 	   		$user->save();
	// 		}
	// 	}
	// }

    public static function build_query()
    {
        $partner_facility_contacts = DB::table('partner_facility_contacts')->get();

        foreach ($partner_facility_contacts as $partner_facility_contact) {
            print_r($partner_facility_contact);
        }
    }


    public static function critical_results($type)
    {
        $sampleview_class = self::$my_classes[$type]['sampleview_class'];
        $view_table = self::$my_classes[$type]['view_table'];
        $dt = date('Y-m-d', strtotime('-7 days'));
        $q = 'rcategory IN (3, 4)';
        $lab = \App\Lab::find(env('APP_LAB'));
        if ($type == 'eid') $q = 'result=2';

        $facilities = Facility::whereRaw("id IN (SELECT DISTINCT facility_id FROM {$view_table} WHERE datedispatched >= '{$dt}' AND repeatt=0 AND {$q})")->get();
        $data = [];
        $index = 0;
            $samples = $sampleview_class::whereRaw($q)
                ->where('datedispatched', '>=', $dt)
                ->where(['repeatt' => 0])
                ->orderBy('county', 'asc')
                ->get();

            foreach ($samples as $key => $sample) {
                $data[$index]['patient'] = $sample->patient;
                $data[$index]['facility_mfl'] = $sample->facility_code;
                $age = $sample->age;
                if ($age && $age < 15) {
                    $data[$index]['group'] = "Child";
                } else {
                    $data[$index]['group'] = "Adult";
                }
                $data[$index]['facility_name'] = $sample->facility_name;
                $data[$index]['county'] = $sample->county;
                $data[$index]['datecollected'] = $sample->my_date_format('datecollected');
                $data[$index]['tat_collected_to_received'] = $sample->tat_collected_to_received;
                $data[$index]['tat_received_to_tested'] = $sample->tat_received_to_tested;
                $data[$index]['tat_datetested_to_date_dispatched'] = $sample->tat_datetested_to_date_dispatched;
                $data[$index]['datedispatched'] = $sample->my_date_format('datedispatched');
                if ($sample->result && $type == 'eid') {
                    $data[$index]['result'] = "Positive";
                } elseif($sample->result && $type == 'vl'){
					$data[$index]['result'] = $sample->result;
				}else {
                    $data[$index]['result'] = '';
                }
                $index++;
            }
        // }
		
		
        if ($data){
            $cc_array = [];
            $to_array = [];
            $mail_array = ['sinjiri@healthit.uonbi.ac.ke'];

            $partner_contacts = DB::table('partner_facility_contacts')
                ->whereRaw('partner_facility_contacts.deleted_at is null')
                ->where('critical_results', 1)
                ->get();//add criteria for critical result ==1

            foreach ($partner_contacts as $key => $contact) {
                if ($contact->type == "Recepient") {
                    array_push($to_array, $contact->email);
                } else {
                    if ($contact->type == "Cc") {
                        array_push($cc_array, $contact->email);

                    }
                }
            }
            if (env('APP_ENV') == 'production') {
                try {
					// dd($data);
                    $comm = new CriticalResults( $type, $data, $dt);
                    Mail::to($to_array)->cc($cc_array)->send($comm);
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            } else {
                try {
                    $comm = new CriticalResults($type, $data, $dt);
                    Mail::to($mail_array)->cc([$lab->email])->send($comm);
                } catch (Exception $e) {
                    dd($e->getMessage());
                }
            }
        }else{
            //TODO implement a custom feedback mechanism for weeks with no critical results reported
            echo "No critical results for specified period";
        }
		// 
    }

}

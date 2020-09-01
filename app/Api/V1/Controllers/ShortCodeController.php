<?php
namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\GenerealController;
use App\Api\V1\Requests\ShortCodeRequest;
use App\SampleCompleteView;
use App\ViralsampleCompleteView;
use App\Patient;
use App\Viralpatient;
use App\Facility;
use App\ShortCodeQueries;
use GuzzleHttp\Client;
/**
 * 
 */
class ShortCodeController extends Controller
{
    // public static $sms_url = 'https://api.vaspro.co.ke/v3/BulkSMS/api/create';
    public static $sms_url = 'https://mysms.celcomafrica.com/api/services/sendsms/';
	public static $sms_callback = 'http://vaspro.co.ke/dlr';

    private $limit = 2;

    private $msgFormat = "R`MFLCode`-`Patient Number`";

    private $msgFormatDescription = "This message should always begin with `R` this is immediatly followed by the MFLCode without any character between the `R` and the `MFLCode`. The MFLCode is immediatly followed by a hyphen (-). The hyphen is immediately followed by the patient number as it appears on the patient file.\nN/B The shortcode does not contain any spaces in it and there is no hyphen after the `R`";

	public function shortcode(ShortCodeRequest $request) {
		$message = $request->input('smsmessage');
		$phone = $request->input('smsphoneno');
		$patient = null;
		$facility = null;
		$testtype = null;
		$status = 1;
		$messageBreakdown = $this->messageBreakdown($message);
		if (!$messageBreakdown) {
			$message = "The correct message format is {$this->msgFormat}\n {$this->msgFormatDescription}";
			return response()->json(self::__sendMessage($phone, $message));
		}
		$patientTests = $this->getPatientData($messageBreakdown, $patient, $facility); // Get the patient data

		$textMsg = $this->buildTextMessage($patientTests, $status, $testtype); // Get the message to send to the patient.
		$sendTextMsg = $this->sendTextMessage($textMsg, $patient, $facility, $status, $message, $phone, $testtype); // Save and send the message
		return response()->json($textMsg);
	}

	private function messageBreakdown($message = null) {
		if (!$message)
			return null;
		if (!$this->checkMessageFormat($message)) // Check if the correct message format was adhered to
			return null;
		$data['querytype'] = substr($message,0,1);
		$data['mflcode'] = substr($message,1,5);
		$querytypeplusmfl = substr($message,0,6);
		$data['sampleID'] = substr($message, ($pos = strpos($message, $querytypeplusmfl)) !== false ? $pos + 7 : 0);

		return (object) $data;
	}

	private function checkMessageFormat($message) {
		return preg_match("/^[rR][0-9]{5,6}[-][a-zA-Z0-9\/-_.]{3,}/", $message);
	}

	private function getPatientData($message = null, &$patient, &$facility){
		if(empty($message))
			return null;
		$facility = Facility::select('id', 'facilitycode')->where('facilitycode', '=', $message->mflcode)->first();
		if(!$facility) return null;
		$dbPatient = Patient::select('id', 'patient')->where('patient', '=', $message->sampleID)->where('facility_id', '=', $facility->id)->get(); // EID patient
		$class = SampleCompleteView::class;
		$table = 'sample_complete_view';
		if ($dbPatient->isEmpty()) { // Check if VL patient
			$dbPatient = Viralpatient::select('id', 'patient')->where('patient', '=', $message->sampleID)->where('facility_id', '=', $facility->id)->get();
			$class = ViralsampleCompleteView::class;
			$table = 'viralsample_complete_view';
		}
		if ($dbPatient->isEmpty())
			return null;

		$patient = $dbPatient;
		return $this->getTestData($patient->first(), $class, $table);
	}

	private function getTestData($patient, $class, $table) {
		$select = "$table.*, view_facilitys.name as facility, view_facilitys.facilitycode, labs.labdesc as lab";
		$model = $class::selectRaw($select)
						->join('view_facilitys', 'view_facilitys.id', '=', "$table.facility_id")
						->leftJoin('labs', 'labs.id', '=', "$table.lab_id")
						->where('patient_id', '=', $patient->id)
						->where('repeatt', '=', 0)
						->orderBy("$table.id", 'desc')
						->limit($this->limit)
						->get();
		return $model;
	}

	private function buildTextMessage($tests = null, &$status, &$testtype){
		if (empty($tests))
			return "No test data found for the patient number provided.";
		$msg = '';
		$inprocessmsg="Sample Still In process at the ";
		$inprocessmsg2=" The Result will be automatically sent to your number as soon as it is Available.";
		
		foreach ($tests as $key => $test) {
			$testtype = (get_class($test) == 'App\ViralsampleCompleteView') ? 2 : 1;
			$msg .= "Facility: " . $test->facility . " [ " . $test->facilitycode . " ]\n";
			$msg .= (get_class($test) == 'App\ViralsampleCompleteView') ? "CCC #: " : "HEI #:";
			$msg .= $test->patient . "\n";
			$msg .= "Batch #: " . $test->original_batch_id . "\n";
			$msg .= "Date Drawn: " . $test->datecollected . "\n";
			if ($test->receivedstatus != 2) {
				if ($test->result){
					$msg .= "Date Tested: " . $test->datetested . "\n";
				} else{
					$msg .= $inprocessmsg . "\n";
					$status = 0;
				}
				if (isset($test->result) && get_class($test) == 'App\ViralsampleCompleteView')
					$msg .= "VL Result: " . $test->result . "\n";
				else if (isset($test->result) && get_class($test) == 'App\SampleCompleteView')
					$msg .= "EID Result: " . $test->result_name . "\n";
			} else {
				$msg .= (get_class($test) == 'App\ViralsampleCompleteView') ? " VL" : " EID";
				$msg .= " Rejected Sample: " . $test->rejected_reason->name . " - Collect New Sample.\n";
			}
			$lab = $test->lab;
			if ($test->lab == NULL)
				$lab = 'POC';
			$msg .= "Lab Tested In: " . $lab;
			$msg .= (!$test->result && $test->receivedstatus != 2) ? "\n" . $inprocessmsg2 : "\n\n";
		}
		return $msg;
	}

	private function sendTextMessage($msg, $patient = null, $facility = null, $status, $receivedMsg, $phone, $testtype) {
		if (empty($patient)){
			$msg = "The Patient Idenfier Provided Does not Exist in the Lab. Kindly confirm you have the correct one as on the Sample Request Form. Thanks.";
		} else {
			$patient = $patient->first()->id;
		}
		date_default_timezone_set('Africa/Nairobi');
        $dateresponded = date('Y-m-d H:i:s');
		$response = \App\Common::sms($phone, $msg);
		$shortcode = new ShortCodeQueries;
		$shortcode->testtype = $testtype;
		$shortcode->phoneno = $phone;
		$shortcode->message = $receivedMsg;
		$shortcode->facility_id = $facility->id ?? null;
		$shortcode->patient_id = $patient;
		$shortcode->datereceived = $dateresponded;
		if ($response)
			$shortcode->dateresponded = $dateresponded;
		$shortcode->status = $status;

		// if ($response->code < 400)
		if ($response)
			$shortcode->dateresponded = $dateresponded;
		$shortcode->save();
		return $msg;
	}

    static function __sendMessage($phone, $message) {
       $client = new Client(['base_uri' => self::$sms_url]);

		// $response = $client->request('post', '', [
		// 	// 'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
		// 	'http_errors' => false,
		// 	'json' => [
		// 		// 'sender' => env('SMS_SENDER_ID'),
  //               'apiKey' => env('SMS_KEY'),
  //               'shortCode' => env('SMS_SENDER_ID'),
		// 		'recipient' => $phone,
		// 		'message' => $message,
  //               'callbackURL' => self::$sms_callback,
  //               'enqueue' => 0,
		// 	],
		// ]);
		// return $response->getStatusCode();

		$response = $client->request('post', '', [
			// 'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
			'http_errors' => false,
			'debug' => false,
			'json' => [
                'apikey' => env('SMS_KEY'),
                'shortcode' => env('SMS_SENDER_ID'),
                'partnerID' => env('SMS_PARTNER_ID'),
				'mobile' => $phone,
				'message' => $message,
			],
		]);

		return (object)[
				'code' => $response->getStatusCode(),
				'body' => json_decode($response->getBody())
			];

		// $body = json_decode($response->getBody());
  //       if($response->getStatusCode() == 402) die();
		// // if($response->getStatusCode() == 201){
  //       if($response->getStatusCode() < 300) return true;
    }

    static function __oldSendMessage() {
    	 $client = new Client(['base_uri' => self::$sms_url]);

        $response = $client->request('post', '', [
            'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
            'http_errors' => false,
            'json' => [
                'sender' => env('SMS_SENDER_ID'),
                'recipient' => $phone,
                'message' => $message,
            ],
        ]);
        return $response->getStatusCode();    	
    }
}

?>
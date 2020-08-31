<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;

use App\Mail\TestMail;

class ShortCodeQueries extends Model
{
    protected $guarded = [];
    // public static $sms_url = 'https://api.vaspro.co.ke/v3/BulkSMS/api/create';
    public static $sms_url = 'https://mysms.celcomafrica.com/api/services/sendsms/';
	public static $sms_callback = 'http://vaspro.co.ke/dlr';

    private $limit = 5;

    private $msgFormat = "R`MFLCode`-`Patient Number`";

    private $msgFormatDescription = "This message should always begin with `R` this is immediatly followed by the MFLCode without any character between the `R` and the `MFLCode`. The MFLCode is immediatly followed by a hyphen (-). The hyphen is immediately followed by the patient number as it appears on the patient file.\nN/B The shortcode does not contain any spaces in it and there is no hyphen after the `R`";

    public function resendSMS()
    {
    	echo "==> Get unresponded SMS for date('Y')\n";
    	$smses = $this->getUnsentSMS();
    	$valid = 0;
    	$invalid = 0;
    	$loop = 0;
    	$ratio = 0;
    	echo "==> Gotten {$smses->count()} requests\n";
    	foreach ($smses as $key => $sms) {
    		$newratio = round((@($loop/$smses->count())*100));
    		if ($ratio != $newratio){
    			echo $ratio . '%';
    			$ratio = $newratio;
    		}
    		$message = $sms->message;
    		$message = $sms->message;
			$phone = $sms->phoneno;
			$patient = null;
			$facility = null;
			$testtype = null;
			$status = 1;
    		$messageBreakdown = $this->messageBreakdown($message);
			if (!$messageBreakdown) {
				$invalid++;
				$message = "The correct message format is {$this->msgFormat}\n {$this->msgFormatDescription}";
				self::__sendMessage($phone, $message);
			}
			$patientTests = $this->getPatientData($messageBreakdown, $patient, $facility); // Get the patient data
			$textMsg = $this->buildTextMessage($patientTests, $status, $testtype); // Get the message to send to the patient.
			$sendTextMsg = $this->sendTextMessage($textMsg, $patient, $facility, $status, $message, $phone, $testtype, $sms); // Save and send the message
			$valid++;
			$loop++;
    	}
    	echo "==>Completed sending SMS with valid SMS`s : {$valid} and invalid SMS`s: {$invalid}";
    }

    public function getUnsentSMS()
    {
    	return $this->whereNull('dateresponded')->whereYear('created_at', date('Y'))->get();
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
	}private function getTestData($patient, $class, $table) {
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
		$msg = '';
		$inprocessmsg="Sample Still In process at the ";
		$inprocessmsg2=" The Result will be automatically sent to your number as soon as it is Available.";
		if (empty($tests))
			return $msg;
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
				$msg .= " Rejected Sample: " . $test->rejected_reason($test->rejectedreason) . " - Collect New Sample.\n";
			}
			$lab = $test->lab;
			if ($test->lab == NULL)
				$lab = 'POC';
			$msg .= "Lab Tested In: " . $lab;
			$msg .= (!$test->result && $test->receivedstatus != 2) ? "\n" . $inprocessmsg2 : "\n\n";
		}
		return $msg;
	}

	private function sendTextMessage($msg, $patient = null, $facility = null, $status, $receivedMsg, $phone, $testtype, $shortcode = null) {
		if (empty($patient)){
			$msg = "The Patient Idenfier Provided Does not Exist in the Lab. Kindly confirm you have the correct one as on the Sample Request Form. Thanks.";
		} else {
			$patient = $patient->first()->id;
		}

		date_default_timezone_set('Africa/Nairobi');
        $dateresponded = date('Y-m-d H:i:s');
		$response = Common::sms($phone, $msg);
		// dd($response);
		if (!isset($shortcode))
			$shortcode = new ShortCodeQueries;
		$shortcode->testtype = $testtype;
		$shortcode->phoneno = $phone;
		$shortcode->message = $receivedMsg;
		$shortcode->facility_id = $facility->id ?? null;
		$shortcode->patient_id = $patient;
		$shortcode->datereceived = $dateresponded;
		$shortcode->status = $status;

		// if ($response->code < 400){
		if ($response){
			$shortcode->dateresponded = $dateresponded;
		} else {
			Mail::to(['baksajoshua09@gmail.com'])->send(new TestMail(null, $response));
		}
		$shortcode->save();
		return $msg;
	}

    static function __sendMessage($phone, $message) {
       $client = new Client(['base_uri' => self::$sms_url]);

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

		$response = $client->request('post', '', [
			// 'auth' => [env('SMS_USERNAME'), env('SMS_PASSWORD')],
			'http_errors' => false,
			'debug' => true,
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
    }

    public static function sendPendingMessages()
    {
    	return self::__sendMessage('254725455925', 'Prepaation for the pending SMS`s');
    }
}

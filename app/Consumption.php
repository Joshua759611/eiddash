<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Consumption extends Model
{
    /**
     * The attributes that should be guarded from mass assignment.
     *
     * @var array
     */

    protected $guarded = [];

    public function scopeExisting($query, $year, $month, $lab_id)
    {
        return $query->where(['year' => $year, 'month' => $month, 'lab_id' => $lab_id]);
    }

    public function details()
    {
        return $this->hasMany('App\ConsumptionDetail');
    }

    public function lab()
    {
        return $this->belongsTo('App\Lab');
    }

    public function apisave() {
        $this->synched = 1;
        $this->datesynched = date('Y-m-d');
        $this->save();
    }

    public function saveConsumption($consumption)
    {
        DB::beginTransaction();
        try {
            $lab = Lab::find($consumption->lab_id);
            $this->original_consumption_id = $consumption->id;
            $this->month = $consumption->month;
            $this->year = $consumption->year;
            $this->lab_id = $lab->id;
            $this->datesubmitted = $consumption->datesubmitted;
            $this->submittedby = $consumption->submittedby ?? $lab->labname;
            $this->synched = 1;
            $this->datesynched = date('Y-m-d');
            $this->save();

            $this->saveConsumptionDetails($this, $consumption);

            DB::commit();
            return (object)[
                'success' => true,
                'data' => $this
            ];
        } catch(Exception $e) {
            DB::rollBack();
            return (object)['error' => true, 'message' => $e];
        }
    }

    public function reviewed($testtype=null){
        $details = $this->details->when($testtype, function($query) use ($testtype){
                            if ($testtype == 'EID')
                                return $query->where('testtype', '=', 1);
                            else if ($testtype == 'VL')
                                return $query->where('testtype', '=', 2);
                            else if ($testtype == 'CONSUMABLES')
                                return $query->where('testtype', '=', NULL);         
                        })->count();
        if ($details > 0)
            return true;
        return false;
    }

    private function saveConsumptionDetails($consumption, $inconsumption)
    {
        $existing = ConsumptionDetail::existing($consumption->id, $inconsumption->type, $inconsumption->machine)->get();
        if ($existing->isEmpty()) {
            // New consumption to be saved
            $saveconsumptiondetails = new ConsumptionDetail();
            $saveconsumptiondetails->original_consumption_details_id = $inconsumption->id;
            $saveconsumptiondetails->consumption_id = $consumption->id;
            $saveconsumptiondetails->machine_id = $inconsumption->machine;
            $saveconsumptiondetails->testtype = $inconsumption->type;
            $saveconsumptiondetails->tests = $inconsumption->tests;
            $saveconsumptiondetails->synched = 1;
            $saveconsumptiondetails->datesynched = date('Y-m-d');
            $saveconsumptiondetails->save();
        } else {
            $saveconsumptiondetails = $existing->first();
            $saveconsumptiondetails->tests = $inconsumption->tests;
            $saveconsumptiondetails->save();
        }

        return $this->saveConsumptionDetailBreakdown($saveconsumptiondetails, $inconsumption->details);
    }

    private function saveConsumptionDetailBreakdown($detail, $breakdown)
    {
        foreach ($breakdown as $key => $item) {
            $kit = $item->kit ?? NULL;
            if (null !== $kit) {
                $existing = ConsumptionDetailBreakdown::existing($detail->id, $item->kit->id, Kits::class)->get();
                if ($existing->isEmpty()) {
                    $saveconsumptiondetailbreakdown = new ConsumptionDetailBreakdown;
                    $saveconsumptiondetailbreakdown->original_consumption_details_breakdown_id = $item->id;
                    $saveconsumptiondetailbreakdown->consumption_details_id = $detail->id;
                    $saveconsumptiondetailbreakdown->consumption_breakdown_id = $item->kit->id;
                    $saveconsumptiondetailbreakdown->consumption_breakdown_type = Kits::class;
                } else {
                    $saveconsumptiondetailbreakdown = $existing->first();
                }
                $saveconsumptiondetailbreakdown->opening = $item->begining_balance;
                $saveconsumptiondetailbreakdown->consumed = $item->used;
                $saveconsumptiondetailbreakdown->qty_received = $item->received;
                $saveconsumptiondetailbreakdown->wasted = $item->wasted;
                $saveconsumptiondetailbreakdown->issued_out = $item->negative_adjustment;
                $saveconsumptiondetailbreakdown->issued_in = $item->positive_adjustment;
                $saveconsumptiondetailbreakdown->closing = $item->ending_balance;
                $saveconsumptiondetailbreakdown->requested = $item->request;
                $saveconsumptiondetailbreakdown->synched =  1;
                $saveconsumptiondetailbreakdown->datesynched = date('Y-m-d');
                $saveconsumptiondetailbreakdown->save();
            } else {
                Log::channel('consumption')->critical(json_encode($item));
            }
        }
        
        return true;
    }
}

// "id": 1,
// "national_id": null,
// "month": 1,
// "year": 2013,
// "type": 1,
// "machine": 2,
// "tests": 2725,
// "datesubmitted": "2013-02-18",
// "submittedby": null,
// "lab_id": 2,
// "comments": "",
// "issuedcomments": "",
// "approve": 1,
// "disapprovereason": "",
// "synched": 0,
// "datesynched": null,
// "created_at": "2020-06-01 23:19:55",
// "updated_at": "2020-06-01 23:19:55"

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Consumption;
use App\Lab;

class ConsumptionController extends Controller
{
    /**
     * The test types available.
     *
     * @var array
     */
    public $testtypes = NULL;

    /**
     * The months for allocations.
     *
     * @var array
     */
    public $consumption_months = NULL;

    /**
     * The last month of consumption.
     *
     * @var array
     */
    public $last_month = NULL;


    /**
     * The years for allocations.
     *
     * @var array
     */
    public $consumption_years = NULL;

    /**
     * The last year of consumption.
     *
     * @var array
     */
    public $last_year = NULL;

    /**
     * The years for allocations displayed.
     *
     * @var array
     */
    public $years = NULL;

    /**
     * The NHRL or EDARP user initialized.
     *
     * @var array
     */
    public $lab_id = NULL;

    public function __construct() {
        $this->testtypes = ['EID' => 1, 'VL' => 2, 'CONSUMABLES' => NULL];
        $this->years = [date('Y'), date('Y')-1];
        $this->last_month = date('m')-1;
        $this->last_year = date('Y');
        if (date('m') == 1) {
            $this->last_year -= 1;
            $this->last_month = 12;
        }
    }

    public function index($testtype = null)
    {
        $testtype = strtoupper($testtype);
        if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL' || $testtype == 'CONSUMABLES'))
            $testtype = 'EID';
        
        $labs = Lab::get();
        $consumptions_data = [];
        $consumptions = Consumption::whereIn('year', $this->years)->get();
        $this->consumption_years = $consumptions->unique('year')->pluck('year');
        $this->consumption_months = $consumptions->unique('month')->pluck('month');
        foreach ($this->consumption_years as $key => $year) {
            foreach ($this->consumption_months as $key => $month) {
                $filtered = $consumptions->where('year', $year)->where('month', $month);
                $consumption_labs = $filtered->count();
                $reviewed_labs = 0;
                foreach ($filtered as $lab_consumption) {
                    if ($lab_consumption->reviewed($testtype))
                        $reviewed_labs ++;
                }
                $consumptions_data[] = (object)[
                        'testtype' => strtolower($testtype),
                        'year' => $year,
                        'month' => $month,
                        'all_labs' => $labs->count(),
                        'consumption_labs' => $consumption_labs,
                        'approved_labs' => $reviewed_labs,
                    ];
            }
        }
        return view('tables.consumptions', compact('consumptions_data'))->with('pageTitle',"$testtype Consumption List");
    }

    public function view_consumption($testtype = null, $year = null, $month = null)
    {
        $testtype = strtoupper($testtype);
        if (!isset($testtype) || !($testtype == 'EID' || $testtype == 'VL' || $testtype == 'CONSUMABLES'))
            $testtype = 'EID';
        if (!isset($year))
            $year = $this->year[0];
        if (!isset($month))
            $month = date('m');
        $columntesttype = $this->testtypes[$testtype];
        $labs = Lab::with(array('consumptions' => function($query) use($year, $month) {
                        $query->where('consumptions.year', $year);
                        $query->where('consumptions.month', $month);
                    }, 'consumptions.details' => function($childQuery) use ($columntesttype) {
                            $childQuery->where('testtype', $columntesttype);
                    }))->get();
        
        $month_name = date("F", mktime(null, null, null, $month));
        $data = (object)['year' => $year, 'month' => $month, 'labs' => $labs, 'testtype' => $testtype];
        
        return view('tables.viewconsumptions', compact('data'))->with('pageTitle',"$testtype Consumption $month_name, $year");
    }

    public function history($consumption = NULL){
    	if (isset($consumption)){
    		$consumption = Consumption::find($consumption);

    		return view('tables.labconsumptionsdetails', compact('consumption'))->with('pageTitle', auth()->user()->lab->labdesc." Consumption Details");
    	} else {
    		$data['consumptions'] = Consumption::where('lab_id', auth()->user()->lab_id)->get();
	    	$data = (object) $data;
	    	return view('tables.labconsumptions', compact('data'))->with('pageTitle', auth()->user()->lab->labdesc." Consumption reports");
    	}
    }
}

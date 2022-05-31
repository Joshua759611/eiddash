<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use Mpdf\Mpdf;

use DB;
use \App\ViralsampleAlertView;
use \App\Lookup;
use stdClass;

class VlSummary extends Mailable
{
    use Queueable, SerializesModels;

    public $nonsup_absent;
    public $title;
    public $name;
    public $division;
    public $path;
    public $user_id;
    public $range;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        ini_set("memory_limit", "-1");

        $contact = DB::table('partner_facility_contacts')->where('id', $this->user_id)->get()->first();

        $startdate = date('Y-m-d', strtotime('-7 days'));
        $enddate = date('Y-m-d', strtotime('0 days'));
        // $startdate = date('Y-m-d', strtotime('-180 days'));
        // $enddate = date("Y-m-d", strtotime('-70 days'));

        $displayfromdate=date("d-M-Y",strtotime($startdate));
        $displaytodate=date("d-M-Y",strtotime($enddate));
        $range = strtoupper($displayfromdate . ' TO ' . $displaytodate);
        $this->range = $range;
               
        

        $fData = DB::table('labs')->select('*')
        ->where('id','<=','10')
        ->get()->map(function($key, $value) use ($startdate , $enddate) {
            $data_final = new stdClass();
            $data_final->key = $key->id;
            $data_final->name = $key->name;
            // dd($key);

            $data_final->tests  = ViralsampleAlertView::selectRaw("lab_name, lab_id, count(lab_id) as tests")
            ->where('lab_id', $key->id)
            ->where('lab_name' ,'!=', null)
            ->whereBetween('datetested', [$startdate, $enddate])
            ->groupBy('lab_id')
            ->orderBy('lab_name', 'asc')
            ->get()->map(function ($tests){
                return $tests->tests;
            });;

            $data_final->recieved = ViralsampleAlertView::selectRaw("lab_name, lab_id, count(lab_id) as recieved")
            ->where('lab_id', $key->id)
            ->where('lab_name' ,'!=', null)
            ->whereBetween('datereceived', [$startdate, $enddate])
            ->groupBy('lab_id')
            ->orderBy('lab_name', 'asc')
            ->get()->map(function ($recieved){
                return $recieved->recieved;
            });
            $data_final->untested = ViralsampleAlertView::selectRaw("lab_name, lab_id, count(lab_id) as untested")
            ->where('lab_id', $key->id)
            ->where('lab_name' ,'!=', null)
            ->whereBetween('datereceived', [$startdate, $enddate])
            ->whereNull('datetested')
            ->groupBy('lab_id')
            ->orderBy('lab_name', 'asc')
            ->get()->map(function ($untested){
                return $untested->untested;
            });
            $data_final->dispatch  = ViralsampleAlertView::selectRaw("lab_name, lab_id, count(lab_id) as dispatch")
            ->where('lab_id', $key->id)
            ->where('lab_name' ,'!=', null)
            ->whereBetween('datetested', [$startdate, $enddate])
            ->whereBetween('datedispatched', [$startdate, $enddate])
            ->groupBy('lab_id')
            ->orderBy('lab_name', 'asc')
            ->get()->map(function ($dispatch){
                return $dispatch->dispatch;
            });;
            // array_push($fdata,$data_final);
            // dd($data_final_recieved);
            // dd($data_final_tests);
            // dd($data_final_tests);
            // dd($data_final);
            return $data_final;

            


        });

        // dd($fData[0]->recieved[0],$fData[0]->tests)[0];

     



        
      

       
         $this->title = "SUMMARY OF HIV-VIRAL LOAD STATUS REPORT BETWEEN {$range} ";
         $this->name = DB::table('partner_facility_contacts')->where('id', $contact->id)->first()->name ?? '';
     

        $header = "<div align='center' style='text-align: center; align-content: center;'>
                        <img src=" . asset('img/naslogo.jpg') . " alt='NASCOP'>
                        <h3>MINISTRY OF HEALTH</h3>
                        <h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3> 
                    </div>";
                    

        if(!is_dir(storage_path('app/suppression/county'))) mkdir(storage_path('app/suppression/county'), 0777, true);

        $path = storage_path('app/suppression/county/' . $contact->id .   '.pdf');
        $this->path = $path;
        if(file_exists($path)) unlink($path);
        $pdf_data['title'] = $this->title; 
        $pdf_data['range'] = $range; 

        $mpdf = new Mpdf(['format' => 'A4-L']);
        
        $view_data = view('exports.summary', ['rows'=>$fData, 'title'=>$pdf_data['title']])->render();
        // $mpdf->SetHTMLHeader($header);
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($path, \Mpdf\Output\Destination::FILE);

        $this->attach($this->path, ['as' => $this->title . '.pdf']);
        return $this->subject($this->title)->view('mail.summary');
    }
}

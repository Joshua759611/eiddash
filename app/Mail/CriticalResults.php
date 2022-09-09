<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use Mpdf\Mpdf;

class CriticalResults extends Mailable
{
    use Queueable, SerializesModels;

    public $type;
    public $data;
    public $file_path;
    public $datedispatched;
    public $tests;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($type, $data, $datedispatched)
    {
        $this->type = $type;
        $this->data = $data;
        $this->datedispatched = $datedispatched;
        $this->file_path = storage_path('app/critical/' . $type . '_'.'.pdf');
        $this->tests = [
            'eid' => [
                'name' => 'EID',
            ],
            'vl' => [
                'name' => 'Viral Load',
            ],
        ];

        if(!is_dir(storage_path("app/critical/"))) mkdir(storage_path("app/critical/"), 0777, true);

        $mpdf = new Mpdf(['format' => 'A4-L']);
            $view_data = view('exports.mpdf_critical', ['summary' => $data, 'type' => $type])->render();
        $mpdf->WriteHTML($view_data);
        $mpdf->Output($this->file_path, \Mpdf\Output\Destination::FILE);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $t = strtoupper($this->type);
        $str = "Critical {$t} Results For {$this->datedispatched} to ".date("Y-m-d ");
        $this->attach($this->file_path, ['as' => $str]);
        return $this->subject($str)->view('emails.critical');
    }
}

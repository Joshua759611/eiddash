<?php

namespace App\Exports;

use DB;

use Maatwebsite\Excel\Excel;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QueryExport implements FromQuery, Responsable, WithHeadings
{
	use Exportable;

	protected $fileName;
	// protected $writerType = Excel::CSV;
	protected $writerType = Excel::XLSX;
	protected $sql;
	protected $eloquentQuery;
    protected $headings;

    public function __construct($eloquentQuery, $headings)
    {
        $this->eloquentQuery = $eloquentQuery;
        $this->headings = $headings;
    }

    public function headings() : array
    {
    	return $this->headings;
    }

    public function query()
    {
        return $this->eloquentQuery;
    }


}

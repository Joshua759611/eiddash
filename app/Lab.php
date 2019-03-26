<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lab extends Model
{
    //
    public $timestamps = false;

    public function getTokenNameAttribute()
    {
    	return 'lab_' . $this->id . '_token';
    }

    public function allocations(){
    	return $this->hasMany('App\Allocation');
    }

    public function allocation_contacts() {
        return $this->hasOne('App\AllocationContact');
    }

    // public function getallocations($testtype) {
    // 	$data = [];
    // 	foreach($this->allocations as $key => $allocations) {
    // 		if ($allocations->testtype == $testtype)
    // 			$data[] = $allocations;
    // 	}
    // 	return $data;
    // }
}

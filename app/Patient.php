<?php

namespace App;

use App\BaseModel;

class Patient extends BaseModel
{

    public function sample()
    {
    	return $this->hasMany('App\Sample');
    }

    public function mother()
    {
    	return $this->belongsTo('App\Mother');
    }

    public function scopeExisting($query, $facility_id, $hei_number)
    {
        return $query->where(['facility_id' => $facility_id, 'patient' => $hei_number]);
    }
}

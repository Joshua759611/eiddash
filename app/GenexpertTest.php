<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GenexpertTest extends Model
{

	protected $primaryKey = 'ID';
	protected $connection = 'node';
	protected $table = 'genexperttests';
    public $timestamps = false;

	protected $guarded = ['ID'];

	
    public function scopeActive($query)
    {
        return $query->where('active', 1);
    }
}

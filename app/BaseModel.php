<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    use \Venturecraft\Revisionable\RevisionableTrait;
    protected $revisionEnabled = true;
    // protected $revisionCleanup = true; 
    // protected $historyLimit = 500; 
    // protected $revisionCreationsEnabled = true;
    // protected $keepRevisionOf = [];
    protected $dontKeepRevisionOf = ['synched', 'datesynched', 'created_at', 'updated_at'];
    
    protected $guarded = ['id', 'created_at', 'updated_at', 'batch', 'kemri_id', 'national_sample_id', 'national_patient_id', 'national_batch_id', 'time_sent_to_edarp', 'edarp_error', 'tat5', 'time_received', 'areaname', 'label_id', 'sample_received_by'];



    /**
     * Get the sample's received status name
     *
     * @return string
     */
    public function getResultNameAttribute()
    {
        if($this->result == 1){ return "Negative"; }
        else if($this->result == 2){ return "Positive"; }
        else if($this->result == 3){ return "Failed"; }
        else if($this->result == 5){ return "Collect New Sample"; }
        else{ return ""; }
    }

    public function my_date_format($value=null, $format='d-M-Y')
    {
        if($this->$value) return date($format, strtotime($this->$value));

        return '';
    }

    public function my_string_format($value, $default='0')
    {
        if($this->$value) return (string) $this->$value;
        return $default;
    }


    public function pre_update()
    {
        if(($this->synched == 0 || $this->synched == 1) && $this->isDirty()) $this->synched = 2;
        $this->save();
    }

    public function pre_delete()
    {
        if($this->synched == 0 || $this->synched == 1){
            $this->synched = 3;
        }else{
            $this->delete();
        }        
    }
    

    public function get_prop_name($coll, $attr, $attr2='name')
    {
        if(!$this->$attr) return '';
        foreach ($coll as $value) {
            if($value->id == $this->$attr) return $value->$attr2;
        }
        return '';
    }
}

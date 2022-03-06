<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PartnerFacilityContactsChangeLog extends Model
{
    protected $fillable = ['partner_contact_id','name', 'email', 'telephone', 'critical_results', 'type', 'county_id', 'subcounty_id', 'partner_id', 'contact_change_date','contact_deleted_at','contact_updated_by'];
}

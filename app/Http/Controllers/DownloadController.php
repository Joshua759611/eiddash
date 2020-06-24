<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Resource;

class DownloadController extends Controller
{
	

    public function covid_sop(){
        $path = public_path('downloads/quarantine_site_sop.pdf');
        return response()->download($path, 'COVID-19 Quarantine Sites Remote Log In Job Aid.pdf');
    }

    public function covid(){
        $path = public_path('downloads/COVID-19_LRF_RB.pdf');
        return response()->download($path, 'Covid-19 LRF.pdf');
    }

	public function user_guide(){
    	$path = public_path('downloads/PartnerLoginUserGuide.pdf');
    	return response()->download($path);
    }

	public function consumption(){
    	$path = public_path('downloads/CONSUMPTION_GUIDE.pdf');
    	return response()->download($path);
    }

	public function hei(){
    	$path = public_path('downloads/HEIValidationToolGuide.pdf');
    	return response()->download($path);
    }

	public function poc(){
    	$path = public_path('downloads/POC_USERGUIDE.pdf');
    	return response()->download($path);
    }


	public function eid_req(){
    	$path = public_path('downloads/EID_REQUISITION_FORM.pdf');
    	return response()->download($path);
    }

	public function vl_req(){
    	$path = public_path('downloads/VL_REQUISITION_FORM.pdf');
    	return response()->download($path);
    }

    public function collection_guidelines(){
        $path = public_path('downloads/collection_manual.pdf');
        return response()->download($path, 'KEMRI Nairobi HIV Lab sample collection manual 2019.pdf');
    }

    public function api(){
        $path = public_path('downloads/Lab.postman_collection.json');
        return response()->download($path);
    }

    public function hit_api(){
        $path = public_path('downloads/HIT.postman_collection.json');
        return response()->download($path);
    }

    public function remotelogin() {
        $path = public_path('downloads/NASCOP_Remote_Login_SOP.pdf');
        return response()->download($path, 'NASCOP Lab Remote Login SOP.pdf');
    }

    public function resource($resource) {
        $extension = explode(".", $resource);
        if (is_array($extension)){
            $resourcedb = Resource::where('uri', $extension[0])->get();
            if (!$resourcedb->isEmpty()){
               $path = public_path('resource/'.$resource);
                return response()->download($path, $resourcedb->first()->name . '.' . $extension[1]); 
            }            
        }
        abort(404);
    }

}

<?php

namespace App\Http\Controllers;

use App\BlockedEmail;
use App\EidPartner;
use App\Partner;
use App\VlPartner;
use DB;
use Illuminate\Http\Request;
use App\Facility;
use App\Lookup;
use App\PartnerFacilityContact;
use App\PartnerFacilityContactsChangeLog;
use Illuminate\Support\Facades\Log;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $facilities = DB::table('facilitys')
            ->select('facilitys.id', 'facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as district', 'countys.name as county', 'facilitys.ftype', 'facilitys.telephone', 'facilitys.telephone2', 'facilitys.email', 'facilitys.contactperson', 'facilitys.PostalAddress', 'facilitys.contacttelephone', 'facilitys.contacttelephone2', 'facilitys.ContactEmail', 'partners.name as partner', 'facilitys.SMS_printer_phoneNo AS smsprinterphoneno', 'facilitys.G4Sbranchname', 'facilitys.G4Slocation')
            ->join('districts', 'districts.id', '=', 'facilitys.district')
            ->join('countys', 'countys.id', '=', 'districts.county')
            ->join('partners', 'partners.id', '=', 'facilitys.partner')
            ->where('facilitys.flag', '=', 1)
            ->get();
        $table = '';
        foreach ($facilities as $key => $value) {
            $table .= '<tr>';
            $table .= '<td>' . $value->facilitycode . '</td>';
            $table .= '<td>' . $value->facility . '</td>';
            $table .= '<td>' . $value->county . '</td>';
            $table .= '<td>' . $value->district . '</td>';
            $table .= '<td>' . $value->telephone . '</td>';
            $table .= '<td>' . $value->telephone2 . '</td>';
            $table .= '<td>' . $value->email . '</td>';
            $table .= '<td>' . $value->smsprinterphoneno . '</td>';
            $table .= '<td>' . $value->contactperson . '</td>';
            $table .= '<td>' . $value->contacttelephone . '</td>';
            $table .= '<td>' . $value->contacttelephone2 . '</td>';
            $table .= '<td>' . $value->ContactEmail . '</td>';
            $table .= '<td>' . $value->G4Sbranchname . '</td>';
            $table .= '<td><a href="' . route('facility.show', $value->id) . '">View</a>|<a href="' . route('facility.edit', $value->id) . '">Edit</a></td>';
            $table .= '</tr>';
        }
        $columns = parent::_columnBuilder(['MFL Code', 'Facility Name', 'County', 'Sub-county', 'Facility Phone 1', 'Facility Phone 2', 'Facility Email', 'Facility SMS Printer', 'Contact Person Names', 'Contact Phone 1', 'Contact Phone 2', 'Contact Email', 'G4S Branch', 'Task']);

        return view('tables.facilities', ['row' => $table, 'columns' => $columns])->with('pageTitle', 'Facilities');
    }

    public function served()
    {
        $facilities = DB::table('facilitys')
            ->select('facilitys.id', 'facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as district', 'countys.name as county', 'ftype', 'telephone', 'telephone2', 'facilitys.email', 'facilitys.contactperson', 'facilitys.PostalAddress', 'facilitys.contacttelephone', 'facilitys.contacttelephone2', 'facilitys.ContactEmail', 'partners.name as partner', 'facilitys.SMS_printer_phoneNo AS smsprinterphoneno', 'facilitys.G4Sbranchname', 'facilitys.G4Slocation')
            ->join('districts', 'districts.id', '=', 'facilitys.district')
            ->join('countys', 'countys.id', '=', 'districts.county')
            ->join('partners', 'partners.id', '=', 'facilitys.partner')
            ->where('facilitys.flag', '=', 1)
            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
            ->get();
        $count = 0;
        $table = '';
        foreach ($facilities as $key => $value) {
            $count++;
            $table .= '<tr>';
            $table .= '<td>' . $count . '</td>';
            $table .= '<td>' . $value->facilitycode . '</td>';
            $table .= '<td>' . $value->facility . '</td>';
            $table .= '<td>' . $value->county . '</td>';
            $table .= '<td>' . $value->district . '</td>';
            $table .= '<td>' . $value->telephone . '</td>';
            $table .= '<td>' . $value->email . '</td>';
            $table .= '<td>' . $value->contactperson . '</td>';
            $table .= '<td>' . $value->contacttelephone . '</td>';
            $table .= '<td>' . $value->ContactEmail . '</td>';
            $table .= '<td>' . $value->partner . '</td>';
            $table .= '</tr>';
        }
        $columns = parent::_columnBuilder(['#', 'MFL Code', 'Facility Name', 'County', 'Sub-county', 'Mobile', 'Email Address',
            'Contact Person', 'CP Telephone', 'CP Email', 'Supporting Partner']);

        return view('tables.facilities', ['row' => $table, 'columns' => $columns])->with('pageTitle', 'Facilities Served');
    }

    public function smsprinters()
    {
        $facilities = DB::table('facilitys')
            ->select('facilitys.id', 'facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as district', 'countys.name as county', 'ftype', 'telephone', 'telephone2', 'facilitys.email', 'facilitys.contactperson', 'facilitys.PostalAddress', 'facilitys.contacttelephone', 'facilitys.contacttelephone2', 'facilitys.ContactEmail', 'partners.name as partner', 'facilitys.SMS_printer_phoneNo AS smsprinterphoneno', 'facilitys.serviceprovider')
            ->join('districts', 'districts.id', '=', 'facilitys.district')
            ->join('countys', 'countys.id', '=', 'districts.county')
            ->join('partners', 'partners.id', '=', 'facilitys.partner')
            ->where('facilitys.flag', '=', 1)
            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
            ->where('facilitys.smsprinter', '<>', '')
            ->get();

        $columns = parent::_columnBuilder(['#', 'MFL Code', 'Facility Name', 'County', 'Sub-county', 'Email Address',
            'Contact Person', 'CP Telephone', 'CP Email', 'Supporting Partner', 'SMS Printer No.', 'Service Provider']);

        $count = 0;
        $table = '';
        foreach ($facilities as $key => $value) {
            $count++;
            $table .= '<tr>';
            $table .= '<td>' . $count . '</td>';
            $table .= '<td>' . $value->facilitycode . '</td>';
            $table .= '<td>' . $value->facility . '</td>';
            $table .= '<td>' . $value->county . '</td>';
            $table .= '<td>' . $value->district . '</td>';
            $table .= '<td>' . $value->email . '</td>';
            $table .= '<td>' . $value->contactperson . '</td>';
            $table .= '<td>' . $value->contacttelephone . '</td>';
            $table .= '<td>' . $value->ContactEmail . '</td>';
            $table .= '<td>' . $value->partner . '</td>';
            $table .= '<td>' . $value->smsprinterphoneno . '</td>';
            $table .= '<td>' . $value->serviceprovider . '</td>';
            $table .= '</tr>';
        }

        return view('tables.facilities', ['row' => $table, 'columns' => $columns])->with('pageTitle', 'With SMS Printers');
    }

    public function withoutemails()
    {
        $columns = parent::_columnBuilder(['Facility Code', 'Facility Name', 'Mobile No', 'Email Address', 'Contact Person', 'CP Telephone', 'CP Email']);

        $facilities = DB::table('facilitys')
            ->select('facilitys.id', 'facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as district', 'countys.name as county', 'partners.name as partner', 'ftype', 'telephone', 'telephone2', 'facilitys.email', 'facilitys.contactperson', 'facilitys.PostalAddress', 'facilitys.contacttelephone', 'facilitys.contacttelephone2', 'facilitys.ContactEmail', 'partners.name as partner', 'facilitys.SMS_printer_phoneNo AS smsprinterphoneno', 'facilitys.serviceprovider')
            ->join('districts', 'districts.id', '=', 'facilitys.district')
            ->join('countys', 'countys.id', '=', 'districts.county')
            ->join('partners', 'partners.id', '=', 'facilitys.partner')
            ->where('facilitys.flag', '=', 1)
            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
            ->whereRaw("((facilitys.email = '' and facilitys.ContactEmail ='') or (facilitys.email = '' and facilitys.ContactEmail is null) or (facilitys.email is null and facilitys.ContactEmail ='') or ((facilitys.email is null and facilitys.ContactEmail is null)))")
            ->get();
        // dd($facilities);
        $table = '';
        foreach ($facilities as $key => $value) {
            $table .= '<tr>';
            $table .= '<td>' . $value->facilitycode . '</td>';
            $table .= '<td>' . $value->facility . '</td>';
            $table .= '<td>' . $value->telephone . '</td>';
            $table .= '<td>
                            <input type="hidden" name="id[]" value="' . $value->id . '">
                            <input type="text" class="form-control m-b input-sm" size="20" name="email[]" value="' . $value->email . '">
                        </td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="contactperson[]" value="' . $value->contactperson . '"></td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="contacttelephone[]" value="' . $value->contacttelephone . '"></td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="ContactEmail[]" value="' . $value->ContactEmail . '"></td>';
            $table .= '</tr>';
        }
        return view('tables.editable', ['row' => $table, 'columns' => $columns, 'function' => 'update'])->with('pageTitle', 'Without Emails');
    }

    public function withoutG4S()
    {
        $columns = parent::_columnBuilder(['Facility Code', 'Facility Name', 'County', 'Sub-county', 'G4S Branch Name', 'G4S Branch Location']);

        $facilities = DB::table('facilitys')
            ->select('facilitys.id', 'facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as district', 'countys.name as county', 'facilitys.G4Sbranchname', 'facilitys.G4Slocation')
            ->join('districts', 'districts.id', '=', 'facilitys.district')
            ->join('countys', 'countys.id', '=', 'districts.county')
            ->join('partners', 'partners.id', '=', 'facilitys.partner')
            ->where('facilitys.flag', '=', 1)
            ->where('facilitys.lab', '=', Auth()->user()->lab_id)
            ->where('G4Sbranchname', '=', '')
            ->where('G4Slocation', '=', '')
            ->get();
        $table = '';
        foreach ($facilities as $key => $value) {
            $table .= '<tr>';
            $table .= '<td>' . $value->facilitycode . '</td>';
            $table .= '<td>' . $value->facility . '</td>';
            $table .= '<td>' . $value->county . '</td>';
            $table .= '<td>' . $value->district . '</td>';
            $table .= '<td>
                            <input type="hidden" name="id[]" value="' . $value->id . '">
                            <input type="text" class="form-control m-b input-sm" size="20" name="G4Sbranchname[]" value="' . $value->G4Sbranchname . '">
                        </td>';
            $table .= '<td><input type="text" class="form-control m-b input-sm" size="20" name="G4Slocation[]" value="' . $value->G4Slocation . '"></td>';
            $table .= '</tr>';
        }
        return view('tables.editable', ['row' => $table, 'columns' => $columns, 'function' => 'update'])->with('pageTitle', 'Without G4S');
    }

    /**
     * A listing of all partners who support various facilities
     **/
    public function partners()
    {
        $partners = DB::table("partners")->get();
        $columns = parent::_columnBuilder(['Name', 'Funding Agency', 'Status', 'Action']);
        $table = '';
        if ($partners->isEmpty()) {
            $table = '<tr><th colspan="7"><center>No Contact Available</center></th></tr>';
        }
        foreach ($partners as $key => $p) {
            $table .= '<tr>';
            $table .= '<td>' . $p->name ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $p->fundingagency ?? '<strong>N/A</strong>' . '</td>';
            if ($p->flag == 0) {
                $table .= '<td><strong>Inactive</strong></td>';
                $table .= '<td><a title="Partner will be activated to alerts and assigned facilities" href="' . env('APP_URL') . '/enablepartner/' . $p->id . '"  class="btn btn-default" style="color: green;"> Activate </a></td>';

            } else {
                $table .= '<td><strong>Active</strong></td>';
                $table .= '<td><a title="Once deactivated  partner staff will not receive alerts" href="' . env('APP_URL') . '/disablepartner/' . $p->id . '" class="btn btn-default" style="color: orangered;"> Deactivate</a></td>';

            }
            $table .= '</tr>';
        }

        return view('tables.editable', [
            'row' => $table,
            'columns' => $columns,
            'create_endpoint' => 'createpartnercontacts',
            'create_text' => 'Add Partner',
            'function' => 'update'
        ])->with('pageTitle', 'Nascop partners');

    }

    public function disablepartner($id)
    {
        $partner = Partner::find($id);
        $partner->flag = 0;
        $partner->save();

        $p_eid_alerts = EidPartner::where("partner", $id)->first();
        if ($p_eid_alerts) {
            $p_eid_alerts->active = 0;
            $p_eid_alerts->save();
        }
        $p_vl_alerts = VlPartner::where("partner", $id)->first();
        if ($p_vl_alerts) {
            $p_vl_alerts->active = 0;
            $p_vl_alerts->save();
        }

        return redirect()->route('partners');
    }

    public function enablepartner($id)
    {
        $partner = Partner::find($id);
        $partner->flag = 1;
        $partner->save();

        $p_eid_alerts = EidPartner::where("partner", $id)->first();
        if ($p_eid_alerts) {
            $p_eid_alerts->active = 1;
            $p_eid_alerts->save();
        }

        //TODO apply same action to a facility assosiated to the partner
        $p_vl_alerts = VlPartner::where("partner", $id)->first();
        if ($p_eid_alerts) {
            $p_vl_alerts->active = 2;
            $p_vl_alerts->save();
        }

        return redirect()->route('partners');
    }

    public function partnercontacts()
    {
        $columns = parent::_columnBuilder(['Name', 'Email', 'Mobile No', 'County', 'Sub-County', 'Partner', 'Critical Results', 'EID Results', 'VL Results', 'Edit', 'Action']);
        $partners = PartnerFacilityContact::withTrashed()->get();
        if ($partners->isEmpty()) {
            $table = '<tr><th colspan="7"><center>No Contact Available</center></th></tr>';
        }

        $table = '';
        foreach ($partners as $key => $partner) {
            $county_label = $partner->county->name ?? '<strong>N/A</strong>';
            $subcounty_label = $partner->subcounty->name ?? '<strong>N/A</strong>';
            $partner_label = $partner->partner->name ?? '<strong>N/A</strong>';
            $critical_results_label = "<label class'label label-danger'>Not Allowed</label>";
            if ($partner->critical_results)
                $critical_results_label = "<label class'label label-success'>Allowed</label>";
            $eid_results_label = "<label class'label label-danger'>Not Allowed</label>";
            if ($partner->eid_results)
                $eid_results_label = "<label class'label label-success'>Allowed</label>";
            $vl_results_label = "<label class'label label-danger'>Not Allowed</label>";
            if ($partner->vl_results)
                $vl_results_label = "<label class'label label-success'>Allowed</label>";
            $table .= '<tr>';
            $table .= '<td>' . $partner->name ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $partner->email ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $partner->telephone ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $county_label . '</td>';
            $table .= '<td>' . $subcounty_label . '</td>';
            $table .= '<td>' . $partner_label . '</td>';
            $table .= '<td>' . $critical_results_label . '</td>';
            $table .= '<td>' . $eid_results_label . '</td>';
            $table .= '<td>' . $vl_results_label . '</td>';
            if ($partner->deleted_at == NULL) {
                $table .= '<td><a href="' . env('APP_URL') . '/updatepartnercontacts/' . $partner->id . '" class="btn btn-default">Edit</a></td>';
            } else {
                $table .= '<td><a href="' . env('APP_URL') . '/updatepartnercontacts/' . $partner->id . '" class="btn btn-default disabled">Edit</a></td>';
            }
            if ($partner->deleted_at == NULL) {
                $table .= '<td><a title="Once deactivated the user will not be able to receive alerts" href="' . env('APP_URL') . '/disable_notification/' . $partner->id . '" class="btn btn-default" style="color: orangered;"> Deactivate</a></td>';
            } else {
                $table .= '<td><a title="Activate this user account to get alearts" href="' . env('APP_URL') . '/enable_notification/' . $partner->id . '"  class="btn btn-default" style="color: green;"> Activate </a></td>';
            }
            $table .= '</tr>';
        }
        return view('tables.editable', [
            'row' => $table,
            'columns' => $columns,
            'create_endpoint' => 'createpartnercontacts',
            'create_text' => 'Create Partner Facility Contact',
            'function' => 'update'
        ])->with('pageTitle', 'Partner Facility Contacts');
    }

    public function vlAlertsEmails()
    {
        return self::contacts_for_alerts('vl', "VL alert Emails");
    }

    public function eidAlertsEmails()
    {
        return self::contacts_for_alerts('eid', "EID alert Emails");
    }

    public function createpartnercontacts(Request $request)
    {
        if ($request->method() == 'GET') {
            $data = [
                'counties' => DB::table('countys')->get(),
                'subcounties' => DB::table('districts')->get(),
                'partners' => DB::table('partners')->get(),
            ];
            return view('forms.partnercontacts', $data)->with('pageTitle', 'Create Partner Facility Contact');
        } else {
            $validate = $request->validate([
                'name' => 'required',
                'email' => 'required|unique:partner_facility_contacts',
            ]);
            $form_data = $request->only(['name', 'email', 'telephone', 'critical_results', 'eid_results', 'vl_results', 'type', 'county_id', 'subcounty_id', 'partner_id']);
            $contact = new PartnerFacilityContact;
            $contact->fill($form_data);
            $contact->save();

            return redirect()->route('partnercontacts');
        }
    }

    public function updatepartnercontacts($id, Request $request)
    {
        if ($request->method() == 'GET') {
            $data = [
                'counties' => DB::table('countys')->get(),
                'subcounties' => DB::table('districts')->get(),
                'partners' => DB::table('partners')->get(),
                'contact' => PartnerFacilityContact::find($id),
            ];
            return view('forms.partnercontacts', $data)->with('pageTitle', 'Update Partner Facility Contact');
        } else {
            $validate = $request->validate([
                'name' => 'required',
                'email' => 'required',
            ]);
            $form_data = $request->only(['name', 'email', 'telephone', 'critical_results', 'eid_results', 'vl_results', 'type', 'county_id', 'subcounty_id', 'partner_id']);
            $contact = PartnerFacilityContact::find($id);
            $contact->fill($form_data);
            $contact->save();


            PartnerFacilityContactsChangeLog::create([
                'partner_contact_id' => $contact['id'],
                'county_id' => $contact['county_id'],
                'subcounty_id' => $contact['subcounty_id'],
                'partner_id' => $contact['partner_id'],
                'name' => $contact['name'],
                'email' => $contact['email'],
                'telephone' => $contact['telephone'],
                'type' => $contact['type'],
                'critical_results' => $contact['critical_results'],
                'eid_results' => $contact['eid_results'],
                'vl_results' => $contact['vl_results'],
                'contact_change_date' => $contact['updated_at'],
                'contact_deleted_at' => $contact['deleted_at'],
                'contact_updated_by' => auth()->user()->id,
            ]);

            return redirect()->route('partnercontacts');
        }

    }

    /**
     * function can help extract mails from the hr growing table on db as quick fix
     */
    public function contacts_for_alerts($testType, $title)
    {
        $partner_contacts = [];
        if ($testType === 'vl') {
            $partner_contacts = DB::table('vl_partner_contacts_for_alerts')->where('active', '=', 2)->get();

        } else {
            if ($testType === 'eid') {
                $partner_contacts = DB::table('eid_partner_contacts_for_alerts')->where('active', '=', 1)->get();

            }
        }
        $cc_array = [];
        $bcc_array = [];
        foreach ($partner_contacts as $key => $contact) {

            foreach ($contact as $column_name => $value) {
                $value = trim($value);

                // Check if email address is blocked
                if (self::my_string_contains($column_name, ['ccc', 'bcc', 'mainrecipientmail'])) {
                    $b = DB::table('blocked_emails')->where('email', '=', $value)->first();
                    if ($b) {
                        if ($testType == "vl") {
                            DB::table('vl_partner_contacts_for_alerts')
                                ->where("$column_name", $value)
                                ->update(["$column_name" => null]);
                        } else {
                            DB::table('eid_partner_contacts_for_alerts')
                                ->where("$column_name", $value)
                                ->update(["$column_name" => null]);
                        }
                        Log::info("Removed blocked email {$value} \n");
                        continue;
                    }
                }
                $partner_name = DB::table('partners')->where('id', '=', $contact->partner)->first()->name ?? '';
                $county = DB::table('countys')->where('id', '=', $contact->county)->first()->name ?? '';

                if (self::my_string_contains($column_name, ['ccc', 'mainrecipientmail']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $cc_array[] = [trim($value), $contact->partner, $partner_name, $county, $contact->active, $contact->lastalertsent];
                if (self::my_string_contains($column_name, ['bcc']) && filter_var($value, FILTER_VALIDATE_EMAIL) && !self::my_string_contains($value, ['jbatuka'])) $bcc_array[] = trim($value);
            }

        }

        $table = '';
        $columns = parent::_columnBuilder(['Email', 'Partner', 'County', 'Notification Status', 'Last alert sent', 'Action']);

        foreach ($cc_array as $k_arr => $cc_val) {
            $table .= '<tr>';
            $table .= '<td>' . $cc_val[0] ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $cc_val[2] ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $cc_val[3] ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $cc_val[4] ?? '<strong>N/A</strong>' . '</td>';
            $table .= '<td>' . $cc_val[5] ?? '<strong>N/A</strong>' . '</td>';
            $statusChange = "<a href='" . url("blockAlerts/$cc_val[2]") . "'>Activate</a>";

            $table .= '<td><a href=' . url("blockAlerts/$cc_val[0]") . ' title="Once deactivated the user will be blocked from receiving alerts"  class="btn btn-default" style="color: orangered;"> Delete</a></td>';
            $table .= '</tr>';
        }
        return view('tables.editable', [
            'row' => $table,
            'columns' => $columns,
            'create_endpoint' => 'createpartnercontacts',
            'create_text' => 'Create Partner Facility Contact',
            'function' => 'update'
        ])->with('pageTitle', $title);

    }

    public function my_string_contains($str, $search_array)
    {
        if (!is_array($search_array)) return str_contains($str, $search_array);
        foreach ($search_array as $value) {
            if (str_contains($str, $value)) return true;
        }
        return false;
    }

    /**
     * push the select email into the blocked emails
     * @param $alertEmail
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deleteAlertEmailAddress($alertEmail)
    {
        BlockedEmail::create([
            'email' => $alertEmail,
        ]);

        return back();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $facilitytype = DB::table('facilitytype')->get();
        $districts = DB::table('districts')->get();
        $wards = DB::table('wards')->get();
        $partners = DB::table('partners')->get();

        return view('forms.facility', compact('facilitytype', 'districts', 'wards', 'partners'))->with('pageTitle', 'Add Facility');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    public function getFacility($id)
    {
        // return DB::table('facilitys')->select('facilitys.id','facilitys.facilitycode', 'facilitys.name as facility', 'districts.name as subcounty', 'countys.name as county', 'labs.name as lab','facilitys.physicaladdress', 'facilitys.PostalAddress','facilitys.telephone', 'facilitys.telephone2', 'facilitys.fax','facilitys.email', 'facilitys.contactperson', 'facilitys.ContactEmail', 'facilitys.contacttelephone', 'facilitys.contacttelephone2','facilitys.smsprinterphoneno', 'facilitys.G4Sbranchname', 'facilitys.G4Slocation', 'facilitys.G4Sphone1', 'facilitys.G4Sphone2', 'facilitys.G4Sphone3', 'facilitys.G4Sfax')
        //                 ->join('labs' ,'labs.id', '=', 'facilitys.lab')
        //                 ->join('districts', 'districts.id', '=', 'facilitys.district')
        //                 ->join('view_facilitys', 'view_facilitys.id', '=', 'facilitys.id')
        //                 ->join('countys', 'countys.id', '=', 'view_facilitys.county')
        //                 ->where('facilitys.id', '=', $id)
        //                 ->get();

        return DB::table('view_facilitys')->select('*', 'name as facility')->where('id', $id)->get();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $facility = Facility::find($id);
        // dd($facility);
        $facility = $this->getFacility($id);
        // dd($facility);
        return view('facilities.facility', ['facility' => $facility[0], 'disabled' => 'disabled'])->with('pageTitle', 'Facilities');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $facility = $this->getFacility($id);
        // dd($facility[0]);
        return view('facilities.facility', ['facility' => $facility[0], 'disabled' => ''])
            ->with('edit', true)
            ->with('pageTitle', 'Facilities');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id = null)
    {
        $id = $request->id;
        $success = 'Update was sucessfull';
        $failed = 'Updated failed try again later';
        // $this->validate($request, [
        //     'facilitycode' => 'required',
        //     'name' => 'required',
        //     'subconty' => 'required',
        //     'county' => 'required',
        //     'lab' => 'required',
        // ]);
        // dd($request->contacttelephone[199]);

        if (gettype($request->id) == "array") {//From the bulk update views
            if (isset($request->G4Sbranchname) || isset($request->G4Slocation)) {// update the G4S details
                foreach ($request->id as $key => $value) {
                    $data = ['G4Sbranchname' => $request->G4Sbranchname[$key], 'G4Slocation' => $request->G4Slocation[$key]];

                    $update = DB::table('facilitys')
                        ->where('id', $request->id[$key])
                        ->update($data);
                }
                if ($update) {
                    return redirect()->route('withoutG4S')
                        ->with('success', $success);
                } else {
                    return redirect()->route('withoutG4S')
                        ->with('failed', $failed);
                }
            } else { //Updating the facilities contact details
                foreach ($request->id as $key => $value) {
                    $data = ['email' => $request->email[$key], 'contactperson' => $request->contactperson[$key],
                        'contacttelephone' => $request->contacttelephone[$key], 'ContactEmail' => $request->ContactEmail[$key]];
                    $update = DB::table('facilitys')
                        ->where('id', $request->id[$key])
                        ->update($data);
                }
                if ($update) {
                    return redirect()->route('withoutemails')
                        ->with('success', $success);
                } else {
                    return redirect()->route('withoutemails')
                        ->with('failed', $failed);
                }
            }
        } else {//From the single row update views
            $data = ['facilitycode' => $request->facilitycode, 'name' => $request->name,
                'PostalAddress' => $request->PostalAddress, 'physicaladdress' => $request->physicaladdress,
                'telephone' => $request->telephone, 'fax' => $request->fax,
                'telephone2' => $request->telephone2, 'email' => $request->email,
                'smsprinterphoneno' => $request->smsprinterphoneno, 'contactperson' => $request->contactperson,
                'contacttelephone' => $request->contacttelephone, 'ContactEmail' => $request->ContactEmail,
                'contacttelephone2' => $request->contacttelephone2, 'G4Sbranchname' => $request->G4Sbranchname,
                'G4Sphone1' => $request->G4Sphone1, 'G4Sphone3' => $request->G4Sphone3,
                'G4Slocation' => $request->G4Slocation, 'G4Sphone2' => $request->G4Sphone2, 'G4Sfax' => $request->G4Sfax];
            $update = DB::table('facilitys')
                ->where('id', $id)
                ->update($data);
            if ($update) {
                return redirect()->route('facility.index')
                    ->with('success', $success);
            } else {
                return redirect()->route('facility.index')
                    ->with('failed', $failed);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $facilities = \App\ViewFacility::select('ID as id', 'name', 'facilitycode', 'county')
            ->whereRaw("(name like '%" . $search . "%' OR  facilitycode like '" . $search . "%')")
            ->paginate(10);
        return $facilities;
    }

    public function disable_notification($id)
    {
        $contact = PartnerFacilityContact::where('id', $id)->first()->get();
        PartnerFacilityContact::where('id', $id)->delete();

        PartnerFacilityContactsChangeLog::create([
            'partner_contact_id' => $contact[0]->id,
            'county_id' => $contact[0]->county_id,
            'subcounty_id' => $contact[0]->subcounty_id,
            'partner_id' => $contact[0]->partner_id,
            'name' => $contact[0]->name,
            'email' => $contact[0]->email,
            'telephone' => $contact[0]->telephone,
            'type' => $contact[0]->type,
            'critical_results' => $contact[0]->critical_results,
            'eid_results' => $contact[0]->eid_results,
            'vl_results' => $contact[0]->vl_results,
            'contact_change_date' => $contact[0]->updated_at,
            'contact_deleted_at' => $contact[0]->deleted_at,
            'contact_updated_by' => Auth()->user(),
        ]);

        return redirect()->route('partnercontacts');
    }

    public function enable_notification($id)
    {
        PartnerFacilityContact::withTrashed()->find($id)->restore();

        return redirect()->route('partnercontacts');
    }
}





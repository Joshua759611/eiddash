@extends('layouts.master')

@component('/forms/css')
    
@endcomponent

@section('css_scripts')

@endsection

@section('custom_css')
    <style type="text/css">
        .form-horizontal .control-label {
                text-align: left;
            }
        
    </style>
@endsection

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            @php
                // dd($contact);
            @endphp
        @isset($contact)
            {{ Form::open(['url' => '/updatepartnercontacts/' . $contact->id, 'method' => 'put', 'class'=>'form-horizontal']) }}
        @else
            {{ Form::open(['url' => '/createpartnercontacts/', 'method' => 'post', 'class'=>'form-horizontal']) }}
        @endisset
            <div class="hpanel">
                <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                    <center>Contact Information</center>
                </div>
                <div class="panel-body" style="padding-bottom: 6px;">
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Contact Name</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="name" id="name" type="text" value="{{ $contact->name ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Contact Email</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="email" id="email" type="email" value="{{ $contact->email ?? '' }}" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Contact Phone</label>
                        <div class="col-sm-8">
                            <input class="form-control" name="telephone" id="telephone" type="text" value="{{ $contact->telephone ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Notifaction Type</label>
                        <div class="col-sm-8">
                            <label class="control-label">Critical Results</label>
                            <input type="checkbox" class="i-checks" name="critical_results" value="1" 
                                @if(isset($contact) && $contact->critical_results)
                                    checked
                                @endif
                             />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Recepient Type</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="type" id="type" required="true">
                                <option @isset($contact) @else selected="true" @endisset disabled="true">Select Type</option>
                                <option value="Recepient" @if(isset($contact) && $contact->type == 'Recepient') selected="true" @endif>RECEPIENT</option>
                                <option value="Cc" @if(isset($contact) && $contact->type == 'Cc') selected="true" @endif>CC</option>
                                <option value="Bcc" @if(isset($contact) && $contact->type == 'Bcc') selected="true" @endif>BCC</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 control-label">Partner Type</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="partner_type" id="partner_type" required="true">
                                <option @isset($contact) @else selected="true" @endisset disabled="true">Select Partner Type</option>
                                <option value="county" @if(isset($contact) && $contact->county_id) selected="true" @endif>COUNTY</option>
                                <option value="subcounty" @if(isset($contact) && $contact->subcounty_id) selected="true" @endif>SUB-COUNTY</option>
                                <option value="partner" @if(isset($contact) && $contact->partner_id) selected="true" @endif>PARTNER</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="county">
                        <label class="col-sm-4 control-label">County</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="county_id" id="county_id">
                                <option @if(isset($contact) && $contact->county_id) @else selected="true" @endif disabled="true">Select County</option>
                                @foreach($counties as $county)
                                    <option value="{{ $county->id }}"
                                        @if(isset($contact) && $contact->county_id == $county->id) 
                                            selected="true"
                                        @endif>{{ $county->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="subcounty">
                        <label class="col-sm-4 control-label">Sub-County</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="subcounty_id" id="subcounty_id">
                                <option @if(isset($contact) && $contact->subcounty_id) @else selected="true" @endif disabled="true">Select Sub-County</option>
                                @foreach($subcounties as $subcounty)
                                    <option value="{{ $subcounty->id }}"
                                        @if(isset($contact) && $contact->subcounty_id == $subcounty->id) 
                                            selected="true"
                                        @endif>{{ $subcounty->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="partner">
                        <label class="col-sm-4 control-label">Partner</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="partner_id" id="partner_id">
                                <option @if(isset($contact) && $contact->partner_id) @else selected="true" @endif disabled="true">Select Partner</option>
                                @foreach($partners as $partner)
                                    <option value="{{ $partner->id }}"
                                        @if(isset($contact) && $contact->partner_id == $partner->id) 
                                            selected="true"
                                        @endif>{{ $partner->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>           
            </div>
            <center>
                <button type="submit" class="btn btn-primary btn-lg" style="margin-top: 2em;margin-bottom: 2em; width: 200px; height: 30px;">Save Partner Contact Facility</button>
            </center>
        {{ Form::close() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot
        
    @endcomponent
    <script type="text/javascript">
        $("#county").hide();
        $("#subcounty").hide();
        $("#partner").hide();

        @if(isset($contact) && $contact->county_id)
            $("#county").show();
        @endif
        @if(isset($contact) && $contact->subcounty_id)
            $("#subcounty").show();
        @endif
        @if(isset($contact) && $contact->partner_id)
            $("#partner").show();
        @endif

        $("#partner_type").change(function(){
            let type = $(this).val();

            if (type == 'county') {
                $("#subcounty").hide();
                $("#partner").hide();
                $("#county").show();
            }
            if (type == 'subcounty') {
                $("#subcounty").show();
                $("#county").hide();
                $("#partner").hide();
            }
            if (type == 'partner') {
                $("#partner").show();
                $("#county").hide();
                $("#subcounty").hide();
            }
        });
    </script>
   
@endsection
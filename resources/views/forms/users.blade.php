@extends('layouts.master')

@component('/forms/css')
    
@endcomponent

@section('custom_css')
    <style type="text/css">
        .hpanel {
            margin-bottom: 4px;
        }
        .hpanel.panel-heading {
            padding-bottom: 2px;
            padding-top: 4px;
        }
    </style>
@endsection

@section('content')
    <div class="content">
        <div>
            {{ Form::open(['url' => '/user', 'method' => 'post', 'class'=>'form-horizontal']) }}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="hpanel">
                            <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                                <center>Account Information</center>
                            </div>
                            <div class="panel-body" style="padding-bottom: 6px;">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Account Type</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" required name="user_type" id="user_type">
                                            <option value="" selected disabled>Select Account Type</option>
                                        @forelse ($data->accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->user_type }}</option>
                                        @empty
                                            <option value="" disabled="true">No Account types available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>
                                @if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 10)
                                <div class="form-group" id="partners">
                                    <label class="col-sm-4 control-label">Partner</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="level" id="partner_select">
                                            <option value="" selected disabled>Select Partner</option>
                                        @forelse ($data->partners as $partner)
                                            @if(!in_array($partner->id, [77,82,84,85,80,93]))
                                                <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                            @endif
                                        @empty
                                            <option value="" disabled="true">No Partners available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" id="super_partners">
                                    <label class="col-sm-4 control-label">Super Partner</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="level" id="super_partner_select">
                                            <option value="" selected disabled>Select Super Partner</option>
                                        @forelse ($data->partners as $partner)
                                            @if(in_array($partner->id, [77,82,84,85,80,93]))
                                                <option value="{{ $partner->id }}">{{ $partner->name }}</option>
                                            @endif
                                        @empty
                                            <option value="" disabled="true">No Partners available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group" id="countys">
                                    <label class="col-sm-4 control-label">County</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="level" id="partner_select">
                                            <option value="" selected disabled>Select County</option>
                                        @forelse ($data->countys as $county)
                                            <option value="{{ $county->id }}">{{ $county->name }}</option>
                                        @empty
                                            <option value="" disabled="true">No Countys available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>
                                @endif
                                @if(Auth::user()->user_type_id == 1 || Auth::user()->user_type_id == 4 || Auth::user()->user_type_id == 10)
                                <div class="form-group" id="subcounty">
                                    <label class="col-sm-4 control-label">Sub-County</label>
                                    <div class="col-sm-8">
                                        <select class="form-control" name="level" id="subcounty" style="width: 100%;">
                                            <option value="" selected disabled>Select Sub-County</option>
                                        @forelse ($data->subcountys as $subcounty)
                                            <option value="{{ $subcounty->id }}">{{ $subcounty->name }}</option>
                                        @empty
                                            <option value="" disabled="true">No Sub-County available</option>
                                        @endforelse
                                        </select>
                                    </div>
                                </div>
                                @endif
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Email</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="email" id="email" type="email" value="{{ $user->email ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Username</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="username" id="username" type="username" value="{{ $user->username ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Password</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="password" id="password" type="password" value="">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Confirm Password</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="confirm-password" id="confirm-password" type="password" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hpanel">
                            <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                                <center>Personal Information</center>
                            </div>
                            <div class="panel-body" style="padding-bottom: 6px;">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Surname</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="surname" id="surname" type="text" value="{{ $user->surname ?? '' }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Other Name(s)</label>
                                    <div class="col-sm-8">
                                        <input class="form-control" name="oname" id="oname" type="text" value="{{ $user->oname ?? '' }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hpanel">
                            <div class="panel-heading" style="padding-bottom: 2px;padding-top: 4px;">
                                <center>Contact Details</center>
                            </div>
                            <div class="panel-body" style="padding-bottom: 6px;">
                                <div class="form-group">
                                    <label class="col-sm-4 control-label">Telephone No.</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                            <input class="form-control" name="telephone" id="telephone" type="text" value="{{ $user->telephone ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="hr-line-dashed"></div>
                        <div class="form-group">
                            <center>
                                <div class="col-sm-10 col-sm-offset-1">
                                    <button class="btn btn-success submit" type="submit" name="submit_type" value="release">Save User</button>
                                    <button class="btn btn-primary submit" type="submit" name="submit_type" value="add">Save & Add User</button>
                                    <button class="btn btn-danger" type="reset" formnovalidate name="submit_type" value="cancel">Reset</button>
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
            {{ Form::close() }}
        </div>
    </div>
@endsection

@section('scripts')

    @component('/forms/scripts')
        @slot('js_scripts')
            
        @endslot

        @slot('val_rules')
           
        @endslot

    @endcomponent


    <script type="text/javascript">
        $(document).ready(function(){
            $("#partners").hide();
            $("#super_partners").hide();
            $("#countys").hide();
            $("#subcounty").hide();

            $(".submit").click(function(e){
                password = $("#password").val();
                confirm = $("#confirm-password").val();
                if (password !== confirm) {
                    e.preventDefault();
                    set_warning("Passwords do not match");
                    $("#confirm-password").val("");
                    $("#confirm-password").focus();
                }
            });

            $("#email").on('keyup', function(){
                $val = $(this).val();
                $("#username").val($val);
            });

            $("#user_type").change(function(){
                val = $(this).val();
                if (val == 3) {
                    $("#partners").fadeIn();
                    $("#super_partners").hide();
                    $("#countys").hide();
                    $("#subcounty").hide();
                } else if (val == 4) {
                    $("#countys").fadeIn();
                    $("#partners").hide();
                    $("#super_partners").hide();
                    $("#subcounty").hide();
                } else if (val == 5) {
                    $("#subcounty").fadeIn();
                    $("#countys").hide();
                    $("#partners").hide();
                    $("#super_partners").hide();
                } else if (val == 7) {
                    $("#super_partners").fadeIn();
                    $("#countys").hide();
                    $("#partners").hide();
                    $("#subcounty").hide();
                }

            });
            
        });
    </script>
@endsection

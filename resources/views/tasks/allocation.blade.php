@extends('layouts.tasks')

@section('css_scripts')

@endsection

@section('custom_css')
	<style type="text/css">
		.hpanel .panel-body .spacing {
			margin-bottom: 1em;
		}
        .hpanel .panel-body .bottom {
            border-bottom: 1px solid #eaeaea;
        }
	</style>
@endsection

@section('content')
<div class="row">
        <div class="col-md-12">
            <div class="hpanel" style="margin-top: 1em;margin-right: 20%;">
            	<div class="alert alert-danger">
	                <center><i class="fa fa-bolt"></i> Please note that you CANNOT access the main system until the below pending tasks have been completed.</center>
	            </div>

                @php
                    $currentmonth = date('m');
                    $year = date('Y');
                @endphp
                
                <div class="panel-body" style="padding: 20px;box-shadow: none; border-radius: 0px;">
                    <div class="alert alert-warning spacing bottom" id="choice">
                        <strong>Do you wish to perform the kits allocation for this month ({{ date("F", mktime(null, null, null, $currentmonth)) }}, {{ $year }})</strong>
                        <button class="btn btn-success btn-lg" id="yesBtn">YES</button>
                        <button type="button" class="btn btn-danger btn-lg" data-toggle="modal" data-target="#noAllocationModal">NO</button>

                        <div class="modal fade hmodal-danger" id="noAllocationModal" tabindex="-1" role="dialog"  aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="color-line"></div>
                                    <div class="modal-header">
                                        <center>
                                            <h4 class="modal-title">Dismiss Allocation for {{ date("F", mktime(null, null, null, $currentmonth)) }}, {{ $year }}</h4>
                                            <small class="font-bold">Think twice before you leap.</small>
                                        </center>
                                    </div>
                                    <div class="modal-body">
                                        <p>Please note that you have responded with <strong class="btn btn-danger">NO</strong> to kit allocation. Before anything is set to stone, ensure you have enough stock to last this month. If not select <strong class="btn btn-primary">Cancel</strong>, but if stock is sufficient enough you can select <strong class="btn btn-default">OK</strong> and proceed.</p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                                        <a href="{{ url('cancelallocation') }}">
                                            <button type="button" class="btn btn-default">OK</button>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="allocationForm" style="display: none;">
                    {{ Form::open(['url' => '/national/allocation', 'method' => 'post', 'class'=>'form-horizontal']) }}
                        <div class="form-group">
                            <label class="col-sm-4 control-label"><center>Machines:<br><small>(Select Multiple)</small></center></label>
                            <div class="col-sm-8">
                                <select class="form-control input-sm" name="machine[]" id="machine" required style="width: 80%;">
                                    @foreach($machines as $machine)
                                        <option value="{{ $machine->id }}">{{ $machine->machine }}</option>
                                    @endforeach
                                </select>
                                <br>
                                <p class="label label-info">Please note in the above drop down you need to select all the machines you wish to allocate for</p>
                            </div>
                        </div>
                        <center><button type="submit" name="machine-form" class="btn btn-primary btn-lg" value="true">Proceed to Allocate</button></center>
                    {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function(){
            $("#machine").select2({
              closeOnSelect: false,
              multiple:true,
              placeholder: "Select a machine for allocation"
            });
            $("#yesBtn").click(function(){
                $("#choice").hide();
                $("#allocationForm").fadeIn();
            });
        });
    </script>
@endsection
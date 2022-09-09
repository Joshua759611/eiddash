@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')
<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="hpanel">
            	<div class="panel-heading">
                    @if ($message = Session::get('success'))

                        <div class="alert alert-success" id="message">

                            <center><p>{{ $message }}</p></center>

                        </div>

                    @endif
                    @if ($message = Session::get('failed'))

                        <div class="alert alert-danger" id="message">

                            <center><p>{{ $message }}</p></center>

                        </div>

                    @endif
                    @if ($create_text !='Add Partner')
            		<div class="alert alert-success" style="/*padding-top: 4px;padding-bottom: 4px;">
		                <p>
		                    You may update the respective facility contact details in the fields provided. Click on the " Update Contact Details " button at the bottom of the page to save the details.
		                </p>
		            </div>
                    @else
                    <div class="alert alert-success" style="/*padding-top: 4px;padding-bottom: 4px;">
                        <p>
                            You may add a new partner by 'Add partner' action.
                        </p>
                    </div>
                    @endif

                    @isset($create_endpoint)
                    <br />
                    <div class="form-group">
                        <div class="col-sm-8 col-sm-offset-5">
                            <a class="btn btn-success" href="{{ url($create_endpoint) }}">{{ $create_text }}</a>
                        </div>
                    </div>
                    <br />
                    @endisset
            	</div>
                <div class="panel-body">
                    <table class="table table-striped table-bordered table-hover data-table" style="font-size: 10px;" >
                        <thead>
                            <tr class="colhead">
                                @php
                                    echo $columns 
                                @endphp
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                        		echo $row 
                        	@endphp
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@section('scripts') 

    @component('/tables/scripts')

    @endcomponent

    <script type="text/javascript">
        setTimeout(function(){
            $("#message").hide();
        }, 2000);
    </script>
@endsection
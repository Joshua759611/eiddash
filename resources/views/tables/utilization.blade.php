@extends('layouts.master')

    @component('/tables/css')
    @endcomponent

@section('content')

<div class="content">
    <div class="row">
        <div class="col-lg-12">
            <div class="row" style="margin-bottom: 1em;">
                <!-- Year -->
                <div class="col-md-6">
                    <center><h5>Year Filter</h5></center>
                    @for ($i = 0; $i <= 9; $i++)
                        @php
                            $year=gmdate('Y')-$i
                        @endphp
                        <a href='{{ url("reports/utilization/$viewdata->testingSystem/$year") }}'>{{ gmdate('Y')-$i }}</a> |
                    @endfor
                </div>
                <!-- Year -->
                <!-- Month -->
                <div class="col-md-6">
                    <center><h5>Month Filter</h5></center>
                    @for ($i = 1; $i <= 12; $i++)
                        <a href='{{ url("reports/utilization/$viewdata->testingSystem/null/$i") }}'>{{ gmdate("F", mktime(null, null, null, $i)) }}</a> |
                    @endfor
                </div>
                <!-- Month -->
            </div>
            <div class="hpanel">
                <div class="panel-body">
            	    <table class="table table-striped table-bordered table-hover data-table">
                        <thead>
                            <tr class="colhead">
                                <th>Lab Name</th>
                                @foreach($viewdata->machines as $machinekey => $machinevalue)
                                    <th>{{ $machinevalue->machine }}</th>
                                @endforeach
                                <th>Total</th>
                                @foreach($viewdata->machines as $machinekey => $machinevalue)
                                    <th>{{ $machinevalue->machine }}%</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                           @foreach($viewdata->data as $datakey => $datavalue)
                           <tr>
                               @php
                                   $totals = ($datavalue->abbott + $datavalue->taqman + $datavalue->c8800 + $datavalue->panther);
                               @endphp
                               <td>{{ $datavalue->lab_name }}</td>
                               <td>{{ $datavalue->abbott }}</td>
                               <td>{{ $datavalue->taqman }}</td>
                               <td>{{ $datavalue->c8800 }}</td>
                               <td>{{ $datavalue->panther }}</td>
                               <td>{{ ($totals) ? number_format($totals) : 0 }}</td>
                               <td>{{ @($datavalue->abbott*100)/$totals) }}</td>
                               <td>{{ @($datavalue->taqman*100)/$totals) }}</td>
                               <td>{{ @($datavalue->c8800*100)/$totals) }}</td>
                               <td>{{ @($datavalue->panther*100)/$totals) }}</td>
                           </tr>
                           @endforeach
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
@endsection
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
                           @foreach($viewdata->labs as $datakey => $lab)
                           <tr>
                                @php
                                    $totals = 0;
                                    $labdata = $viewdata->data->where('lab_id', $lab->id)->first();
                                    if (!empty($labdata))
                                        $totals = @($labdata->abbott + $labdata->taqman + $labdata->c8800 + $labdata->panther);
                                @endphp
                                <td>{{ $lab->labname }}</td>
                                <td>{{ $labdata->taqman ?? 0 }}</td>
                                <td>{{ $labdata->abbott ?? 0 }}</td>
                                <td>{{ $labdata->c8800 ?? 0 }}</td>
                                <td>{{ $labdata->panther ?? 0 }}</td>
                                <td>{{ ($totals) ? number_format($totals) : 0 }}</td>
                                <td>{{ ($labdata) ? number_format(@(($labdata->taqman * 100)/$totals)) : 0 }}</td>
                                <td>{{ ($labdata) ? number_format(@(($labdata->abbott * 100)/$totals)) : 0 }}</td>
                                <td>{{ ($labdata) ? number_format(@(($labdata->c8800 * 100)/$totals)) : 0 }}</td>
                                <td>{{ ($labdata) ? number_format(@(($labdata->panther * 100)/$totals)) : 0 }}</td>
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
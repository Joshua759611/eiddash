@extends('layouts.master')

@component('/forms/css')
    <link href="{{ asset('css/datapicker/datepicker3.css') }}" rel="stylesheet" type="text/css">
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
            <div class="hpanel">
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Consumption Month</th>
                                    <th>Lab</th>
                                    <th>Consumption Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($data->labs as $key => $lab)
                                <tr>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ date("F", mktime(null, null, null, $data->month, 1, $data->year)) }}, {{ $data->year }}</td>
                                    <td>{{ $lab->labdesc ?? $lab->name }}</td>
                                    <td>
                                    @if($lab->consumptions->count() > 0)
                                        <span class="label label-success">Complete</span>
                                    @else
                                        <span class="label label-warning">Incomplete</span>
                                    @endif
                                    </td>
                                    <td>
                                    @if($lab->consumptions->count() > 0)
                                        <a href="{{ url('approveallocation/'.$lab->id.'/'.$data->testtype.'/'.$data->year.'/'.$data->month) }}" class="btn btn-info">View</a>
                                    @else
                                        N/A
                                    @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><center>No Consumption Data Available</center></td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
    @component('/forms/scripts')
        @slot('js_scripts')
            <script src="{{ asset('js/datapicker/bootstrap-datepicker.js') }}"></script>
            <script src="{{ asset('vendor/datatables/media/js/jquery.dataTables.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-bs/js/dataTables.bootstrap.min.js') }}"></script>
            <!-- DataTables buttons scripts -->
            <script src="{{ asset('vendor/pdfmake/build/pdfmake.min.js') }}"></script>
            <script src="{{ asset('vendor/pdfmake/build/vfs_fonts.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
            <script src="{{ asset('vendor/datatables.net-buttons-bs/js/buttons.bootstrap.min.js') }}"></script>
            
        @endslot

        $(".date").datepicker({
            startView: 0,
            todayBtn: "linked",
            keyboardNavigation: false,
            forceParse: true,
            autoclose: true,
            endDate: new Date(),
            format: "yyyy-mm-dd"
        });

        $('.data-table').dataTable({
            // dom: "<'row'<'col-sm-4'l><'col-sm-4 text-center'B><'col-sm-4'f>>tp",
            "lengthMenu": [ [10, 25, 50, -1], [10, 25, 50, "All"] ],
            "bInfo" : true,
            buttons: [
                {extend: 'copy',className: 'btn-sm'},
                {extend: 'csv',title: 'Download', className: 'btn-sm'},
                {extend: 'pdf', title: 'Download', className: 'btn-sm'},
                {extend: 'print',className: 'btn-sm'}
            ]
        });

        
    @endcomponent
    <script type="text/javascript">
                
    </script>
   
@endsection
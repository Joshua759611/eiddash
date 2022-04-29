
<!DOCTYPE html>
<html>
<head>

	<style type="text/css">
		body {
			font-weight: 1px;
		}

		table {
			border-collapse: collapse;
			margin-bottom: .5em;
		}

		table, th, td {
			border: 1px solid black;
			border-style: solid;
     		font-size: 10px;
     		text-align: center;
		}

		th{
			font-weight: bold;
		}

		h1, h2, h3 {
			margin-top: 6px;
		    margin-bottom: 6px;
     		text-align: center;
		}
	</style>
</head>
<body>
	<div align='center' style='text-align: center; align-content: center;'>
	    <img src="{{ asset('img/naslogo.jpg') }}" alt='NASCOP'>
	    <h3>MINISTRY OF HEALTH</h3>
	    <h3>NATIONAL AIDS AND STD CONTROL PROGRAM (NASCOP)</h3> 
	</div>

	<h3> {{ $title }} </h3>	

	<table style="width: 100%;">
		<thead>
			<tr>
				<th>#</th>
				<th>Lab</th>
				<th>Samples Recieved in 2022</th>
				<th>Samples Tested</th>
				<th>Samples Untested</th>
				<th>Samples whose results has been dispatched</th>
				{{-- <th>Samples whose results have been read</th> --}}

			</tr>
		</thead>
		<tbody>
			@foreach($rows as $key => $row)	
                {{-- @dd($row) --}}
				<tr>
					<td> {{ $key+1  }} </td>
					<td> {{  $row->name ?? ''}} </td>
					<td> {{ $row->recieved[0] ?? ''}} </td>
					<td> {{  $row->tests[0] ?? '' }} </td>
					<td> {{ $row->untested[0] ?? '' }} </td>
					<td> {{ $row->dispatch[0] ?? '' }} </td>
					{{-- <td> {{ $row->dispatch[0] ?? '' }} </td> --}}
					
				</tr>
                
			@endforeach			
		</tbody>
	</table>




</body>
</html>
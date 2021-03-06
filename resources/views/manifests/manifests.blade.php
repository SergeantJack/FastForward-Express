@extends('layouts.app')

@section('script')
<script type='text/javascript' src='/DataTables/media/js/jquery.dataTables.min.js'></script>
<script type='text/javascript' src='/DataTables/media/js/dataTables.bootstrap.min.js'></script>
<script type='text/javascript' src='/DataTables/extensions/Buttons/js/dataTables.buttons.min.js'></script>
<script type='text/javascript' src='/DataTables/extensions/Buttons/js/buttons.bootstrap.min.js'></script>
<script type='text/javascript' src='/DataTables/extensions/Buttons/js/buttons.colVis.js'></script>
<script type='text/javascript' src='/DataTables/extensions/Buttons/js/buttons.print.min.js'></script>
<script type='text/javascript' src='/js/manifests/manifests.js?{{config('view.version')}}'></script>
@endsection

@section('style')
<link rel='stylesheet' type='text/css' href='/DataTables/media/css/jquery.dataTables.min.css'/>
<link rel='stylesheet' type='text/css' href='/DataTables/extensions/Buttons/css/buttons.dataTables.min.css'/>
<link rel='stylesheet' type='text/css' href='/DataTables/extensions/Buttons/css/buttons.bootstrap.min.css'/>
<link rel='stylesheet' type='text/css' href='/css/tables.css' />
@parent
@endsection

@section('content')
<div class='col-md-11'>
	<table id='table'>
		<thead>
			<tr>
				<td><input type='checkbox' id='selectAll' onclick='selectAll(this)' title='Selects all items on the current table page' /></td>
				<td></td>
				<td>Manifest ID</td>
				<td>Driver ID</td>
				<td>Employee</td>
				<td>Date Run</td>
				<td>First Bill Date</td>
				<td>Last Bill Date</td>
				<td>Bill Count</td>
			</tr>
		</thead>
	</table>
</div>

<div id='downloadModal' class='modal fade' role='dialog'>
	<div class='modal-dialog'>
		<div class='modal-content'>
			<div class='modal-header'>
				<h4 class='modal-title'>Creating PDF files</h4>
			</div>
			<div class='modal-body'>
				<i class='fas fa-spinner fa-spin'></i> Please wait while we generate your manifests
			</div>
		</div>
	</div>
</div>

<!-- delete modal -->
<div id="delete_modal" class="modal fade" role="dialog">
	<div class="modal-dialog">
<!-- delete modal content -->
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title">Confirm Deletion of Manifest</h4>
			</div>
			<div class="modal-body">
				<p id="delete_message">Please confirm deletion of manifest. <b>This action can not be undone.</b></p>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
				<a id="delete_button" type="button" class="btn btn-danger" href="">Delete</a>
			</div>
		</div>
	</div>
</div>
	
@endsection

@section('advFilter')
<div class='well'>
	<h4>Actions</h4>
	<button type='button' class='btn btn-primary' onclick='printMass()'>Download Selected</button>
	<hr/>
</div>
@endsection

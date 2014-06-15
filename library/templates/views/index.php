<?php set('title', '<%CAPITAL_CONTROLLER_NAME%> List') ?>

<h1><%CAPITAL_CONTROLLER_NAME%> List</h1>

<?php messages();

include_javascript('index');

?>

<div class="table-responsive">
	<table class="table table-condensed">

		<thead>
			<tr>
				<?= reset($this-><%CONTROLLER_NAME%>)->model_table_header(); ?>
				<th>Controls</th>
			</tr>
		</thead>

		<tbody>

<?php

foreach ($this-><%CONTROLLER_NAME%> as $<%MODEL_NAME%>) {

 ?>

			<tr id="row_<?= $<%MODEL_NAME%>->id ?>">
				<?= $<%MODEL_NAME%>->model_table_row() ?>
				<td>
					<div class="btn-toolbar">
						<a class="btn btn-success btn-xs" href="<?= address('<%CONTROLLER_NAME%>', 'edit', $<%MODEL_NAME%>->id) ?>">Edit</a>
						<button class="btn btn-danger btn-xs delete-button" id="delete_button_<?= $<%MODEL_NAME%>->id ?>" data-loading-text="Delete">Delete</button>
					</div>
				</td>
			</tr>


<?php } ?>

		</tbody>
	</table>

</div>


<div class="page-header">
<h1>Users</h1>
  <div class="btn-toolbar right-button-bar">
  	<?= link_to(icon('plus').' New User', 'users', 'new', '', array("class" => "btn btn-success")); ?>
  </div>
</div>



<?php

messages();
include_javascript('list');

$all_class = '';
$drop_class = '';

$status_all_class = '';
$active_class = '';
$unverified_class = '';
$deactivated_class = '';
$pending_class = '';

if (!isset($this->get['type'])) {
	$all_class = ' class="active"';
	$name = 'all';
	$all_count = 'all_count';
}
else {
	$name = $this->get['type'];
	$name_class = $name."_class";
	$all_count = $name."_count";
	$$name_class = ' class="active"';
}

if (!isset($this->get['status'])) {
	$status_all_class = ' class="active"';
	$status_name = $name;
}
else {
	$status_name = $this->get['status'];
	$status_count_name = $status_name."_count";
	$status_drop_name = $status_name;
	$drop_class = " active";
	$status_class_name = $status_name.'_class';
	$$status_class_name = ' class="active"';
}

  $params_array = array();

	if (isset($this->get['search']))
		  $params_array['search'] = $this->get['search'];

	if (isset($this->get['order']))
		  $params_array['order'] = $this->get['order'];

	if (isset($this->get['by']))
		  $params_array['by'] = $this->get['by'];

?>


<nav class="navbar navbar-default">
  <div class="container-fluid">
  	<!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#users-nav">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Users</a>
    </div>

   <div class="collapse navbar-collapse" id="users-nav">

	<form class="navbar-form navbar-right" role="search" method="get" action="<?= current_address(); ?>">
		<div class="form-group">
		<?php if (isset($this->get['search']))
			 $search_text = $this->get['search'];
			 else $search_text = ''; ?>
		  <input type="text" class="search-query form-control" placeholder="Search by Name" name="search" value="<?= $search_text ?>"/>
		</div>
	</form>
	<?php if (is_admin()): ?>

	<ul class='nav navbar-nav navbar-right'>
  	  <li class="dropdown<?= $drop_class ?>">
  	      <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
  	        <?php if (isset($status_drop_name)) echo ucfirst($status_drop_name); else echo 'Showing All';
	  		echo ' ('.$this->users_count.')';
			?>
  	        <b class="caret"></b>
  	      </a>
		  <?php
  if (isset($this->get['type']))
	  $params_array['type'] = $this->get['type'];

   ?>

  	      <ul class="dropdown-menu">
			  <li<?= $status_all_class ?>><a href="<?= address('users', 'list', '', $params_array) ?>">All (<?= $this->$all_count ?>)</a></li>
			  <li<?= $active_class ?>><a href="<?= address('users', 'list', '', array_merge($params_array, array('status' => 'active'))) ?>">Active (<?= $this->active_count ?>)</a></li>
			  <li<?= $unverified_class ?>><a href="<?= address('users', 'list', '', array_merge($params_array, array('status' => 'unverified'))) ?>">Unverified (<?= $this->unverified_count ?>)</a></li>
			  <li<?= $deactivated_class ?>><a href="<?= address('users', 'list', '', array_merge($params_array, array('status' => 'deactivated'))) ?>">Deactivated (<?= $this->deactivated_count ?>)</a></li>
			  <li<?= $pending_class ?>><a href="<?= address('users', 'list', '', array_merge($params_array, array('status' => 'pending'))) ?>">Pending (<?= $this->pending_count ?>)</a></li>
  	      </ul>
  	    </li>
	</ul>
	<?php endif ?>
   </div>
  </div>
</nav>

<?php if ($this->users): ?>

	<?php if (isset($this->get['search'])): ?>
	<div class="clearfix">
		<p class="lead pull-left">Showing search results for: <?= $this->get['search'] ?></p>
		<a href="<?= address('users', 'list') ?>" class='btn btn-primary pull-right'><?= icon('remove icon-white') ?> Clear Search</a>
		</div>
<?php endif ?>

<?php pagination($this->users, $this->users_count, $this->get['page']) ?>

<?php $users_count = count($this->users);

		$users_per_column = ceil($users_count / 2);

?>

<?php $params_array = $this->parameters;

$counter = 1;

?>

<div class="row-fluid">

<?php foreach ($this->users as $key => $user): ?>

<?php if($counter == 1 || ($counter == $users_per_column + 1 && !is_admin())) : ?>

<div<?= (is_admin()) ? ' class="col-md-12"' : ' class="col-md-6"'?>>

<table class="table table-condensed table-striped" id="users-list">
	<thead>
		<tr>
			<?php if (is_admin()): ?>
				<th><?= order_link('ID', '#') ?></th>
			<?php endif ?>
			<th><?= order_link('Surname', 'Name') ?></th>
			<?php if (is_admin()): ?>
				<th style="text-align: center;"><?= order_link("Status") ?></th>
				<th><?= order_link("Level") ?></th>
				<th>Actions</th>
				<?php else: ?>
					<th>Contact</th>
			<?php endif ?>
		</tr>
	</thead>
	<tbody>

<?php endif ?>


		<tr id="row_<?= $user->id ?>">
			<?php if (is_admin()): ?>
				<td><?= $user->id ?></td>
			<?php endif ?>
			<td>

					<?= show_link($user->full_name(), $user->id) ?>

			</td>

			<?php if (is_admin()): ?>
				<td style="text-align: center;"><?= status_cycle_link($user) ?></td>
				<td><?= $user->level ?></td>
				<td>
					<div class="btn-toolbar">
					<a class="btn btn-success btn-xs" href="<?= address('users', 'edit', $user->id) ?>">Edit</a><button class="btn btn-danger btn-xs delete-button" id="delete_button_<?= $user->id ?>" data-loading-text="Delete">Delete</button>
					<?php if ($user->email->value): ?>

					<div class="btn-group">
					  <a class="btn dropdown-toggle btn-xs btn-primary" data-toggle="dropdown" href="#">
					    <?= icon('envelope icon-white'); ?>
						<span class="caret"></span>
					  </a>
					  <ul class="dropdown-menu pull-right">
					    <li><a href="mailto:<?= $user->email ?>">email <?= $user->email ?></a></li>
					    <li><a data-toggle="modal" href="#member-message-modal-<?= $user->id ?>">Send Message</a></li>
					  </ul>
					</div>
					<?php endif ?>
				</div>
				</td>
				<?php else: ?>
					<td>
							<?php if ($user->email->value): ?>
								<a class="btn btn-xs btn-default" data-toggle="modal" href="#member-message-modal-<?= $user->id ?>">Send Message</a>
							<?php endif ?>
					</td>
			<?php endif ?>
		</tr>

<?php if($counter == $users_count || ($counter == $users_per_column && !is_admin())) : ?>

	</tbody>
</table>

</div>

<?php endif;
$counter++; ?>

<?php if ($user->email->value): ?>
	<?php send_message_modal($user); ?>
<?php endif ?>

<?php endforeach ?>

</div>

<?php pagination($this->users, $this->users_count, $this->get['page']) ?>

<?php else: ?>
	<?php if ($this->get['search']): ?>
		<p class="lead pull-left">No search results to display for: <?php echo $this->get['search'] ?></p>
		<a href="<?= address('users', 'list') ?>" class='btn btn-primary pull-right'><?= icon('remove icon-white') ?> Clear Search</a>
		<br style="clear: both" />
	<?php else : ?>
		<p class="lead">No users to display</p>
	<?php endif ?>
<?php endif ?>

<?php

?>

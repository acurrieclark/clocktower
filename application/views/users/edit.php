<div class="page-header">
<h1>Edit Profile <small><?= $this->user->full_name() ?></small></h1>
</div>

<?php

messages();

?>

<div class="row">
	<div class="col-md-4 col-md-offset-1">

<div>
	<?php
$this->form->form(ABSOLUTE."users/edit/$this->id", "Update");
?>
</div>

	</div>
	<div class="col-md-3 col-md-offset-3">
			<?php render_partial('user_functions', array('active' => 'edit')); ?>
	</div>
</div>

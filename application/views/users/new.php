
<div class="page-header">
<h1>New User</h1>
</div>

<?php

messages();

?>

<div class="row">
	<div class="col-md-4 col-md-offset-1">
<?php
$this->form->form(ABSOLUTE."users/new", "");
?>
	</div>
	<div class="col-md-3 col-md-offset-3">
			<?php render_partial('user_functions', array('active' => 'new')); ?>
	</div>
</div>

<?php


?>

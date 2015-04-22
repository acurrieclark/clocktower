<?php

?>
<div class="page-header">
	<h1>Reset your Password</h1>
</div>
<?php

messages();

?>

<div class="row">
	<div class="col-md-3 col-md-offset-2">
		<?php
		$this->form->form(ABSOLUTE.'users/password-reset/', "Reset Password");
		?>
	</div>
</div>



<?php

?>
<div class="page-header">
	<h1>Reset Password <small><?= $this->user->Name ?></small></h1>
</div>

<?php

messages();

?>

<div class="row">
	<div class="col-md-3 col-md-offset-2">

<div id="change_password_block">

<?php

$this->form->form(ABSOLUTE.'users/new-password/'.$this->id, "Change Password");
?>

</div>
</div>
</div>



<div class="page-header">
	<h1>Login</h1>
</div>


<?php

set('header-title', 'Login');

messages();

?>
<div class="row">
	<div class="col-md-3 col-md-offset-2">

<?php

$this->form->form_header(ABSOLUTE.'users/login');

$this->form->main_form();

?><p><small>Forgotten your password? <a href="<?php echo ABSOLUTE.'users/password-reset' ?>">Reset Password</a></small></p>

<?php

$this->form->form_button('Login');

$this->form->form_footer();

?>
	</div>

<div class="col-md-3 col-md-offset-2">
		<h4>Not registered yet?</h4>
		<p>Sign up now to get access to the users area of the site.</p>
		<?= link_to('Sign Up Now', 'register', '', '', array('class' => 'btn btn-primary btn-block')) ?>
</div>

</div>



<?php

?>
<div class="page-header">
		<h1>Change Password <small><?= $this->user->Name ?></small></h1>
</div>



<div class="row user-details">
	<div class="col-md-9">
		<?php
			messages();
		?>

		<div class="row">
			<div class="col-md-5 col-md-offset-2">
				<div id="change_password_block">
					<?php
						$this->form->form(ABSOLUTE.'users/change-password/'.$this->user->id, "Change Password");
					?>
				</div>
			</div>
		</div>


	</div>
		<div class="col-md-3">
			<?php render_partial('user_functions', array('active' => 'password')); ?>
	</div>

</div>

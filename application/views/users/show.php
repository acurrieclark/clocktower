<div class="page-header">
	<h1><?= $this->user->full_name() ?><?= is_admin() ? " <small>".$this->user->level."</small>" : "" ?></h1>
</div>
<?php
messages();


?>

<div class="row user-details">
	<div class="col-md-4 col-md-offset-1">

		<p class="lead">Joined on <?= $this->user->created_at->show() ?></p>

		<?php if (is_admin() || $this->id == current_user()->id):

			$contact_details = $this->user->show_simple();

			if ($contact_details) {
				 ?>
				  <h3>Details <small><?= (is_admin()) ? '(Admin Only)' : '(Not Public)' ?></small> </h3>	<?php
				echo $contact_details;
			}
			 endif ?>
	</div>
	<div class="col-md-2 col-md-offset-1">

		<h3>Contact</h3>
		<div class="contact_links">
		<?php if ($this->user->email->value): ?>


		<?php if (is_admin()): ?>

			<div class="btn-group contact_link">
			  <a class="btn dropdown-toggle btn-default" data-toggle="dropdown" href="#">
			    <?= icon('envelope'); ?>
				Contact <span class="caret"></span>
			  </a>
			  <ul class="dropdown-menu">
			    <li><a href="mailto:<?= $this->user->email ?>">email <?= $this->user->email ?></a></li>
			    <li><a data-toggle="modal" href="#member-message-modal-<?= $this->user->id ?>">Send Message</a></li>
			  </ul>
			</div>
			<?php else: ?>
				<div class="contact_link">
					<a data-toggle="modal" href="#member-message-modal-<?= $this->user->id ?>" class="btn btn-default"><?= icon('envelope'); ?> Send Message</a>
				</div>
		<?php endif ?>
<?php endif ?>
		</div>
		<?php
			if ($this->user->status->value == 'Pending') {
				 ?>
				 	<div class="resend_confirmation_container">
				 		<?= link_to('Resend Confirmation Email', 'users' , 'resend_confirmation_email', $this->id, array('class' => 'btn btn-default')) ?>
				 	</div>
				 <?php
			}
		 ?>
	</div>
	<div class="col-md-3">

		<?php
		if (current_user()->id == $this->id)
			$variables = array('active' => 'profile');
		else
			$variables = array();
		?>

			<?php render_partial('user_functions', $variables); ?>
	</div>
</div>

<?php send_message_modal($this->user) ?>

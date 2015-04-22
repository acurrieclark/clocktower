<?php

function order_link($name, $display='') {
	global $app;

	if (!$display) $display = $name;

	$caret_direction = "";

	$script = '';

	$order = 'asc';

	if (isset($app->parameters['by']) && $app->parameters['by'] == make_short($name)) {
		if (isset($app->parameters['order']) && $app->parameters['order'] == 'asc') {
			$order = 'desc';
		}
		else $caret_direction = ' dropup';
	}
	$returnable = '<div class="btn-group'.$caret_direction.'" style="padding: 0px; line-height: 1em" id="'.make_short($name).'_container">';
	$returnable .= link_to($display.' <span class="caret_white caret" id="'.make_short($name).'_caret"></span>', $app->controller, $app->action, '', array("class" => "btn btn-link", 'style' => "padding: 0px; line-height: 1.4em; text-decoration: none;" , "id"=> make_short($name)."_order_link"), array_merge($app->parameters, array('by' => make_short($name), 'order' => $order)));
	$returnable .= '</div>';


	if (isset($app->parameters['by']) && $app->parameters['by'] == make_short($name)) {
		$script .= '$("#'.make_short($name).'_caret").removeClass("caret_white");

		$("#'.make_short($name).'_order_link").hover(function(){
			$("#'.make_short($name).'_container").toggleClass("dropup");
		},
		function () {
			$("#'.make_short($name).'_container").toggleClass("dropup");
		}); ';

	}
	else
	$script .= '

		$("#'.make_short($name).'_order_link").hover(function(){
			$("#'.make_short($name).'_caret").removeClass("caret_white");
		},
		function () {
			$("#'.make_short($name).'_caret").addClass("caret_white");
		}); ';


		javascript_start();
		echo $script;
		javascript_end();

	return $returnable;
}


function send_message_modal($user) {
	 ?>

	<?php
	$form = new user_message(array(
		'sender_id' => current_user()->id->value,
		'Content' => '',
		'recipient_id' => $user->id->value
		));
	 ?>

	 <div class="modal fade" id="member-message-modal-<?= $user->id ?>">
	 	<div class="modal-dialog">
		    <div class="modal-content">
	 	<form action="<?= address('users', 'contact') ?>" method="POST" accept-charset="utf-8">
		 	<div class="modal-header">
		 		<a class="close" data-dismiss="modal">&times;</a>
		 		<h3><?= $user->full_name(); ?></h3>
		 	</div>
		 	<div class="modal-body">
		 		<div class="row-fluid">
		 			<?php $form->main_form(); ?>
				</div>
		 	</div>
		 	<div class="modal-footer">
		 		<button type="submit" class="btn btn-primary">Send</button>
		 		<a href="#" class="btn" data-dismiss="modal">Close</a>
		 	</div>
	 	</form>
	 </div></div>
	 </div>

	 <?php
}


function status_cycle_link($user) {
	$status = $user->status;
	if ($status == "Active" && $user->level != "Administrator") {
		echo link_to("<span class='label label-success' rel='tooltip' data-original-title='Active - Click to deactivate' data-placement='left'>".icon('ok')."</span>", "#deactivate_modal$user->id", '', '', array("data-toggle" => "modal"));
		?>


		<div class="modal fade" id="deactivate_modal<?= $user->id ?>">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title">Deactivate Account</h4>
		      </div>

		      <form action="<?= ABSOLUTE.'users/deactivate/'.$user->id ?>" method="POST" accept-charset="utf-8" class="form-horizontal">
		      	<?php include_security(); ?>
		  		<fieldset>
				  	<div class="modal-body" style="text-align: left;">
						<p class="lead">Please confirm you wish to deactivate this user's account.</p>

					    <?php
						  echo $user->show_simple(array('FirstName', 'Surname', 'email'));
						  ?>
				  		<div class="form-group">
		  					<label class="col-md-4 show_simply_name control-label">
								Reason for Deactivation
		  					</label>
		  					<div class="col-md-8 show_simply_value">
								<input id="reason" type="text" name="reason" size="25" value="" class="text reason form-control" placeholder="Optional">
		  					</div>
				  		</div>
					</div>
			  		<div class="modal-footer">
				  		<input type="submit" value="Deactivate" name="submit" class="btn btn-danger">
					    <a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a>
					</div>
			  	</fieldset>
		  	</form>

		    </div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<?php
	}
	else if ($status == "Active") {
				?><span class='label label-success' rel='tooltip' data-original-title='Active Administrator Account' data-placement='left'><strong>Admin</strong></span>
	<?php
	}
	else if ($status == "Deactivated")
		echo link_to("<span class='label label-danger' rel='tooltip' data-original-title='Deactivated - Click to re-activate' data-placement='left'>".icon('remove')."</span>", 'users', 'activate', $user->id);
	else if ($status == "Unverified") {
		echo link_to("<span class='label label-warning' rel='tooltip' data-original-title='Unverified - Click to verify' data-placement='left'>".icon('flag')."</span>", "#verify_modal$user->id", '', '', array("data-toggle" => "modal"));

		?>

		<div class="modal fade" id="verify_modal<?= $user->id ?>">
		  <div class="modal-dialog">
		    <div class="modal-content">
		      <div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title">Verify Account</h4>
		      </div>

		      <form action="<?= ABSOLUTE.'users/verify/'.$user->id ?>" method="POST" accept-charset="utf-8" class="form-horizontal">
		      	<?php include_security(); ?>
		  		<fieldset>
				  	<div class="modal-body" style="text-align: left;">
						<p class="lead">Please confirm you wish to verify this user's account.</p>

					    <?php
						  echo $user->show_simple(array('FirstName', 'Surname', 'email'));
						  ?>
					</div>
			  		<div class="modal-footer">
				  		<input type="submit" value="Verify" name="submit" class="btn btn-success">
					    <a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a>
					</div>
			  	</fieldset>
		  	</form>

		    </div><!-- /.modal-content -->
		  </div><!-- /.modal-dialog -->
		</div><!-- /.modal -->

		<?php
	}
	else if ($status == "Pending")
		echo "<span class='label label-primary' rel='tooltip' data-original-title='Pending - email not yet confirmed' data-placement='left'>".icon('time')."</span>";
}


?>

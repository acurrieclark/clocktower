<?php

function icon($name, $white=false) {
	if ($white)
		$name .= " icon-white";
	return "<i class=\"icon-$name\"></i>";
}

function label($text, $type='') {
	if ($type)
		$type = ' label-'.$type;
	return '<span class="label'.$type.'">'.$text.'</span>';
}

function include_security() {
	model::setup_security();
	model::form_security();
}

function include_zocial() {

	add_stylesheet(ABSOLUTE.'vendor/zocial/zocial.css');

}

function include_redactor() {
	add_javascript(ABSOLUTE.'vendor/redactor/redactor.min.js');
	add_javascript('redactor_plugins');
	add_stylesheet(ABSOLUTE."vendor/redactor/redactor.css");
}

function include_fancybox() {
	add_javascript(ABSOLUTE.'vendor/fancybox/jquery.fancybox.pack.js');
	add_stylesheet(ABSOLUTE."vendor/fancybox/jquery.fancybox.css");
}

function include_chosen() {
	add_javascript(ABSOLUTE.'vendor/chosen/chosen.jquery.min.js');
	add_stylesheet(ABSOLUTE."vendor/chosen/chosen.css");
}

function include_image_selection() {

	global $image_selection_included;

	if (!$image_selection_included) {

		content_start('body');
		?>
		<div id="image-manager" class="row-fluid">
		<?php
			render_partial('image_manager');

		?>
		</div>
		<?php
		content_end();
		$image_selection_included = true;
	}
}

function social_links($title = '', $url = '')
{
?>
	<div class="social-share-buttons clearfix">

		<ul class="clearfix">
			<li><a href="http://www.facebook.com/sharer.php?u=<?= $url ?>&amp;t=<?= $title ?>" title="Share on Facebook." class="facebook"><?= icon('facebook-sign') ?></a></li>
			<li><a href="http://twitter.com/home/?status=<?= urlencode($title.' - '.$url) ?>" title="Tweet this!" class="twitter"><?= icon('twitter-sign') ?></a></li>
		</ul>
	</div>

<?php

}


function pagination($array, $total, $page = 1, $limit = PAGINATE_LIMIT) {

	global $app;

	if (count($array) >= $total)
		return;

	if ($page == '') {
		$page = 1;
	}

	$previous = $page-1;
	if ($previous == 0)
		$previous = "";
	$next = $page+1;

	$pages = ceil($total/PAGINATE_LIMIT);

	$current_id = $app->id;

	$pages_to_show = array(1,2);
	$counter = 3;

	$upper = $page + 2;
	$lower = $page - 2;
	if ($upper > $pages) {
		$lower = $lower - ($upper - $pages) + 1;
		$upper = $pages;
	}
	if ($lower < 1) {
		$upper = $upper - ($lower);
		$lower = 1;
	}

	while ($counter < $pages) {
		if ($counter >= $lower && $counter <= $upper && !in_array($counter, $pages_to_show)) {
			$pages_to_show[] = $counter;
		}
		$counter++;
	}
	if (!in_array($pages-1, $pages_to_show)) {
		$pages_to_show[] = $pages-1;
	}
	if (!in_array($pages, $pages_to_show)) {
		$pages_to_show[] = $pages;
	}

	?>

	<div class="pagination pagination-centered">
	  <ul>
		  <?php if ($previous < 1)
				$previous_class = ' class="disabled"';
	 ?>

	    <li<?= $previous_class ?>>
	    	<?php echo link_to('&larr;', $app->controller, $app->action, $current_id, array() , array_merge($app->get,array('page' => $previous))); ?>
	    </li>
		<?php
		for ($i=1; $i <= $pages; $i++) {
			if (in_array($i, $pages_to_show)) {
				$showing = true;
				echo '<li';
				if ($i == $page) echo ' class="active"';
				echo '>';
				echo link_to($i, $app->controller, $app->action, $current_id, array() , array_merge($app->get,array('page' => $i)));
				echo '</li>';
			}
			else {
				if ($showing == true)
					echo '<li class="disabled"><a href="#">...</a></li>';
				$showing = false;
			}
		}
		if ($next > $pages)
			$next_class = ' class="disabled"';
		?>
		    <li<?= $next_class ?>>
		    	<?php echo link_to('&rarr;', $app->controller, $app->action, $current_id, array() , array_merge($app->get, array('page' => $next))); ?>
		    </li>
	  </ul>
	</div>

	<?php
}

function link_to($text, $controller="", $action = "", $id="", $options = array(), $params = array()) {
	$other_options = "";
	foreach ($options as $name => $value) {
		$other_options .= " $name=\"$value\"";
	}

	return '<a href="'.address($controller, $action, $id, $params).'"'.$other_options.'>'.$text.'</a>';
}

function new_link($text, $class="") {
	global $app;
	return '<a href="'.address($app->controller, 'new').'" class="'.$class.'">'.$text.'</a>';
}

function show_link($text, $id, $class="") {
	global $app;
	return '<a href="'.address($app->controller, 'show', $id).'" class="'.$class.'">'.$text.'</a>';
}

function edit_link($text, $id, $class="") {
	global $app;
	return '<a href="'.address($app->controller, 'edit', $id).'" class="'.$class.'">'.$text.'</a>';
}

function delete_link($text, $id, $class="") {
	global $controller;
	return '<a href="'.address($controller, 'delete', $id).'" class="'.$class.'">'.$text.'</a>';
}

function cross() {
	return '<span style="color: red; font-family: Arial; font-size: 1.1em; font-weight: bold;">&#10006;</span>';
}

function tick() {
	return '<span style="color: green; font-family: Arial; font-size: 1.1em; font-weight: bold;">&#10004;</span>';
}

function inspect($variables = array()) {
	global $app;
	if (DEVELOPMENT_ENVIRONMENT != true) return;
	$identifier = mt_rand();
	$first_nav = ' class="active"';
	$first_tab =  ' active in';
	global $app, $flash;
	$variables["Current User"] = current_user();
	$vars = get_defined_vars();
	$vars2 = get_defined_constants();
	// $variables["All"] = $vars;
	$variables['Messages'] = $flash;
	?>
	<div class="well clearfix">

		<?php
			if (isset($app->get)) {
				$separator = "&amp;";
			}
			else $separator = "?";
			 ?>

			<button class="btn btn-small" data-toggle="collapse" data-target="#inspector_clouseau<?php echo $identifier ?>" id="inspect<?php echo $identifier ?>_button" style="float: right; margin-top: -4px; z-index: 10; position: relative;">
		  Show Inspector
		</button>
			<div class="collapse" id="inspector_clouseau<?php echo $identifier ?>" style="margin-top:-4px">

				<div class="tab-content">
				<ul class="nav nav-pills" id="inspectorTab">
		<?php foreach ($variables as $key => $value): ?>
		  <li<?php echo $first_nav ?>><a href="#<?php echo make_short($key) ?>" data-toggle="pill"><?php echo $key ?></a></li>
		<?php
		$first_nav = "";
		endforeach ?>
		</ul>

		<?php foreach ($variables as $key => $value): ?>
		  <div class="tab-pane<?php echo $first_tab ?> fade" id="<?php echo make_short($key) ?>">
		  	<pre style="margin-top: 10px"><?php print_r($value) ?></pre>
		  </div>
 		<?php
		$first_tab = "";
		endforeach ?>

						</div>

		</div>





		<?php javascript_start(); ?>
	$('#inspector_clouseau<?php echo $identifier ?>').on('hidden', function () {
		$('#inspect<?php echo $identifier ?>_button').text('Show Inspector');
	})
	$('#inspector_clouseau<?php echo $identifier ?>').on('shown', function () {
		$('#inspect<?php echo $identifier ?>_button').text('Hide Inspector');
	})
	<?php javascript_end(); ?>

	</div>


	<?php
}

function messages() {

	global $flash;

if ($flash->message) {?>
	<div class="alert alert-success">
	 	<button class="close" data-dismiss="alert">×</button>
		<?php echo $flash->message; ?>
	</div>
<?php } ?>
<?php if ($flash->error) {?>
	<div class="alert alert-error">
	 	<button class="close" data-dismiss="alert">×</button>
		<?php echo $flash->error; ?>
	</div>

<?php }
?>
<?php if ($flash->warning): ?>
	<div class="alert">
	  <button class="close" data-dismiss="alert">×</button>
	  <strong>Warning - </strong> <?= $flash->warning ?>
	</div>
<?php endif ?>
<?php if ($flash->information): ?>
	<div class="alert alert-info">
	  <button class="close" data-dismiss="alert">×</button>
	  <?= $flash->information ?>
	</div>
<?php endif ?>

<?php
}

function render_exception($heading, $text) {
	set('title', $heading);
	?>

	<div id="exception" class="container">
		<h2><?php echo $heading ?></h2>
		<p><?php echo $text ?></p>
	</div>

	<?php

	logger::Error(strip_tags($text));

}

function start_form($model, $address="", $style="normal") {
	$model->form_header($address, $style);
	$model->setup_security();
}

function end_form($model, $button_text="submit", $cancel=true) {
	if ($button_text == '') $button_text = 'Submit';
	$model->form_security();
	$model->form_button($button_text, $cancel);
	$model->form_footer();
}

function remote_form_buttons($text = 'Save', $cancel = true) {
	?>
	<input type="submit" value="<?php echo $text ?>" name="submit" class="btn btn-primary" id="remote-form-submit-button"/>
	<?php
	if ($cancel === true) {
		echo link_to("Cancel", "back", "", "", array("class" => "btn btn-default", "id" => 'remote-form-cancel-button'));
	}
	else if ($cancel)
		echo link_to("Cancel", $cancel, "", "", array("class" => "btn btn-default", "id" => 'remote-form-cancel-button'));

	javascript_start();

	?>
	$('#remote-form-submit-button').click(function() {
		$('#form-submit-button').trigger('click');
	});

	$('#remote-form-cancel-button').click(function() {
		$('#form-cancel-button').trigger('click');
	});

	<?php
	javascript_end();

}

function blank_banner_image() {
	return '<img src="'.ABSOLUTE.'/img/blank-banner.png">';
}

function debug($object) {
	 ?>
<pre>
	<?php print_r($object) ?>
</pre>
	 <?php
}


?>

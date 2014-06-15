<?php

$structure = array(
	array('Title', 'string'),
	array('email', 'email'),
	array('Number', 'number'),
	array('Password', 'password'),
	array('Content', 'text', 'style' => 'wysiwyg', 'images' => true),
	array('Short Description', 'smallText', 'limit' => '200', 'not_required' => true),
	array('Published', 'yesno', 'default_value' => 'Yes'),
	array('Event Type', 'checkbox', array('Trampolining', 'Hill Climbing', 'Misanthopy'),
		'show' => array('Published' => 'Yes'),
		'hide' => array('Published' => 'No')),
	// show and hide triggers can include || options
	array('Category', 'drop', array('News', 'Sports', 'Events', 'Obituary'), 'default_value' => 'News'),
	array('Gender', 'radio', array('male' => 'Male', 'female' => 'Female')),
	array('Hidden', 'hidden', 'unique' => true),

	array('Start Date', 'datepicker'),
	array('Start Time', 'timepicker', 'style' => 'input-mini', 'not_required' => true),
);

?>

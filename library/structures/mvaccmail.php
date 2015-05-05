<?php


$structure = array(
	array("From", "string", 'short_name' => "mail_from"),
	array("To", "string", 'short_name' => "mail_to"),
	array("Subject", "string", 'short_name' => "subject"),
	array("HTML Message", "text", 'short_name' => "html", 'not_required' => true),
	array("Plain Text Message", "text", 'short_name' => "plain", 'not_required' => true),
	array('Sent', 'yesno', "default_value" => 'No'),
	array('Received', 'yesno', "default_value" => 'No'),
	array('original_message_id', 'id', "not_required" => true),
	array('user_id', 'id', 'not_required' => true)
	);


?>

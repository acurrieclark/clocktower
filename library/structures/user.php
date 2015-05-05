<?php

$structure = array(
			array('Email Address', 'email', 'short_name' => 'email', 'unique' => true, 'unique_message' => "This email has already been used. Please enter a different address."),
			array("Password", "hashed_password", 'unique' => true, 'short_name' => "hashed_password"),
			array("salt", "hashed_password", 'unique' => true),
			array("Level", "drop", array("Administrator", "Editor", "User"), 'short_name' => 'level'),
			array("session_id", "id_code"),
			array("remember_token", "id_code"),
			array("remember_expiry", "timestamp"),
			array("activation_code", "id_code", 'not_required' => true),
			array("password_reset_code", "id_code", 'not_required' => true),
			array("Status", "radio", array("Active", "Pending", "Unverified", "Deactivated"), 'short_name' => 'status'),
			array("Reason for Deactivation", 'string',
				'short_name' => 'reason',
				'show' => array('status' => 'Deactivated'),
				'hide' => array('status' => 'Active || Pending || Unverified'),
				'not_required' => true)
		);


?>

<?php

/**
* newUser
*/
class userForm extends formTemplate
{

	function structure()
	{

		if (is_admin()) {
			$admin_array = array(
				array("User Options", "heading"),
				array("Level", "drop", array("Administrator", "Editor", "User"), 'short_name' => 'level'),
				array("Status", "radio", array("Active", "Pending", "Unverified", "Deactivated"), 'short_name' => 'status'),
				array('model' => 'user', 'element' => "reason")
			);
		}
		else $admin_array = array();

		return array_merge(
		array(
			array("User Details", "heading"),

			array('Title', 'string', 'style' => 'input-mini'),
			array('model' => 'user', 'element'=>"FirstName"),
			array('model' => 'user', 'element'=>"Surname"),
			array('Email Address', "email", 'short_name' => 'email'),
			array("Password", "password", 'confirmation'=>true, 'short_name' => "password"),


			array('model' => 'user', 'element'=>"Gender"),
			array('model' => 'user', 'element'=>"DateofBirth")

			), $admin_array);

	}
}


?>

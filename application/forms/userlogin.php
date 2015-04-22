<?php

/**
* newUser
*/
class userLoginForm extends formTemplate
{
	
	function structure()
	{
		return array(
			array("Email Address", 'email', 'short_name' => 'email'),
			array("Password", "password", 'short_name' => "password"),
			array("", "checkbox", array("Remember me"), 'short_name' => "remember", 'not_required' => true)
		);
	}
}


?>
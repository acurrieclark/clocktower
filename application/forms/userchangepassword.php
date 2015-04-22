<?php

/**
* newUser
*/
class userChangePasswordForm extends formTemplate
{
	
	function structure()
	{
		return array(
			array("Old Password", 'password', 'short_name' => 'old_password'),
			array("New Password", "password", 'confirmation'=> true, 'short_name' => "password")
		);
	}
}


?>
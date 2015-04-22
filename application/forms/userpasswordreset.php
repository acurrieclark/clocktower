<?php

/**
* newUser
*/
class userPasswordResetForm extends formTemplate
{
	
	function structure()
	{
		return array(
			array("Email Address", 'email', 'short_name' => 'email')
		);
	}
}


?>
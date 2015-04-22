<?php

/**
* newUser
*/
class userRegisterForm extends formTemplate
{

	function structure()
	{
		return array(
			array("model" => 'user', 'element' => 'Title'),
			array("model" => 'user', 'element' => 'FirstName'),

			array("model" => 'user', 'element' => 'Surname'),
			array("model" => 'user', 'element' => 'Gender'),
			array("model" => 'user', 'element' => 'DateofBirth'),

			array('Email Address', "email", 'short_name' => 'email'),
			array("Password", "password", 'confirmation'=>true, 'short_name' => "password")

		);
	}
}


?>

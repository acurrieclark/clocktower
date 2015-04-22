<?php

/**
* User Class
*/
class user extends baseUser
{
	 /**
	 * custom validations for profile
	 *
	 * @return void
	 * @author Alex Currie-Clark
	 **/

	function custom_validations()
	{

	}

	function full_name() {
		return $this->FirstName." ".$this->Surname;
	}

}


?>

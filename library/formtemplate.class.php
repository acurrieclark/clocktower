<?php

/**
* Model
*/
class formTemplate extends model
{

	function __construct($data=array())
	{
		parent::__construct($data);
		unset($this->id);
		unset($this->created_at);
		unset($this->updated_at);
	}

}

?>

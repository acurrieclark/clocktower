<?php




class flash
{	

	function __construct($flashes) {
		$this->error = $flashes['error'];
		$this->message = $flashes['message'];
		$this->information = $flashes['information'];
		$this->warning = $flashes['warning'];
		$this->system_error = $flashes['system_error'];
		$this->system_message = $flashes['system_message'];
	}
	
	public function error($message) 
	{
		$this->error = $message;
		logger::Error($message);
	}

	public function message($message) {
		$this->message = $message;
		logger::Message($message);
	}

	public function information($message) {
		$this->information = $message;
		logger::Information($message);
	}

	public function warning($message) {
		$this->warning = $message;
		logger::Warning($message);
	}
	
	public function system_error($message) 
	{
		$this->system_error = $message;
		logger::System_Error($message);
	}

	public function system_message($message) {
		$this->system_message = $message;
		logger::System_Message($message);
	}
	
	function save() {		
		store('message', $this->message);
		store('warning', $this->warning);
		store('information', $this->information);
		store('error', $this->error);
		store('system_error', $this->system_error);
		store('system_message', $this->system_message);
	}
	
	function json_string() {
		if ($this->error) $json_array['error'] = $this->error;
		if ($this->message) $json_array['message'] = $this->message;
		if ($this->information) $json_array['information'] = $this->information;
		if ($this->warning) $json_array['warning'] = $this->warning;
		if ($this->system_error) $json_array['system_error'] = $this->system_error;
		if ($this->system_message) $json_array['system_message'] = $this->system_message;
		
		return json_encode($json_array);
		
	}
	
}


?>
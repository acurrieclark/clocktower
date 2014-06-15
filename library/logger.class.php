<?php

class logger
{

	/**
	 *
	 * @Constructor is set to private to stop instantiation
	 *
	 */
	private function __construct()
	{
	}

	/**
	 *
	 * @write to the logfile
	 *
	 * @access public
	 *
	 * @param	string	$function The name of the function called
	 * @param 	array	$args	The array of args passed
	 * @return	int	The number of bytes written, false other wise
	 *
	 */
	public static function __callStatic($function, $args)
	{
		// args[0] constains the error message
		// args[1] contains the log level
		// args[2] constains the filename
		// args[3] constains the line number

		global $app;

		static $last_logger_function, $logger_has_been_run, $log;

		$mask_log = false;
		$logger_mask = array('Back');

		if ($function == 'write') {
			if ($handle = fopen( ROOT.DS.'tmp/logs/development.log', "a+") )
			{
				if( !fwrite( $handle, $log ))
				{
					throw new Exception("Unable to write to log file");
					return false;
				}
				return true;
			}
		}

		$output = "";
		$function = str_replace('_', ' ', $function);

		if ($logger_has_been_run != true && (isset($app->full_request) || php_sapi_name() == 'cli')) {
			if ($function == 'Not Found') {
				$output .= "\n[Not Found] - ".date('jS \of F Y h:i:s A');
			}
			else if ((isset($app)) && $app->request_type == "ajax")
				$output .= "\n[Ajax Request Received] - ".date('jS \of F Y h:i:s A');
			else
				$output .= "\n[Request Received] - ".date('jS \of F Y h:i:s A');

			if (isset($_SERVER ['REMOTE_ADDR'])) {
				$output .= " from ".$_SERVER ['REMOTE_ADDR'];

				if ($app->static_page) $output .= " for static page";

				if ($function == 'Not Found') {
					$output .= " - ".$app->url;
				}
				else {
					if ($app->full_request['controller'])
						$output .= " - ".$app->full_request['controller'];
					if ($app->full_request['action'])
						$output .= "/".$app->full_request['action'];
					if ($app->full_request['id'])
						$output .= "/".$app->full_request['id'];
				}
				$output .= " via ".$_SERVER['REQUEST_METHOD'];
				if (isset($_SERVER['HTTP_REFERER']))
					$output .= "\nReferred by - ".$_SERVER['HTTP_REFERER'];
				$output .= "\n";
				$output .= $_SERVER['HTTP_USER_AGENT'];

			}
			else $output .= " from SERVER";
			if (!empty($app->get))
				$output .= " with ".json_encode($app->get);
			$output .= "\n";
			$output .= $log;
			$log = '';
		}

		if (!$mask_log || ($mask_log && in_array($function, $logger_mask))) {

			$message = $args[0];

			if ($function != 'Not Found') {

				if ($function != $last_logger_function) {
					$output .= "\n";
					$output .= "[".$function."]\n";
				}

				$message = preg_replace('/"password":"[^"]*"/', '"password":"•••••••••"', $message);
				$message = preg_replace('/"hashed_password":"[^"]*"/', '"hashed_password":"•••••••••"', $message);
				$message = preg_replace('/"password_confirmation":"[^"]*"/', '"password_confirmation":"•••••••••"', $message);
				$message = preg_replace('/"salt":"[^"]*"/', '"salt":"•••••••••"', $message);
				$message = preg_replace('/"activation_code":"[^"]*"/', '"activation_code":"•••••••••"', $message);

				// if the message does not end with a newline character, add one
				if (!(substr($message,-1) == "\n")) {
					$message .= "\n";
				}
				$output .= $message;

			}
		}



		$last_logger_function = $function;

		$log .= $output;
		if (isset($app->full_request) || php_sapi_name() == 'cli')
			$logger_has_been_run = true;

	}

	/**
	 *
	 * Clone is set to private to stop cloning
	 *
	 */
	private function __clone()
	{
	}

} // end of log class

?>

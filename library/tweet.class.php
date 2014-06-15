<?php




class tweet
{	

	function __construct($values) {

	}
	
	static public function update($message) 
	{
		$tmhOAuth = new tmhOAuth(array(
		  'consumer_key'    => TWITTER_CONSUMER_KEY, 
		  'consumer_secret' => TWITTER_CONSUMER_SECRET,
		  'user_token'      => TWITTER_ACCESS_TOKEN, 
		  'user_secret'     => TWITTER_ACCESS_SECRET,
		));
						
		$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
		  'status' => $message ));
		
		  global $flash;
		
		if ($code == 200) {
			// success!
		  	return json_decode($tmhOAuth->response['response']);
		} else {
			$flash->system_error(json_decode($tmhOAuth->response['response'])->error);
			return false;
		}

	}

}


?>
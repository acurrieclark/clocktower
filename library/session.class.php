<?php

/*
  Session class by Stephen McIntyre
  http://stephenmcintyre.net
*/

class session
{
    private $alive = true;
    private $dbc = NULL;

    private static $instance = NULL;

    private function __clone() {}

    private function __construct()
    {
      	session_set_save_handler(
      		array(&$this, 'open'),
      		array(&$this, 'close'),
      		array(&$this, 'read'),
      		array(&$this, 'write'),
      		array(&$this, 'destroy'),
      		array(&$this, 'clean'));
      	session_start();
    }

    public static function start()
    {
  		if (!self::$instance) {
      		self::$instance = new session;
    			self::$instance->verify();
  		}
    }

    /**
     * Verifies the session is valid and has not expired
     *
     * @return void
     * @author Alex Currie-Clark
     **/
    function verify() {

		if (isset($_SESSION['exists'])) {

			// verify user-agent is same
			if($_SESSION['user_agent'] != $_SERVER['HTTP_USER_AGENT']) {
					self::refresh('User agent does not match');
			}
			// verify access within 20 min
			elseif($_SESSION['last_access'] && (time()-SESSION_LIFETIME) > $_SESSION['last_access'] && $_SERVER['REQUEST_METHOD'] != 'POST') {
					self::refresh('Session Expired');
			}
		}
		else {
			logger::Session('Generating new session');
			session_regenerate_id();
		}

		  $_SESSION['exists'] = true;
		  $_SESSION['user_agent']	= $_SERVER['HTTP_USER_AGENT'];
		  $_SESSION['last_access']	= time();

		  self::clean(SESSION_LIFETIME * 5);

		  return true;
	}

    function __destruct()
    {
      	if($this->alive) {
			session_write_close();
			self::$instance = false;
        	$this->alive = false;
      	}
    }

	function refresh($message = "") {
		if ($message)
			logger::Session($message);
		session_regenerate_id(true);
	}

    function delete()
    {
      if(ini_get('session.use_cookies'))
      {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
          $params['path'], $params['domain'],
          $params['secure'], $params['httponly']
        );
      }

      session_destroy();

      $this->alive = false;
    }

    private function open()
    {
      $this->dbc = new dbabstraction;
      return true;
    }

    private function close()
    {
      return;
    }

    private function read($sid)
    {
    	$q = "SELECT `data` FROM `sessions` WHERE `id` = '".dbAbstraction::check_input($sid)."' LIMIT 1";
      	$stmt = $this->dbc->query($q);
		$stmt->execute();
		$result = $stmt->fetch(PDO::FETCH_ASSOC);


      	if($result) {
	      	return $result['data'];
      	}
      	else {
      		return '';
      	}
    }

    private function write($sid, $data)
    {
      	$q = "REPLACE INTO `sessions` (`id`, `data`) VALUES ('".dbAbstraction::check_input($sid)."', '".$this->dbc->check_input($data)."')";
      	$stmt = $this->dbc->query($q);
		return $stmt->execute();
    }

    function destroy($sid)
    {
      	$_SESSION = array();

      	$q = "DELETE FROM `sessions` WHERE `id` = '".dbAbstraction::check_input($sid)."'";
      	$stmt = $this->dbc->query($q);
  		return $stmt->execute();
    }

    private function clean($expire)
	{
      	$q = "DELETE FROM `sessions` WHERE DATE_ADD(`last_accessed`, INTERVAL ".(int) $expire." SECOND) < NOW()";
    	$stmt = $this->dbc->query($q);
		return $stmt->execute();
    }
}

?>

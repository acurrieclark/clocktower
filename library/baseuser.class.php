<?php

/**
* user
*/
class baseUser extends systemModel
{

	function __construct($user = array())
	{
		if (!empty($user)) {
			if (isset($user['password'])) {
				$user['salt'] = user::hash_password(mt_rand(),mt_rand());
				$user['hashed_password'] = user::hash_password($user['password'], $user['salt']);
			}
			if (!isset($user['level']) && !(is_admin())) {
				$user['level'] = 'User';
				$user['status'] = 'Pending';
			}
		}
		parent::__construct($user);
		if (isset($user['password']))
			$this->hashed_password->changing = true;
		if (!is_admin()) {
			$this->level->display = false;
			$this->status->display = false;
		}
	}

	function save($keep_id = false, $run_validations = true) {
		if (!$this->hashed_password->changing) {
			unset($this->hashed_password);
			unset($this->salt);
		}
		if (!$this->session_id->changing) {
			unset($this->session_id);
			unset($this->remember_token);
			unset($this->remember_expiry);
		}
		return parent::save($keep_id, $run_validations);
	}

	function update($verify = true, $mask_commit = false) {
		logger::Model('Checking user');

		$user_check = user::find(array('where' => array('id' => $this->id->value)));
		if ($this->level->value != $user_check->level->value && !is_admin() && $this->level->value != "Unverified") {
			$this->level->value = $user_check->level->value;
			logger::error('Illegal User Upgrade Attempted');
		}
		if ($this->status->value == 'Unconfirmed' && $user_check->status->value == 'Active' && !is_admin()) {
			$this->status->value = $user_check->status->value;
			logger::error('Illegal User Activation Attempted');
		}

		if (!is_admin() && $this->status->value !== 'Unverified') {
			$this->status->value = $user_check->status->value;
		}

		if (!$this->hashed_password->changing) {
			unset($this->hashed_password);
			unset($this->salt);
		}
		if (!$this->session_id->changing) {
			unset($this->session_id);
			unset($this->remember_token);
			unset($this->remember_expiry);
		}
		return parent::update($verify);
	}

	function hash_password($password, $salt)
	{
	$hasher = new PasswordHash(8, false);
	$hash = $hasher->HashPassword($password . $salt);
	if (strlen($hash) != 60) {
		error('Failed to hash new password');
		redirect_to('users','new');
	}
	unset($hasher);
	return $hash;
	}

	static function login($email, $password, $remember = false) {
		logger::back(json_encode($login_redirect));
		$user = user::find(array('where' => array('email' => $email)));
		if (!empty($user)) {
			if ($user->status->value != 'Active') {
				return false;
			}
			$pass_check = user::check_password($user->hashed_password, $user->salt->value, $password);
			if (!$pass_check) {
				logger::Invalid('Password did not match');
				return false;
			}
			$saved_session = $_SESSION;
			session_regenerate_id(true);
			$_SESSION = $saved_session;
			$user->session_id->value = session_id();
			if($remember) {
			    $cookie_auth= mt_rand() . $email;
			    $auth_key = md5($cookie_auth);
				$user->remember_token->value = $auth_key;
				$expiry_time = time() + 60 * 60 * 24 * 30;
			    setcookie("auth_key", $auth_key, $expiry_time, '/', $_SERVER['HTTP_HOST']);
				$user->remember_expiry->value = date( 'Y-m-d H:i:s', $expiry_time );
			}
			else {
				$user->remember_token->value = "no_token";
				$user->remember_expiry->value = date( 'Y-m-d H:i:s', 0 );
			}
			$user->session_id->changing = true;
			$user->password_reset_code->value = 'no_code';
			$user->update(false);
			unset($user->hashed_password);
			unset($user->salt);
			set_current_user($user);
		}
		else set_current_user(false);
		user::set_security_timeout();
	}

	static function check_password($hashed_password, $salt, $password) {
		$hasher = new PasswordHash(8, false);
		$check = $hasher->CheckPassword($password . (string)$salt, $hashed_password);
		unset($hasher);
		return $check;
	}

	static function autoload_user() {
		$user = user::load_from_session();
		if (!$user) $user = user::load_from_cookie();
		if ($user && $user->status->value == 'Active') {
			$user->update_remember_me();
			unset($user->hashed_password);
			unset($user->salt);
			unset($user->session_id);
			unset($user->remember_token);
			unset($user->remember_expiry);
			set_current_user($user);
		}
		else set_current_user(false);
		user::set_security_timeout();
	}

	static function load_from_session() {
		if (session_id() != "" && session_id() != "no_id") {
			$user = user::find(array('where' => array('session_id' => session_id())));
			if ($user)
				return $user;
		}
		else return false;
	}

	static function load_from_cookie() {
		if (isset($_COOKIE['auth_key']) && $_COOKIE['auth_key'] != "no_token" && $_COOKIE['auth_key'] != "") {
			$user = user::find(array( 'where' => array('remember_token' => $_COOKIE['auth_key'])));
			if ($user) {
				return $user;
			}
			else return false;
		}
		else return false;

	}

	private static function set_security_timeout() {
		global $app;
		logger::user('Setting appropriate security timeout');
		if (is_admin()) {
			$app->security_token_lifetime = (SECURITY_TOKEN_LIFETIME * 10);
		}
		else if (logged_in()) {
			$app->security_token_lifetime = (SECURITY_TOKEN_LIFETIME * 2);
		}
	}

	private function update_remember_me() {
			if (strtotime($this->remember_expiry) > time()) {
				$expiry_time = time() + 60 * 60 * 24 * REMEMBER_ME_LIFETIME;
			    setcookie("auth_key", $this->remember_token, $expiry_time, '/', $_SERVER['HTTP_HOST']);
				$this->remember_expiry->value = date( 'Y-m-d H:i:s', $expiry_time );
				$this->session_id->changing = true;
				$this->session_id->value = session_id();
				$this->update(false);
			}
	}

	function update_password($old_password, $password, $skip_check = false) {
		if (!$this->check_own()) {
			redirect_to();
		}
		if (is_admin() || user::check_password($this->hashed_password, $this->salt, $old_password) || $skip_check) {
			$this->hashed_password->changing = true;
			$this->salt->value = user::hash_password(mt_rand(),mt_rand());
			$this->hashed_password->value = user::hash_password($password, $this->salt);
			if ($this->update())
				return true;
			else return false;
		}
		else {
			$this->password->error = incorrect_password;
			return false;
		}
	}

	function logout() {
		if (current_user()) {
			set_current_user(false);
			$user = user::load_from_session();
			if (!$user) $user = user::load_from_cookie();
			if ($user) {
				$user->session_id->value = "no_id";
				$user->remember_token->value = "no_token";
				$user->remember_expiry->value = date( 'Y-m-d H:i:s', 0 );
				$user->session_id->changing = true;
				$user->update(false);
			}
			session::delete();
			return true;
		}
		else return false;
	}

	function check_own() {
		if (!is_admin()) {
			if ($this->id->value == current_user()->id->value)
				return true;
			else return false;
		}
		else return true;
	}

}



?>

<?php

/**
*  usersController
*/
class usersController extends applicationController
{

	function __construct()
	{
		parent::__construct();
		$this->template->set('header-title', 'Users');
		$this->current_menu_selection = 'users';
		$this->before_filters = array(
						'check_and_fetch_user' => array(
							'edit',
							'update',
							'change_password',
							'update_password',
							'delete',
							'verify',
							'deactivate',
							'activate'
						),
						"must_be_logged_in" => array(
							'except' => array(
								'confirm',
								'register',
								'create_registration',
								'confirmation_thankyou',
								'confirmation_details',
								'password_reset',
								'reset_password',
								'new_password',
								'save_new_password',
								'login',
								'create_login'
							)
						),
						'must_be_admin' => array(
							'verify',
							'deactivate',
							'activate',
							'delete',
							'new',
							'create',
							'resend_confirmation_email')
					);

	}

	function _index() {

	}

	function _list() {

		$type = isset($this->get['type']) ? $this->get['type'] : '';

		if (is_admin()) {

			$status = (isset($this->get['status'])) ? $this->get['status'] : '';

		}
		$params_array = array();
		$order = array();

		if (isset($this->get['by'])) {
			$order = array($this->get['by'] => strtoupper($this->get['order']));
			if ($this->get['by'] != "Surname")
				$order['Surname'] = 'ASC';
		}
		else if (!is_admin()) {
			$order['Surname'] = 'ASC';
		}

		if (isset($this->get['search'])) {
			$search = $this->get['search'];
			$search = str_replace(' ', '%', '%'.$search.'%');
			$search = check_input($search);
			$params_array['query'] = "(CONCAT_WS(' ', FirstName, Surname) LIKE '$search')";
		}

		if (!is_admin()) {
			if (isset($params_array['query']))
				$params_array['query'] .= ' AND ';
			$params_array['query'] .= "(status='active')";
		}


		$where = $params_array;
		$this->all = user::find_all(array('where' => $where, 'order' => $order, 'page' => $this->get['page']));

		$this->all_count = user::count(array('where' => $params_array, 'order' => $order));

		$this->users_count = $this->all_count;

		$this->users = $this->all;


		if (is_admin()) {

			$this->use_template('admin');

			if ($status)
				$this->$status = user::find_all(array('where' => array_merge($where, array('status' => $status)), 'order' => $order));

			$this->active_count = user::count(array('where' => array_merge($where, array('status' => 'active'))));
			$this->deactivated_count = user::count(array('where' => array_merge($where, array('status' => 'deactivated'))));
			$this->pending_count = user::count(array('where' => array_merge($where, array('status' => 'pending'))));
			$this->unverified_count = user::count(array('where' => array_merge($where, array('status' => 'unverified'))));

			if ($status) {
				$this->users = $this->$status;
				$status_count_name = $status."_count";
				$this->users_count = $this->$status_count_name;
			}
			else $this->users = $this->all;

		}

	}

	function _new() {
		$this->form = new userForm($this->posted);
	}

	function _create() {
		$this->form = new userForm($this->posted);
		$user = new user($this->posted);
		$main_user = $user->validate();
		$form = $this->form->validate();
		if ($main_user && $form) {
			if ($user->status->value == "Pending")
				$this->activation_code = md5(mt_rand());
				$user->activation_code->value = sha1($this->activation_code);
			$user->save();
			flash($user->full_name().' added to users.');
			redirect_to('users', 'list');
		}
		else {
			merge_errors($user, $this->form);
			error('There appears to be an error with the form.');
			render_action('new');
		}
	}


	function check_and_fetch_user() {
		if (isset($this->posted['id']) && $this->posted['id'] != $this->id) {
			error('User not found - ID mismatch');
			redirect_to();
		}
		if (access_ok($this->id,'users')) {
			$this->user = user::find_by_id($this->id);
		}
		if (!$this->user) {
			error('User not found');
			redirect_to();
		}
	}

	function _login() {
		if (current_user()) {
			flash('You are already logged in');
			redirect_to();
		}
		$this->form = new userLoginForm($this->posted);
	}

	function _create_login() {
		$this->form = new userLoginForm($this->posted);
		user::login($this->posted['email'], $this->posted['password'], $this->posted['remember']);
		if ($this->current_user && $this->form->validate()) {
			flash('Login Successful');
			$login_redirect = get_stored('back_after_login');
			if ($login_redirect) {
				logger::Redirecting("After Login");
				$this->back = $login_redirect;
				remove_stored('back_after_login');
				redirect_to('back');
			}
			else
			redirect_to(LOGIN_REDIRECT);
		}
		else {
			$this->form->email->error = not_found;
			if (!$this->form->password->error) {
				$this->form->password->error = not_found;
			}
			error('The email and password combination do not match. Please try again');
			render_action('login');
		}
	}

	function _logout() {
		if(user::logout())
			flash('Successfully logged out. Thank you.');
		redirect_to();
	}

	function _register() {
		if (current_user()) {
			error('No need to register. You are already logged in!');
			redirect_to('users');
		}
		$this->form = new userRegisterForm($this->posted);
	}

	function _create_registration() {
		$this->form = new userRegisterForm($this->posted);
		$this->user = new user($this->posted);
		$main_user = $this->user->validate();
		$form = $this->form->validate();
		if ($main_user && $form) {
				$this->user->status->value = 'Pending';
				$this->activation_code = md5(mt_rand());
				$this->user->activation_code->value = sha1($this->activation_code);
				$this->user->save();

				$html_email = $this->html_email('email', 'user_activation_email');
				$text_email = $this->text_email('email', 'user_activation_email');
				mailer::send($this->user->email->value, USERS_EMAIL_ADDRESS, "Activation code for ".$this->user->full_name(), $text_email, $html_email);

				flash('Thank you for registering.');
				store('signup_email', $this->user->email->value);
				redirect_to('users', 'confirmation_details');
			}
		else {
			merge_errors($this->user, $this->form);
			error('There appears to be an error with the form.');
			render_action('register');
		}
	}

	function _resend_confirmation_email() {
		$this->user = user::find_by_id($this->id);
		if ($this->user && $this->user->status->value == 'Pending') {
				$this->activation_code = md5(mt_rand());
				$this->user->activation_code->value = sha1($this->activation_code);
				$this->user->update();

				$html_email = $this->html_email('email', 'user_activation_email');
				$text_email = $this->text_email('email', 'user_activation_email');
				mailer::send($this->user->email->value, USERS_EMAIL_ADDRESS, "Activation code for ".$this->user->full_name(), $text_email, $html_email);

				flash('Activation code has been regenerated and resent');
				redirect_to('users', 'list');
		}

	}


	// form to get email address for password reset
	function _password_reset()
	{
		$this->form = new userPasswordResetForm($this->posted);
	}

	// provides a password reset code and emails link
	function _reset_password() {
		$this->form = new userPasswordResetForm($this->posted);
		$this->user = user::find(array('where' => array('email' => $this->posted['email'], 'status' => 'Active')));
		if (!empty($this->user)) {
			$this->reset_code = md5(mt_rand());
			$this->user->password_reset_code->value = sha1($this->reset_code);
			$this->user->update();
			flash("An email has been sent to ".show_safely($this->posted['email'])." with instructions on how to reset your password.");
			$html_email = $this->html_email('email', 'password_reset_email');
			$text_email = $this->text_email('email', 'password_reset_email');
			mailer::send($this->user->email->value, USERS_EMAIL_ADDRESS, "Password Reset - ".$this->user->full_name(), $text_email, $html_email);
			redirect_to();
		}
		else {
			error('The email address you entered does not match.');
			$this->form->email->error = 'does not match';
			render_action('password_reset');
		}
	}

	function _new_password() {
		if ($this->id != '' && $this->id && $this->id != 'no_code')
			$this->user = user::find(array('where' => array ('password_reset_code' => substr(sha1($this->id), 0 , 32))));
		if (!empty($this->user) && !current_user()) {
			$this->form = new userChangePasswordForm($this->posted);
			unset($this->form->old_password);
		}
		else if (current_user()) {
			error('You are already logged in!');
			redirect_to('users');
		}
		else {
			error('User not found');
			redirect_to();
		}
	}

	function _save_new_password() {
			$this->form = new userChangePasswordForm($this->posted);
			unset($this->form->old_password);
			if ($this->id != '' && $this->id && $this->id != 'no_code')
				$this->user = user::find(array('where' => array ('password_reset_code' => substr(sha1($this->id), 0, 32))));
			if ($this->user && $this->form->validate()) {
				set_current_user($this->user);
				if ($this->user->update_password(false, $this->posted['password'], true)) {
					$this->user->password_reset_code->value = '';
					$this->user->update();
					$html_email = $this->html_email('email', 'password_been_reset_email');
					$text_email = $this->text_email('email', 'password_been_reset_email');

					mailer::send($this->user->email->value, USERS_EMAIL_ADDRESS, "Your Password has been reset - ".$this->user->full_name(), $text_email, $html_email);

					flash('Your password has been changed. Please login using your new password to continue.');
					redirect_to('login');
				}
				else {
					if ($this->user->password->error)
						$this->form->old_password->error = $this->user->password->error;
					error('There appears to be a problem with the form');
				}
				set_current_user(false);
			}
			else if (!$this->user)
				error('User could not be found');
			else {
				error('There appears to be a problem with the form');
			}
			render_action('new_password');
	}

		function _confirm() {
		$this->user = user::find(array('where' => array('activation_code' => substr(sha1($this->id), 0, 32))));
		if ($this->user && $this->id) {
			$this->user->status->value = 'Unverified';
			$this->user->activation_code->value = "";
			if ($this->user->update()) {
				$html_email = $this->html_email('email', 'new_user_email');
				$text_email = $this->text_email('email', 'new_user_email');

				mailer::send(ADMIN_EMAIL_ADDRESS, USERS_EMAIL_ADDRESS, "New User - ".$this->user->full_name(), $text_email, $html_email);

				flash('Thank you for confirming your email address.');
				redirect_to('users', 'confirmation_thankyou');
			}
			else {
				error('Your details could not be saved. We are aware of the problem and will be in touch soon.');

				mailer::send('acurrieclark@gmail.com', USERS_EMAIL_ADDRESS, "Confirmation error - ".$this->user->full_name(), $this->user->full_name()." - $this->id did not work");

				redirect_to('');
			}

		}
		else {
			error('The member confirmation code provided is incorrect');
			redirect_to('');
		}
	}

	// shown after a user has signed up for the first time
	function _confirmation_details() {
		if (!isset($_SESSION['signup_email']))
			redirect_to();
	}

	function _confirmation_thankyou() {

	}

	function _edit() {
			$this->form = new userForm($this->posted);
			unset($this->form->password);
			unset($this->form->password_confirmation);
			populate($this->user, $this->form);
			if ($this->user->status->value == 'Pending') {
				warning('Pending users should not be activated until their email address has been confirmed.');
			}
	}

	function _update() {
		$this->form = new userForm($this->posted);
		if (isset($this->form->LinkedIn->value))
			$this->form->LinkedIn->value = add_http($this->form->LinkedIn->value);
		unset($this->form->password);
		unset($this->form->password_confirmation);
		populate($this->form, $this->user);
		if ($this->form->validate()) {
			$this->user->update(false);
			flash($this->user->full_name()." - User profile updated");
			$html_email = $this->html_email('email', 'user_details_updated_email');
			$text_email = $this->text_email('email', 'user_details_updated_email');
			mailer::send(ADMIN_EMAIL_ADDRESS, USERS_EMAIL_ADDRESS, 'User Details Updated - '.$this->user->full_name(), $text_email, $html_email);
			if (is_admin())
				redirect_to('users', 'list');
			else
				redirect_to('users');
		}
		else {
			merge_errors($this->user, $this->form);
			error("There was a problem with the form, please check and resubmit.");
			render_action('edit');
		}
	}

	function _show() {
		$this->user = user::find_by_id($this->id);
		if (!$this->user) {
			error('User not found');
			redirect_to('users');
		}
	}

	function _change_password() {
		if (access_ok($this->id)) {
			$this->form = new userChangePasswordForm($this->posted);
			if (is_admin()) {
				unset($this->form->old_password);
			}
			else {
				$this->user = current_user();
			}
			if (is_admin() && $this->user->id->value != current_user()->id->value && $this->user) {
				warning('You are changing the password for user '.$this->user->email->value);
			}
		}
	}

	function _update_password() {
			$this->form = new userChangePasswordForm($this->posted);
			if (is_admin()) {
				unset($this->form->old_password);
			}
			if ($this->user && $this->form->validate()) {
				if ($this->user->update_password($this->posted['old_password'], $this->posted['password'])) {
					flash($this->user->full_name().' - Password Changed');
					if (is_admin())
						redirect_to('users', 'list');
					else redirect_to('users');
				}
				else {
					if ($this->user->password->error)
						$this->form->old_password->error = $this->user->password->error;
					error('There appears to be a problem with the form');
				}
			}
			else if (!$this->user)
				error('User could not be found');
			else {
				error('There appears to be a problem with the form');
			}
			render_action('change_password');
	}

	function _verify() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			$old_user = $this->user;
			$this->user->status->value = 'Active';

			if ($this->user->update()) {

				$html_email = $this->html_email('email', 'verified_email');
				$text_email = $this->text_email('email', 'verified_email');

				mailer::send($this->user->email->value, USERS_EMAIL_ADDRESS, "User Verified - ".$this->user->full_name(), $text_email, $html_email);
				flash($this->user->full_name().' has been verified');
				redirect_to('back');
			}
			else {
				error('User details could not be updated');
				redirect_to('back');
			}
		}
		else {
			error('Incorrect request method');
			redirect_to();
		}

	}

	function _deactivate() {
		if ($_SERVER['REQUEST_METHOD'] == 'POST')
		{
			if ($this->get['reason']) {
				$this->user->reason->value = $this->get['reason'];
			}

			if ($this->user->level->value == 'Administrator') {
				error('Administrator accounts cannot be deactivated. Please reduce user level first, then deactivate.');
				redirect_to('back');
			}
			else {
				$this->user->status->value = 'Deactivated';
				if ($this->user->update()) {
					flash($this->user->full_name().' has been deactivated');
					redirect_to('back');
				}
				else {
					error('User details could not be updated');
					redirect_to('back');
				}
			}
		}
		else {
			error('Incorrect request method');
			redirect_to();
		}
	}

	function _activate() {
		$this->user->status->value = 'Active';
		if ($this->user->update()) {
			flash($this->user->full_name().' has been activated');
			redirect_to('back');
		}
		else {
			error('User details could not be updated');
			redirect_to('back');
		}
	}

	function _delete() {
		if ($this->user->level->value == 'Administrator') {
			error('Administrator accounts cannot be deleted. Please reduce user level first, then try again.');
			redirect_to('back');
		}
		else if ($this->user->destroy()) {
			flash($this->user->full_name().' has been deleted');
			redirect_to('back');
		}
		else {
			error('User details could not be deleted');
			redirect_to('back');
		}
	}

	function _contact() {
		if ($_SERVER['REQUEST_METHOD'] != 'POST' || ($this->posted['sender_id'] != current_user()->id->value)) {
			redirect_to();
		}
		else {
			$this->message = new user_message($this->posted);
			if ($this->message->validate()) {
				$this->to = user::find_by_id($this->message->recipient_id->value);
				$html_email = $this->html_email('email', 'member_message');
				$text_email = $this->text_email('email', 'member_message');

				if (mailer::send($this->to->email->value, USERS_EMAIL_ADDRESS, "Message from ".current_user()->full_name(), $text_email, $html_email)) {
					$this->message->save();
					flash('Message Sent');
				}
				else error('Message sending error');

				redirect_to('back');

			} else  {
				error('The message you sent was blank. Please try again.');
				redirect_to('back');
			}
		}
	}

}


?>

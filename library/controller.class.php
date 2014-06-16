<?php

/**
* baseController
*/
class baseController
{

	protected $reset_security_flag, $construct_completed;

	public $ajax_return, $controller_error, $method_error, $before_filters, $security_token_lifetime;

	function __construct() {

		session::start();

		$this->template = new template;

		$this->reset_security_flag = false;
		$this->construct_completed = true;

		// check for identifiable ajax request
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			$this->set_request_type('ajax');
		}
		else $this->set_request_type('http');

	}

	function setup($properties) {

		global $flash;

		if (!($this->security_token_lifetime)) {
			$this->security_token_lifetime = SECURITY_TOKEN_LIFETIME;
		}

		foreach ($properties as $property => $value) {
			$this->$property = $value;
		}

		// if a file which does not exist has been requested respond with 404 header and no html
		if (preg_match('/\S*\.\S{1,}/',$this->url)) {
			logger::Not_Found();
			$this->file_not_found = true;
		}

		if ($this->controller_error || $this->method_error) {
			logger::Not_Found();
		}

		if (!$this->construct_completed) {
			logger::Warning("$this->controller controller __construct does not include parent::__construct() function");
			logger::write();
		}

		// autoload the user if we have a valid page and if we are using users
		if (!$this->file_not_found && !$this->controller_error && !$this->method_error && AUTOLOAD_USER) {
			user::autoload_user();
		}

		$flash = new flash(array('message' => retrieve_and_remove('message'),
								'error' => retrieve_and_remove('error'),
								'information' => retrieve_and_remove('information'),
								'warning' => retrieve_and_remove('warning'),
								'system_error' => retrieve_and_remove('system_error'),
								'system_message' => retrieve_and_remove('system_message'),
								));

		$this->setup_back();

		$this->verify_posted();

		if (empty($this->get)) $this->get = array();

		if (DEVELOPMENT_ENVIRONMENT == true) {
			if ($this->posted)
				logger::Posted(json_encode($this->posted));
			else if ($this->delete) {
				logger::Delete(json_encode($this->delete));
			}
		}

	}

	protected function set_request_type($type) {
		if ($type == 'ajax') {
			ob_start();
			$this->request_type = 'ajax';
		}
		if ($type == 'http') {
			$this->request_type = 'http';
		}
	}

	private function setup_back()
	{

		if (!isset($this->get['iframe']) && ($this->request_type != 'ajax')) {
			if (isset($_SESSION['back'])) {
				$this->back = $_SESSION['back'];
				$this->doubleback = $_SESSION['doubleback'];
			}
			else {
				$this->back = '';
				$this->doubleback = '';
			}

			if ($this->full_request == $this->back) {
				$this->back = $_SESSION['doubleback'];
			}
		}

	}

	function finalise_back() {
			if ($this->full_request != $this->back && !isset($this->get['iframe']) && ($this->request_type != 'ajax') && !($this->controller_error || ($this->method_error) || $this->file_not_found)) {
				$_SESSION['doubleback'] = $this->back;
				$_SESSION['back'] = $this->full_request;
			}

			// this attempts to resolve redirection after login, but is not quite right
			// basically, we want to remove the 'back after login' session variable but
			// only if we are clearly not going to use it.

			if (isset($_SESSION['back_after_login']) && isset($this->doubleback) && ($_SESSION['back_after_login'] != $this->doubleback && $_SESSION['back_after_login'] != $_SESSION['doubleback'] && $_SESSION['back_after_login'] != $_SESSION['back'])) {
				unset($_SESSION['back_after_login']);
			}


	}

	function activate_reset_security() {
		$this->reset_security_flag = true;
	}

	function cancel_reset_security() {
		$this->reset_security_flag = false;
	}

	function reset_security($force = false) {
		if (($this->reset_security_flag && ($_SERVER['REQUEST_METHOD'] == "POST" ||  $_SERVER['REQUEST_METHOD'] == "DELETE" || $_SERVER['REQUEST_METHOD'] == "PUT")) || $force) {
			logger::Security('Resetting Security');
			unset($this->security_token);
			unset($_SESSION['SecurityTokenTime']);
			unset($_SESSION['SecurityToken']);
			unset($_SESSION['SecurityTokenCount']);
		}
	}

	private function verify_posted() {

		if ($_SERVER['REQUEST_METHOD'] == "POST" ||  $_SERVER['REQUEST_METHOD'] == "DELETE" || $_SERVER['REQUEST_METHOD'] == "PUT") {
			$tokenAge = time() - $_SESSION['SecurityTokenTime'];

			$security_token = ($this->posted['security_token']) ? $this->posted['security_token'] : $this->get['security_token'];

			if ($_SERVER['REQUEST_METHOD'] == 'DELETE' && !$security_token) {
				$security_token = $this->delete['security_token'];
			}

			if (!$security_token || ($security_token != $_SESSION['SecurityToken']) && ($tokenAge <= $this->security_token_lifetime)) {
				unset($_SESSION['SecurityTokenTime']);
				unset($_SESSION['SecurityToken']);
				unset($_SESSION['SecurityTokenCount']);
				error('The form you have submitted contains an incorrect security token');
				logger::Error('Forgery Detected');
				redirect_to();
			}
			else if (!($tokenAge <= $this->security_token_lifetime)) {
				unset($_SESSION['SecurityTokenTime']);
				unset($_SESSION['SecurityToken']);
				unset($_SESSION['SecurityTokenCount']);
				error('The form you have submitted has expired. Please refresh or try again.');
				redirect_to('back');
			}
			else if ($_SESSION['SecurityTokenCount'] > 10) {
				unset($_SESSION['SecurityTokenTime']);
				unset($_SESSION['SecurityToken']);
				unset($_SESSION['SecurityTokenCount']);
				error('You have submitted the same form too many times. Please try starting again.');
				redirect_to('back');
			}
			else {
				$this->security_token = ($_SESSION['SecurityToken']);
				if ($this->request_type != 'ajax') $_SESSION['SecurityTokenCount']++;
			}
		}
	}

	function ajax_response($ajax) {
		$this->ajax_respond = $ajax;
	}

	function set_action_to_render($action) {
		$this->action_to_render = $action;
	}

	function html_email($template = 'email', $email_content_template = false) {

		include(ROOT . DS . 'library/vendor/CssToInlineStyles/CssToInlineStyles.php');

		ob_start();

		include(ROOT.DS.'application/views/mailers/'.$template.'.php');

		$html = ob_get_clean();

		ob_start();

		include(ROOT.DS.'public/css/email.css');

		$css = ob_get_clean();

		$inliner = new CssToInlineStyles($html, $css);

		$inliner->setEncoding('utf-8');

		return $inliner->convert();

	}

	function text_email($template, $email_content_template = false) {

		include(ROOT . DS . 'library/vendor/html2text.class.php');

		ob_start();

		include(ROOT.DS.'application/views/mailers/'.$template.'.php');

		$html = ob_get_clean();

		return html2text::convert($html);

	}


	function use_template($template) {
		if (file_exists(ROOT . DS . 'application' . DS . 'views'. DS  .$this->controller.DS.$template.'_template.php'))
			$this->template_to_use = ROOT . DS . 'application' . DS . 'views'. DS  .$this->controller.DS.$template.'_template.php';
		else if (file_exists(ROOT . DS . 'application' . DS . 'views'. DS  .'application'.DS.$template.'_template.php'))
			$this->template_to_use = ROOT . DS . 'application' . DS . 'views'. DS  .'application'.DS.$template.'_template.php';
		else {
			logger::Template('Designated template could not be found. Remember that "_template" is automatically added to the name. Using Default');
			$this->template_to_use = false;
		}
		logger::Template($this->template_to_use);
	}

	function render() {
		global $flash;

		$this->finalise_back();
		$this->reset_security();

		if ($this->request_type == 'ajax') {
			if ($this->controller_error || $this->method_error) {
				error('The requested asset does not exist');
				redirect_to();
			}
			else {
				if (!header_already_set())
					header('Content-type: application/json');
				logger::Sending("Ajax Response");
				logger::Sending("Headers: ". json_encode(headers_list()));
				$response_output = ob_get_clean();
				if (strlen($response_output) > 0) {
					logger::Sending($response_output);
					echo $response_output;
				}
				else {
					echo $flash->json_string();
					logger::Sending(stripslashes(json_encode($flash->json_string())));
				}
			}
		}
		else {

			add_stylesheet('system/bootstrap.min');
			add_stylesheet('main');

			add_javascript('system/jquery-1.11.0.min');
        	add_javascript('system/modernizr-2.6.2-respond-1.1.0.min');
        	add_javascript('main');
        	add_javascript('system/bootstrap.min');

			try {

				// add default stylesheet and javascript if present
				if (file_exists(ROOT . DS . 'public' . DS . 'css'. DS .$this->controller. '.css'))
					add_stylesheet($this->controller);
				if (file_exists(ROOT . DS . 'public' . DS . 'js'. DS .$this->controller. '.js'))
					add_javascript($this->controller);

				if (!isset($this->action_to_render))
					$this->action_to_render = $this->action;

				$this->template->content_start("main");

				if ((DEVELOPMENT_ENVIRONMENT != true) && ($this->controller_error || ($this->method_error) || $this->file_not_found)) {
					require_once (ROOT . DS. 'public' . DS . '404.php');
				}
				else {
					if ($this->file_not_found) {
						header("HTTP/1.0 404 Not Found");
						render_exception("File Not Found", "The file <strong>'$this->url'</strong> does not exist.");
					}
					else if ($this->controller_error) {
						header("HTTP/1.0 404 Not Found");
						render_exception("Controller not found", "The controller <strong>'$this->controller'</strong> does not exist.");
					}
					else if ($this->method_error) {
						header("HTTP/1.0 404 Not Found");
						render_exception("Method not found", "The method <strong>'$this->action_to_render'</strong> does not exist for the controller <strong>'$this->controller'</strong>. Please check the controller file.");
					}
					else {
						// if the page is static, load it
						if ($this->static_page) require_once(ROOT."/application/static/$this->controller.php");
						// if we know the view to be rendered, load that
						else if (file_exists(ROOT . DS . 'application' . DS . 'views'. DS .$this->controller. DS . $this->action_to_render .'.php'))
							require_once(ROOT . DS . 'application' . DS . 'views'. DS .$this->controller. DS . $this->action_to_render .'.php');
						// otherwise throw an exception
						else render_exception("View for method '$this->action_to_render' could not be found.", "Please ensure the view file has been named correctly and is in the correct folder.");
					}
				}
				content_end();

				if (isset($this->template_to_use) && $this->template_to_use)
					require($this->template_to_use);
				else if (file_exists(ROOT . DS . 'application' . DS . 'views'. DS  .$this->controller.DS.'template.php'))
					require( ROOT . DS . 'application' . DS . 'views'. DS .$this->controller.DS.'template.php');
				else if (file_exists(ROOT . DS . 'application' . DS . 'views'. DS . 'application'. DS . 'template.php'))
					require(ROOT . DS . 'application' . DS . 'views'. DS . 'application'. DS . 'template.php');
				else
					render_exception("There does not appear to be a valid template file", "Please create a template.php file in application/views/application for your controller or application.");

				if ($this->controller_error || $this->method_error || $this->file_not_found)
					$logger_string = '404.php';
				else {
					$action_string = ($this->action_to_render && $this->action_to_render != 'index') ? " - ".$this->action_to_render : '' ;

					$logger_string = $this->controller.$action_string;
				}

				if ($this->id != "") $logger_string .= " - ".$this->id;
					logger::Rendering($logger_string);

			}
			catch(Exception $e){
				error($e->getMessage());
			}
		}
		logger::write();
	}

	function render_partial ($name, $variables = array()) {

		if (file_exists(ROOT . DS . 'application' . DS . 'views'. DS  .$this->controller.DS.'_'. $name.'.php'))
			include(ROOT . DS . 'application' . DS . 'views'. DS  .$this->controller.DS.'_'. $name.'.php');
		else if (file_exists(ROOT . DS . 'application' . DS . 'views'. DS  .'application'.DS.'_'. $name.'.php'))
			include(ROOT . DS . 'application' . DS . 'views'. DS  .'application'.DS.'_'. $name.'.php');
		else render_exception("Partial Missing", "The partial \"$name\" you have requested does not appear to exist. Please check.");
		}


}


?>

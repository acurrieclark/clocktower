<?php

/**
* Application Controller
*/
class applicationController extends baseController
{

	function __construct() {
		parent::__construct();
		$this->template->set('header-title', '');
	}

	function must_be_logged_in() {
		must_be_logged_in();
	}

	function must_be_admin() {
		if (!is_admin()){
			error("You need to be an administrator to view that page");
			redirect_to();
		}
	}

	function admin_mode() {
		must_be_admin();
		$this->use_template('admin');
	}

	function set_menu($selection) {
		$this->current_menu_selection = $selection;
	}

}

?>

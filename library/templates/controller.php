<?php

/**
* <%CAPITAL_CONTROLLER_NAME%> Controller
*/

class <%CONTROLLER_NAME%>Controller extends applicationController
{

	function __construct()
	{
		// Use this to control the creation of your application controller
		parent::__construct();
		$this->before_filters = array();
	}

	function _index() {
		$this-><%CONTROLLER_UNDERSCORE%> = <%MODEL_NAME%>::find_all();
	}

	function _show() {
		$this-><%MODEL_NAME%> = <%MODEL_NAME%>::find_by_id($this->id);
	}

	function _new() {
		$this-><%MODEL_NAME%> = new <%MODEL_NAME%>($this->posted);
	}

	function _create() {
		$this-><%MODEL_NAME%> = new <%MODEL_NAME%>($this->posted);
		if ($this-><%MODEL_NAME%>->save()) {
			flash('<%CAPITAL_MODEL_NAME%> created.');
			redirect_to('<%CONTROLLER_UNDERSCORE%>');
		}
		else {
			error('There appears to be an error with the <%CAPITAL_MODEL_NAME%>.');
			render_action('new');
		}
	}

	function _edit() {
		$this-><%MODEL_NAME%> = <%MODEL_NAME%>::find_by_id($this->id);
	}

	function _update() {
		$this-><%MODEL_NAME%> = new <%MODEL_NAME%>(array_merge(array('id' => $this->id), $this->posted));
		if ($this-><%MODEL_NAME%>->update()) {
			flash('<%CAPITAL_MODEL_NAME%> updated.');
			redirect_to('<%CONTROLLER_UNDERSCORE%>');
		}
		else {
			error('There appears to be an error with the <%CAPITAL_MODEL_NAME%>.');
			render_action('edit');
		}
	}

		function _delete() {
			$this-><%MODEL_NAME%> = <%MODEL_NAME%>::find_by_id($this->id);
		if ($this-><%MODEL_NAME%>->destroy()) {
			flash('<%CAPITAL_MODEL_NAME%> '. $this->id .' deleted');
			redirect_to('back');
		}
		else {
			error('<%CAPITAL_MODEL_NAME%> could not be deleted');
			redirect_to('back');
		}
	}


}

?>

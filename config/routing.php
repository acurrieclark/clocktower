<?php

$routing = array(

	// *** examples ***
	 '/^login/' => 'users/login',
	 '/^register/' => 'users/register',
	 '/^logout/' => 'users/logout',
	// '/^news\/([^show].*?)/' => 'news/show/\1',
	// '/^events\/([0-9].*?)/' => 'events/show/\1'
);

/**
 * array to determine routing on a post request
 *
 * Example
 * 'controller_name' => array("action" => "diverted_action")
 *
 * @author Alex Currie-Clark
 **/

$post_routing = array(
	'all' => array("new" => "create", 'edit' => "update"),
	'users' => array("register" => "create-registration",
					'login' => "create_login",
					'password-reset' => 'reset-password',
					"new-password" => "save-new-password",
					"change-password" => "update-password")
);

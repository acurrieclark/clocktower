<?php

$routing = array(

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
	'all' => array("new" => "create", 'edit' => "update")
);

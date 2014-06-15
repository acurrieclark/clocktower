<?php

if (!isset($url) || $url == '') {
		$app_properties['controller'] = 'index';
		$app_properties['action'] = '';

		$app_properties['full_request']['controller'] = '';
		$app_properties['full_request']['action'] = '';
		$app_properties['full_request']['id'] = '';
		$app_properties['full_request']['parameters'] = '';

	} else {

		$request_url_array = explode("/",$url);
		$request_url['controller'] = show_safely($request_url_array[0]);
		if (isset($request_url_array[1]))
			$request_url['action'] = show_safely($request_url_array[1]);
		else $request_url['action'] = '';
		if (isset($request_url_array[2]))
			$request_url['id'] = show_safely($request_url_array[2]);
		else $request_url['id'] = '';

		$request_url['parameters'] = $_GET;

		$app_properties['full_request'] = $request_url;

		$app_properties['url'] = $url;

		$url = routeURL($url);
		$urlArray = array();
		$urlArray = explode("/",$url);
		$app_properties['controller'] = show_safely($urlArray[0]);
		array_shift($urlArray);
		if (isset($urlArray[0]) && $urlArray[0] != "") {
			$app_properties['action'] = show_safely($urlArray[0]);
			array_shift($urlArray);
			if (isset($urlArray[0]) && $urlArray[0] != "")
				$id = show_safely($urlArray[0]);
			else $id = "";

		} else {
			$app_properties['action'] = 'index'; // Default Action
		}
	}


if (is_numeric($app_properties['action'])) {
	$id = $action;
	unset($app_properties['action']);
}

if (!isset($id)) $id = '';

$app_properties['id'] = $id;
$app_properties['parameters'] = $_GET;

// check for POST
if ($_SERVER['REQUEST_METHOD'] == "POST") {
	$action_changed = false;
	if (isset($post_routing[$app_properties['controller']]) && is_array($post_routing[$app_properties['controller']])) {
		if (isset($post_routing[ $app_properties['controller'] ][ $app_properties['action'] ])) {
			$app_properties['action'] = $post_routing[ $app_properties['controller'] ][ $app_properties['action'] ];
			$action_changed = true;
		}
	}
	if (!$action_changed) {
		foreach ($post_routing['all'] as $current_action => $new_action) {
			if ($current_action == $app_properties['action']) $app_properties['action'] = $new_action;
		}
	}

}

$app_properties['controller'] = str_replace('-', '_', $app_properties['controller']);
$app_properties['action'] = str_replace('-', '_', $app_properties['action']);

?>

<?php

$app_properties['posted'] = array();

if ($_SERVER['REQUEST_METHOD'] == "POST"){
	foreach ($_POST as $key => $detail) {
		$app_properties['posted'][$key] = $detail;
	}
}

else if ($_SERVER['REQUEST_METHOD'] == "DELETE"){
	parse_str(file_get_contents('php://input'), $_DELETE);
	foreach ($_DELETE as $key => $detail) {
		$app_properties['delete'][$key] = $detail;
	}
}

foreach ($_GET as $key => $detail) {
	$app_properties['get'][$key] = $detail;
}


$controller_class_name = underscore_to_camel($app_properties['controller'])."Controller";

if (class_exists($controller_class_name))
	$app = new $controller_class_name;
else if (file_exists(ROOT."/application/static/".$app_properties['controller'].".php")) {
	$app = new baseController;
	$app->static_page = true;
}
else {
	$app = new baseController;
	$app->controller_error = true;
}

$app->setup($app_properties);

$method_name = '_'.$app->action;
if (in_array($method_name, get_class_methods($app)) ) {
	run_before_filters($app->action);
	$app->$method_name();
}
else if (!$app->controller_error && !$app->static_page)
	$app->method_error = true;
$app->render();
// -------------------- Titles --------------------- //
?>

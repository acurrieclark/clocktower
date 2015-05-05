<?php
/** Routing **/

function routeURL($url) {
	global $routing;

	foreach ( $routing as $pattern => $result ) {
            if ( preg_match( $pattern, $url ) ) {
				return preg_replace( $pattern, $result, $url );
			}
	}
	return ($url);
}

function mvacc_autoload($className) {

	if (strpos($className,'Controller') === false && strpos($className,'Form') === false) {
		if (file_exists(ROOT . DS . 'application' . DS . 'models' . DS . strtolower($className) . '.php')) {
		require_once(ROOT . DS . 'application' . DS . 'models' . DS . strtolower($className) . '.php');
		}
		else if (file_exists(ROOT . DS . 'library' . DS . strtolower($className) . '.class.php'))
			require_once(ROOT . DS . 'library' . DS . strtolower($className) . '.class.php');
	}
	else if (strpos($className,'Form')) {
		$file_name = str_replace('Form', '', $className);
		if (file_exists(ROOT . DS . 'application' . DS . 'forms' . DS . strtolower($file_name) . '.php'))
			require_once(ROOT . DS . 'application' . DS . 'forms' . DS . strtolower($file_name) . '.php');
	}
	else if (strpos($className,'Controller')){
		// if the class is a controller, we look for it in its own file
		$className = str_replace('Controller', '', $className);
		if (file_exists(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php')) {
			require_once(ROOT . DS . 'application' . DS . 'controllers' . DS . $className . '.php');
		}
		else logger::Controller("Could not find controller file '$className.php'. Please ensure case sensitivity.");
	}
}


?>

<?php


// alias for dbAbstraction::check_input()
function check_input($text) {
	return dbAbstraction::check_input($text);
}

function make_short($name) {
	$sPattern = '/[^a-zA-Z0-9\[\]_]/';
	$sReplace = '';
	$short = preg_replace( $sPattern, $sReplace, $name );
	return $short;
}

function get_element_from_short_name($form_structure, $short_name) {
	foreach ($form_structure as $element) {
		list($element_name, $element_short_name, $element_type, $element_details) = get_element_details($element);
		if ($element_short_name == $short_name)
			return $element;
	}
	return false;
}

function show_safely($str) {
	$str = stripslashes($str);
	$str = mb_convert_encoding($str, 'UTF-8', 'UTF-8');
	$str = htmlentities($str, ENT_QUOTES, 'UTF-8');
	$str = trim($str);
	return $str;
}

function add_http($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "http://" . $url;
    }
    return $url;
}

function is_valid_email ($email) {

	return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function is_valid_number ($number) {
	$sPattern = '/\s*/m';
	$sReplace = '';
	$number = preg_replace( $sPattern, $sReplace, $number );
	return is_numeric($number);
}

function is_valid_phone_number(&$strTelephoneNumber, &$intError, &$strError) {

  // Copy the parameter and strip out the spaces
  $strTelephoneNumberCopy = str_replace (' ', '', $strTelephoneNumber);

  // Convert into a string and check that we were provided with something
  if (empty($strTelephoneNumberCopy)) {
    $intError = 1;
    $strError = 'Telephone number not provided';
    return false;
  }

  // Don't allow country codes to be included (assumes a leading "+")
  if (preg_match('/^(\+)[\s]*(.*)$/',$strTelephoneNumberCopy)) {
    $intError = 2;
    $strError = 'UK telephone number without the country code, please';
    return false;
  }

  // Remove hyphens - they are not part of a telephone number
  $strTelephoneNumberCopy = str_replace ('-', '', $strTelephoneNumberCopy);

  // Now check that all the characters are digits
  if (!preg_match('/^[0-9]{10,11}$/',$strTelephoneNumberCopy)) {
    $intError = 3;
    $strError = 'UK telephone numbers should contain 10 or 11 digits';
    return false;
  }

  // Now check that the first digit is 0
  if (!preg_match('/^0[0-9]{9,10}$/',$strTelephoneNumberCopy)) {
    $intError = 4;
    $strError = 'The telephone number should start with a 0';
    return false;
  }

  // Check the string against the numbers allocated for dramas

  // Expression for numbers allocated to dramas

	$tnexp[0] =  '/^(0113|0114|0115|0116|0117|0118|0121|0131|0141|0151|0161)(4960)[0-9]{3}$/';
	$tnexp[1] =  '/^02079460[0-9]{3}$/';
	$tnexp[2] =  '/^01914980[0-9]{3}$/';
	$tnexp[3] =  '/^02890180[0-9]{3}$/';
	$tnexp[4] =  '/^02920180[0-9]{3}$/';
	$tnexp[5] =  '/^01632960[0-9]{3}$/';
	$tnexp[6] =  '/^07700900[0-9]{3}$/';
	$tnexp[7] =  '/^08081570[0-9]{3}$/';
	$tnexp[8] =  '/^09098790[0-9]{3}$/';
	$tnexp[9] =  '/^03069990[0-9]{3}$/';

  foreach ($tnexp as $regexp) {
    if (preg_match($regexp,$strTelephoneNumberCopy, $matches)) {
      $intError = 5;
      $strError = 'The telephone number is either invalid or inappropriate';
      return false;
    }
  }

  // Finally, check that the telephone number is appropriate.
  if (!preg_match('/^(01|02|03|05|070|071|072|073|074|075|07624|077|078|079)[0-9]+$/',$strTelephoneNumberCopy)) {
    $intError = 5;
    $strError = 'The telephone number is either invalid or inappropriate';
    return false;
  }

  // Seems to be valid - return the stripped telephone number
  $strTelephoneNumber = $strTelephoneNumberCopy;
  $intError = 0;
  $strError = '';
  return true;
}

function get_element_details($element) {
	if (isset($element[0]))
		$element_name = $element[0];
	else $element_name = '';
	if (!isset($element['short_name']))
		$element_short_name = make_short($element_name);
	else $element_short_name = $element['short_name'];
	if (isset($element[1]))
		$element_type = $element[1];
	else $element_type = '';
	if (isset($element[2]))
		$element_details = $element[2];
	else $element_details = array();
	return array($element_name, $element_short_name, $element_type, $element_details);
}

function model_contains_file_upload($form_structure) {
	foreach ($form_structure as $element) {
		if (isset($element[1]) && $element[1] == 'file') return true;
	}
	return false;
}

function model_contains_headings($form_structure) {
	foreach ($form_structure as $element) {
		if (isset($element[1]) && $element[1] == 'heading') return true;
	}
	return false;
}

function find_associated($form_structure) {
	foreach ($form_structure as $element) {
		list($element_name, $element_short_name, $element_type, $element_details) = get_element_details($element);
		if (($element_type == "mixed") || $element['many']) {
			$m_and_m[] = $element_short_name;
		}
	}
	return $m_and_m;
}

############# Application Functions ###########

// Runs functions needed before the main controller function
function run_before_filters($function) {
	global $app;
	if (!$app->before_filters) return;
	foreach ($app->before_filters as $function_name => $actions) {
		foreach ($actions as $key => $action) {
			if ($key && $key == 'except') {
				foreach ($action as $except_action) {
					if ($except_action == $function) {
			 			break 2;
					}
				}
				$app->$function_name();
			}
			else {
		 		if ($action == $function || $action == 'all') {
		 			$app->$function_name();
					break;
		 		}
		 	}
	 	}
	}
}

function current_address() {
	global $app;
	return address($app->controller, $app->action, $app->id, $app->get);
}

function address($controller, $action = "", $id = "", $params=array()) {

	global $app;

	if ($controller == 'back') {
		$controller = $app->back['controller'];
		$action = $app->back['action'];
		$id = $app->back['id'];
		$params = $app->back['parameters'];
	}
	else if ($controller == 'doubleback') {
		$controller = $app->doubleback['controller'];
		$action = $app->doubleback['action'];
		$id = $app->doubleback['id'];
		$params = $app->doubleback['parameters'];
	}

	if ($action != "")
		$action = DS.$action;
	if ($id != "")
		$id = DS.$id;

	$first = true;

	if (empty($params)) {
		$params = array();
		$params_string = '';
	}

	foreach ($params as $name => $value) {
		if ($first)
			$params_string = "?$name=$value";
		else
			$params_string .= "&$name=$value";
		$first = false;
	}

	return ABSOLUTE.$controller.$action.$id.$params_string;
}

function must_be_ajax() {
	global $app;
	if ($app->request_type != 'ajax') {
		redirect_to('unkown');
	}
}

function must_be_admin() {
	if (!is_admin()){
		error("You need to be an administrator to view that page");
		redirect_to();
	}
}

function redirect_to($controller = "", $action = "", $id = "", $parameters = array()) {

	global $flash, $app;

	$app->finalise_back();
	$app->reset_security();

	if ($app->request_type == 'ajax') {
		if (!header_already_set())
			header('Content-type: application/json');
		echo $flash->json_string();
		logger::Sending("Ajax Response");
		logger::Sending("Headers: ". json_encode(headers_list()));
		logger::Sending(stripslashes(json_encode($flash->json_string())));
		logger::write();
		ob_end_flush();
		exit;
	}
	else {
		$flash->save();
		logger::Redirecting("to ".address($controller , $action, $id, $parameters));
		logger::write();
		header('Location: '.address($controller, $action, $id, $parameters));
		exit();
	}
}


function header_already_set() {
	$headers = headers_list();
	foreach ($headers as $header) {
		if (substr($header, 0, 13) == 'Content-type:') {
			return true;
		}
	}
	return false;
}

function url_friendly($text) {
	$sPattern = '/[\s]/';
	$sReplace = '-';
	$text = preg_replace( $sPattern, $sReplace, $text );
	$sPattern = '/[^a-zA-Z0-9\-]/';
	$sReplace = '';
	return preg_replace( $sPattern, $sReplace, $text );
}

function populate($from, &$to, $replace = true)
{

	foreach ($to as $element) {
		$short_name = $element->short_name;
		if ((!$element->value || $replace) && $short_name && $from->$short_name) {
			$to->$short_name->value = $from->$short_name->value;
		}
	}

}

function string_to_camel($text, array $noStrip = array())
{
        // non-alpha and non-numeric characters become spaces
        $str = preg_replace('/[^a-z0-9' . implode("", $noStrip) . ']+/i', ' ', $text);
        $str = trim($str);
        // uppercase the first character of each word
        $str = ucwords($str);
        $str = str_replace(" ", "", $str);
        $str = lcfirst($str);

        return $str;
}

function string_to_underscore($text) {
	return strtolower((str_replace(" ", "_", $text)));
}

function camel_to_underscore($text) {
	return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', trim($text)));
}

function underscore_to_camel($text) {
	$array = explode('_', $text);
	$count = 0;
	foreach ($array as $key => $value) {
		$value = strtolower($value);
		if ($key > 0)
			$array[$key] = ucfirst($value);
	}
	$text = implode($array, '');
	return $text;
}

function camel_to_string($text) {
	return ucfirst(preg_replace('/([a-z])([A-Z])/', '$1 $2', trim($text)));
}

function underscore_to_string($text) {
	return ucwords((str_replace("_", " ", $text)));
}

############### Action Functions ##############

// Set the action to render
function render_action($action) {
  global $app;
  $app->set_action_to_render($action);
  return;
}

/**
 * functions for templating
 */

function add_stylesheet($location) {
	global $app;
	$app->template->add_stylesheet($location);
}

function add_javascript($location) {
	global $app;
	$app->template->add_javascript($location);
}

function javascript_start() {
	global $app;
	$app->template->javascript_start();
}

function javascript_end() {
	global $app;
	$app->template->javascript_end();
}

function include_javascript($filename, $variables=array()) {
	global $app;
	$app->template->include_javascript($filename, $variables);
}

// starts template content logging

function content_start($name) {
	global $app;
	$app->template->content_start($name);
}

// ends content logging

function content_end() {
	global $app;
	$app->template->content_end();
}

// displays content for region 'name'

function content_for($name) {
	global $app;
	$app->template->content_for($name);
}

// sets variables for use in {@=name} to their required value

function set($name, $value) {
	global $app;
	$app->template->set($name, $value);
}

function render_partial($name, $variables = array()) {
	global $app;
	$app->render_partial($name, $variables);
}

################ Model Functions ###############

// Load a different model structure
function load_model($model) {
	if (file_exists (ROOT . DS . 'application' . DS . 'models' . DS . $model . '.php' ))
		include (ROOT . DS . 'application' . DS . 'models' . DS . $model . '.php');
	return $form_structure;
}

function logged_in() {
	if (current_user()) {
		return true;
	}
	else {
		return false;
	}
}

function must_be_logged_in() {
	if (logged_in()) return true;
	else {
		global $app;
		error('Please login to view that page');
		store('back_after_login', $app->full_request);
		redirect_to('login');
	}
}

function set_current_user($user) {
	global $app;
	$app->current_user = $user;
}

function current_user() {
	global $app;
	if (isset($app->current_user))
		return $app->current_user;
	else return false;
}

function is_admin() {
	global $app;
	if (current_user() && current_user()->level == "Administrator")
		return true;
	else return false;
}

function access_ok($user_id, $redirect = '') {
	if (logged_in() && $user_id != "") {
		if (is_admin()) return true;
		global $app;
		if ($user_id == $app->current_user->id->value)
			return true;
		else {
			error('You do not have permission to view that page');
			redirect_to($redirect);
		}
	}
	else redirect_to($redirect);
}

function merge_errors($from, &$to) {
	foreach ($from as $element) {
		if ($element->error) {
			$error_short_name = $element->short_name;
			$to->$error_short_name->error = $element->error;
		}
	}
}

function getDirectoryList ($directory, $recursive = false)
  {

    // create an array to hold directory list
    $results = array();

    // create a handler for the directory
    $handler = opendir($directory);

    // open directory and walk through the filenames
    while ($file = readdir($handler)) {

      // if file isn't this directory or its parent, add it to the results
      if ($file != "." && $file != "..") {
        if (is_dir($directory.DS.$file) && $recursive) {
        	$results[$file] = getDirectoryList($directory.DS.$file, true);
        }
		else
        	$results[$file] = $file;
      }

    }

    // tidy up: close the handler
    closedir($handler);

    // done!
    return $results;

  }

function delete_directory($dir) {
   if (is_dir($dir)) {
     $objects = scandir($dir);
     foreach ($objects as $object) {
       if ($object != "." && $object != "..") {
         if (filetype($dir."/".$object) == "dir") delete_directory($dir."/".$object); else unlink($dir."/".$object);
       }
     }
     reset($objects);
     rmdir($dir);
   }
 }

function recurse_copy($src,$dst, $noisy = false) {
	if ($noisy) echo "Copying $src\n";
	if (is_dir($src)) {
		$dir = opendir($src);
    	@mkdir($dst);
    	while(false !== ( $file = readdir($dir)) ) {
	        if (( $file != '.' ) && ( $file != '..' )) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
	        }
    }
    closedir($dir);
    return true;
	}

	else if (@copy ($src, $dst)) {
		return true;
	}
	else return false;


}

############## Flash and Error Messages ##############

function flash($message) {
	global $flash;
	$flash->message($message);
}

function warning($message) {
	global $flash;
	$flash->warning($message);
}

function information($message) {
	global $flash;
	$flash->information($message);
}

function error($message) {
	global $flash;
	$flash->error($message);
}

function system_error($message) {
	global $flash;
	$flash->system_error($message);
}

function system_message($message) {
	global $flash;
	$flash->system_message($message);
}

################## Session Functions ###################

// Stores $value in the session as $name
function store($name, $value) {
	$_SESSION[$name]=$value;
}

// Retrieves variable $name from the session
function get_stored($name) {
	if (isset($_SESSION[$name]))
		return $_SESSION[$name];
	else return false;
}

// Retrieves variable $name from session, then removes it
function retrieve_and_remove($name) {
	$return = get_stored($name);
	remove_stored($name);
	return $return;
}

// Removes variable $name from session
function remove_stored($name) {
	unset($_SESSION[$name]);
}

function wrapHTML($data) {
	$ret = '<!doctype html>';
	$ret .= '<html><head>';
        $ret .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
        // No protection if $html contains a stray </div>!
        $ret .= '</head><body>'.$data.'</body></html>';
        return $ret;
}

function limit_title_length($title){
if(strlen($title) > 65)
$title = substr($title, 0, 65);


return $title;
}

// image functions

function saveImage($image, $file, $type) {

    switch ($type) {
        case 1 :
			imagegif($image, $file, 80);
        break;
        case 2 :
    		imagejpeg($image, $file);
        break;
        case 3 :
    		imagepng($image, $file, 9);
        break;
     }

}

function imageCreateFromFile($filepath) {
    $type = exif_imagetype($filepath); // [] if you don't have exif you could use getImageSize()
    $allowedTypes = array(
        1,  // [] gif
        2,  // [] jpg
        3  // [] png
    );

    if (!in_array($type, $allowedTypes)) {
        return false;
	    }
	    switch ($type) {
	        case 1 :
	            $im = imageCreateFromGif($filepath);
				imagealphablending($im, true);
	        break;
	        case 2 :
	            $im = imageCreateFromJpeg($filepath);
	        break;
	        case 3 :
	            $im = imageCreateFromPng($filepath);
				imagealphablending($im, true);
	        break;
	     }
    return $im;
}

// uses curl to retrieve a site's html content

function get_html_data($url) {

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	    $data = curl_exec($ch);
	    curl_close($ch);

	    return $data;

}

?>

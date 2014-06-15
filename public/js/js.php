<?php

define('ROOT', dirname(dirname(__FILE__)));
define('DS', "/");

// Enable GZip encoding.
ob_start("ob_gzhandler");

header("Content-type: application/javascript");

if(isset($_GET['set'])) {
    $files = unserialize(base64_decode($_GET['set']));

    if (!empty($files) && is_array($files)) {
        foreach($files as $file) {
        	$file = str_replace('..', '', $file);
        	if (file_exists(ROOT.'/js/'.$file.'.js')) {
	            $contents = file_get_contents(ROOT.'/js/'.$file.'.js');
		        $data[] = "\n// ============== ".$file.".js ============== \n".$contents;
	        }
        }
    }
    if (is_array($data)){
	    $buffer = implode("\n",$data);

	    if (!DEVELOPMENT_ENVIRONMENT) {

			// Enable caching
			header('Cache-Control: public');
			// Expire in one day
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

		}

		echo $buffer;
		exit();
	}
}

header("HTTP/1.0 404 Not Found");
echo "// No js to display";



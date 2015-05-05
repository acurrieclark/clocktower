<?php

define('ROOT', dirname(dirname(__FILE__)));
define('DS', "/");

include(ROOT."/../config/server-config.php");

// Enable GZip encoding.
ob_start("ob_gzhandler");

header("Content-type: text/css");

if(isset($_GET['set'])) {
    $files = unserialize(base64_decode($_GET['set']));

    if (!empty($files) && is_array($files)) {
        foreach($files as $file) {
        	$file = str_replace('..', '', $file);
        	if (file_exists(ROOT.'/css/'.$file.'.css')) {
	            $contents = file_get_contents(ROOT.'/css/'.$file.'.css');
		        $data[] = "\n/* ============== ".$file.".css ============== */\n".$contents;
	        }
        }
    }
    if (is_array($data)){
	    $buffer = implode("\n",$data);

	    if (!DEVELOPMENT_ENVIRONMENT) {
	    	// Remove comments
			$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
			// Remove space after colons
			$buffer = str_replace(': ', ':', $buffer);
			// Remove whitespace
			$buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

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
echo "/* No css to display */";



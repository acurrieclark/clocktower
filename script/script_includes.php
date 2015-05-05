<?php

include (ROOT."/library/core_functions.php");
include (ROOT."/library/helpers.php");
require (ROOT."/config/database.php");
require (ROOT."/config/server-config.php");
require (ROOT."/config/config.php");
require (ROOT."/config/inflection.php");
require (ROOT."/library/inflection.class.php");
require (ROOT."/library/setup_functions.php");
require (ROOT."/script/script_functions.php");
require (ROOT . DS . 'library' . DS . 'element.class.php');
require_once (ROOT . DS . 'library' .DS . 'vendor' . DS . 'autoload.php');

// adds main app autoloader so as not to interfere with other loaders
spl_autoload_register('mvacc_autoload');

 ?>

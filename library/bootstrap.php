<?php

// Load configuration File
require_once (ROOT . DS . 'config' . DS . 'server-config.php');
require_once (ROOT . DS . 'config' . DS . 'config.php');


require_once (ROOT . DS . 'config' . DS . 'routing.php');
require_once (ROOT . DS . 'config' . DS . 'database.php');
require_once (ROOT . DS . 'config' . DS . 'inflection.php');
require_once (ROOT . DS . 'config' . DS . 'validations.php');

require_once (ROOT . DS . 'library' . DS . 'core_functions.php');
require_once (ROOT . DS . 'library' . DS . 'setup_functions.php');
require_once (ROOT . DS . 'library' . DS . 'helpers.php');
require_once (ROOT . DS . 'library' . DS . 'element.class.php');

require_once (ROOT . DS . 'library' . DS . 'mailer' . DS . 'swift_required.php');

// Load all the helpers
// For now, *all* helpers are loaded as standard

$helpers = getDirectoryList(ROOT.'/application/helpers');

foreach ($helpers as $helper) {
	if (strpos($helper,'.php')) {
		 require_once(ROOT.'/application/helpers/'.$helper);
	}
}


// Setup the application
require_once (ROOT . DS . 'library' . DS . 'setup.php');

// Run the application
require_once (ROOT . DS . 'library' . DS . 'controller.class.php');
require_once (ROOT . DS . 'library' . DS . 'systemaction.class.php');
require_once (ROOT . DS . 'library' . DS . 'router.php');
require_once (ROOT . DS . 'library' . DS . 'application.php');

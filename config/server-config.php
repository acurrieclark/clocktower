<?php

/** Configuration Variables **/

define ('DEVELOPMENT_ENVIRONMENT', true);

define(SEND_ALL_MAIL_TO_DEVELOPER, TRUE);

if (isset($_SERVER['HTTP_HOST'])) {
	define ('ABSOLUTE', str_replace("public/index.php", "", "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME']));

	// ABSOLUTE_PUBLIC will only be different from ABSOLUTE if the app is not accessed at the web address of the files
	define ('ABSOLUTE_PUBLIC', ABSOLUTE);
}

if (DEVELOPMENT_ENVIRONMENT == true) {
	// set error reporting level
	error_reporting(E_ALL & ~E_NOTICE);
}

// set the timezone
date_default_timezone_set('Europe/London');

// server settings
define('WEBSITE_ADDRESS', '');

//email server settings
define('SMTP_PORT', "");
define('SMTP_HOST', "");
define('SMTP_USERNAME', "");
define('SMTP_PASSWORD', '');

define('SENDMAIL_PATH', '/usr/sbin/sendmail -bs');

// email send rate
define('EMAILS_PER_HOUR', 250);

//options -- mail, sendmail, smtp
define('EMAIL_METHOD', 'sendmail');

//session variables
define('SESSION_LIFETIME', 1200);
define('SECURITY_TOKEN_LIFETIME', 900); // seconds
define('REMEMBER_ME_LIFETIME', 30); // number of * days * a remember me token lasts for

// google analytics
define('INCLUDE_GOOGLE_ANALYTICS', TRUE);
define('GOOGLE_ID_CODE', '');

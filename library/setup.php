<?php

if (isset($_GET['mvaccurl']))
	$url = $_GET['mvaccurl'];
unset($_GET['mvaccurl']);

// adds main app autoloader so as not to interfere with other loaders
spl_autoload_register('mvacc_autoload');

?>

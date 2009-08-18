<?php

// Figure out which environment we are running on
// TODO logic to figure out environment
define('ARC_ENV', 'dev');

// Figure out the file system path to the root of the project (where this file is)
define ('FSROOT', realpath(dirname(__FILE__)));

// Set error reporting, based on environment
switch(ARC_ENV){
	case 'dev':  error_reporting(E_ALL); break;
	case 'stage':  error_reporting(0); break;
	case 'live':  error_reporting(0); break;
}

// Include the site config
require_once('conf/config.php');

// 'Boot' arc
require_once('arc/boot.php');

// Set the default layout
$registry->defaultLayout = 'html';

// Start the router
$registry->router->start();
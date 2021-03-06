<?php

// Extract the path from the query string parameters
$path = explode('/', @$_GET['_path']);

//Extract the extension from the path, if there is one.
$ext = substr(strrchr($path[count($path)-1], '.'), 1);

// Set up an autoloader - this runs when it encounters a classname it doesn't recognise
function __autoload($class_name){
	global $registry;
	
	// define possible locations of the file
	$locations = array();
	$locations[] = $registry->modelPath.'/'.$class_name.'.class.php'; //models
	$locations[] = $registry->libPath.'/'.str_replace('_', '/', $class_name).'.php'; //library files, define subfolders with _ in class name

	foreach($locations as $location){
		if(file_exists($location)){
			require($location);
		}
	}
	
	return false;
}

// Define the file system root location of arc. We assume it's in a folder called arc.
define('ARC_ROOT', FSROOT.'/arc');

// Include all the source files
require_once(ARC_ROOT.'/src/BaseController.class.php');
require_once(ARC_ROOT.'/src/Registry.class.php');
require_once(ARC_ROOT.'/src/Router.class.php');
require_once(ARC_ROOT.'/src/View.class.php');
require_once(ARC_ROOT.'/src/Partial.class.php');

// Include all the library files
require_once(ARC_ROOT.'/src/lib/mysql.Database.class.php');
include_once(ARC_ROOT.'/src/lib/Debug.class.php');
include_once(ARC_ROOT.'/src/lib/HTML.class.php');
include_once(ARC_ROOT.'/src/lib/Vars.class.php');

// --- Bootstrap: Set up what needs to be set up to run the MVC ---
// Create a new instance of the registry
$registry = new Registry();

// Add some environment variables to the registry
$registry->fsRoot = FSROOT;
$registry->arcRoot = ARC_ROOT;
$registry->path = $path;
$registry->ext = $ext;

$registry->modelPath = FSROOT.'/php/models';
$registry->viewPath = FSROOT.'/php/views';
$registry->controllerPath = FSROOT.'/php/controllers';
$registry->layoutPath = FSROOT.'/php/layouts';
$registry->helperPath = FSROOT.'/php/helpers';
$registry->partialPath = FSROOT.'/php/views/partials';
$registry->libPath = FSROOT.'/php/lib';

// Create a MySQL database object and add it to the registry
$registry->db = new Database(DBSRVR,DBNAME,DBUSER,DBPASS);

// Create a new router and add it to the registry
$registry->router = new Router($registry);

//Add a new Vars object to the registry
$registry->var = Vars::getInstance();

// Figure out which request method to use. If specified in the url it overrides the request method in the header.
if(in_array(strtolower($registry->var->get('_method')), array('get','put','post','delete'))){
	$registry->request_method = strtolower($registry->var->get('_method'));
}
else{
	$registry->request_method = strtolower($_SERVER['REQUEST_METHOD']);
}

//Add the layout to the registry
$registry->format = $registry->var->get('_format')?$registry->var->get('_format'):'';

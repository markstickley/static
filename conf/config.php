<?php
if(!defined('ARC_ENV')){
	define('ARC_ENV','live'); //safer to assume live and not get dev stuff showing up on live
}

//Database
if(ARC_ENV=='dev'){
	define('DBSRVR','localhost');
	define('DBNAME','qk_assets');
	define('DBUSER','qk_assets');
	define('DBPASS','qu1nt1nkyn4st0n');
}
else if(ARC_ENV=='stage'){
	define('DBSRVR','localhost');
	define('DBNAME','');
	define('DBUSER','');
	define('DBPASS','');	
}
else{ //live
	define('DBSRVR','localhost');
	define('DBNAME','');
	define('DBUSER','');
	define('DBPASS','');
}

//Webroot
if(ARC_ENV=='dev'){
	define('WEBROOT','http://www.qk.dev/assets');
}
else if(ARC_ENV=='stage'){
	define('WEBROOT','');
}
else{ //live
	define('WEBROOT','http://www.qkschool.org.uk/assets');
}
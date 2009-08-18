<?php

/**
* HTML Class, for doing HTML things
*/
class HTML{
	
	function __construct(){
		//no constructor
	}
	
	static function error404($message){
		header("HTTP/1.0 404 Not Found");
		die("<h1>404 Page not found</h1> <p>$message</p>");
	}
}

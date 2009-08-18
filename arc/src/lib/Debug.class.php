<?php
/**
* Debug - static functions that help you debug.
* If this file didn't exist on the live server it shouldn't matter because none of these functions should be used in production.
*/
class Debug{
	
	function __construct(){
		//no constructor
	}
	
	static function var_dump_html($var){
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}
}

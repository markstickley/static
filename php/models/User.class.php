<?php

/**
* User
*/
class User{
	
	// Construct new User with the given id
	function __construct($id){
		
	}
	
	// Returns currently logged in user
	static function getCurrentUser(){
		//try and get the user id from the cookie
		$userCookie = $_COOKIE['static'];
		$className = __CLASS__;
		$thisUser = new $className();
	}
	
}

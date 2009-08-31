<?php
/**
* 
*/
class Util{
	
	static function formatDate($date, $format=null){
		if(is_null($format)){
			return $date;
		}
		else if(is_string($format)){
			return date($format,strtotime($date));
		}
		else{
			return false;
		}
	}
	
}

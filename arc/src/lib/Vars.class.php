<?php
/**
* Vars. Handles the vars from $_POST and $_GET
*/
class Vars{
	
	private static $instance;
	
	private $post;
	private $get;
	
	function __construct(){
		$this->post = $_POST;
		$this->get = $_GET;
	}
	
	public static function getInstance(){
		if(is_null(self::$instance)){
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
	}
	
	public function get($var, $defaultval='', $source='postfirst'){
		if(empty($var) || ($source != 'post' && $source != 'get' && $source != 'postfirst' && $source != 'getfirst')){
			return false;
		}

		$mq = (get_magic_quotes_gpc() || get_magic_quotes_runtime());
		$retval = '';

		if($source=='post'){
			$retval = (array_key_exists($var, $this->post) && isset($this->post[$var]) && $this->post[$var]!=null)?$this->post[$var]:$defaultval;
		}
		else if($source=='get'){
			$retval = (array_key_exists($var, $this->get) && isset($this->get[$var]) && $this->get[$var]!=null)?$this->get[$var]:$defaultval;
		}
		else if($source=='postfirst'){
			$myvar = (array_key_exists($var, $this->post) && isset($this->post[$var]) && $this->post[$var]!=null)?$this->post[$var]:$defaultval;
			if($myvar===$defaultval){
				$retval = (array_key_exists($var, $this->get) && isset($this->get[$var]) && $this->get[$var]!=null)?$this->get[$var]:$defaultval;
			}
			else{
				$retval = $myvar;
			}
		}
		else if($source=='getfirst'){
			$myvar = (array_key_exists($var, $this->get) && isset($this->get[$var]) && $this->get[$var]!=null)?$this->get[$var]:$defaultval;
			if($myvar===$defaultval){
				$retval = (array_key_exists($var, $this->post) && isset($this->post[$var]) && $this->post[$var]!=null)?$this->post[$var]:$defaultval;
			}
			else{
				$retval = $myvar;
			}
		}

		return ($mq && (gettype($retval)=='string'))?stripslashes($retval):$retval;
	}
	
	public function getForDB($var, $defaultval='', $source='postfirst'){
		return mysql_real_escape_string($this->get($var, $defaultval, $source));
	}
	
}


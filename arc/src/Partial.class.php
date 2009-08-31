<?php
/**
* Partial. It's basically a simplified View.
*/
class Partial{
	
	private $registry;
	private $vars;
	private $include_path;
	public $db;
	
	function __construct($registry,$include_file,$vars=null){
		$vars = is_null($vars)?array():$vars;
		
		if(get_class($registry)!='Registry'){
			throw new Exception('Invalid registry passed to Partial');
		}
		if(!is_string($include_file)){
			throw new Exception('Invalid partial filename - must be a string');
		}
		if(!is_array($vars)){
			throw new Exception('Invalid variables passed to Partial - must be an array');
		}
		
		$this->registry = $registry;
		$this->vars = $vars;
		$this->db = Database::getInstance();
		$this->include_path = $this->registry->partialPath.'/'.$include_file.'.php';
	}
	
	function __get($index){
		if(isset($this->$index)){
			return $this->$index;
		}
		else if(isset($this->vars[$index])){
			return $this->vars[$index];
		}
		else{
			return null;
		}
	}
	
	public function render(){
		if(!file_exists($this->include_path)){
			throw new Exception('Partial not found: '.$this->include_path);
		}
		
		require($this->include_path);
	}
}

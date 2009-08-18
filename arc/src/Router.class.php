<?php

class Router {
	/*
	* @the registry
	*/
	private $registry;

	private $args = array();
	
	public $controllerFile;
	public $controller;
	public $action;

	function __construct($registry) {
		$this->registry = $registry;
		
		// Test to see if the controller path has been set correctly
		if (!is_dir($registry->controllerPath)){
			throw new Exception ("Invalid controller path in registry: '$registry->controllerPath'");
		}
	}
	
	public function start(){
		// Set up and run the controller class
		// First get the absolute path to the controller file
		$this->controllerFile = $this->getControllerFile();
		
		// Better check that file exists
		if(!is_readable($this->controllerFile)){
			HTML::error404('Missing controller: <em>'.$this->controller.'</em>');
		}
		
		// Next include that file
		include($this->controllerFile);
		
		// Now create a new instance of that controller
		$controllerClass = ucfirst($this->controller).'Controller';
		$controller = new $controllerClass($this->registry);
		
		//Finally, check and call the action on the controller
		$actionFunction = $this->action.'Action';
		if(!is_callable(array($controller,$actionFunction))){
			if(is_callable(array($controller,'indexAction'))){
				$actionFunction = 'indexAction';
			}
			else{
				HTML::error404('Action not found: <em>'.$this->action.'</em>');
			}
		}
		$controller->$actionFunction(); //run the action
	}
	
	
	private function getControllerFile(){		
		// Set the controller and action names from the path
		// They will be 'index' if they aren't set
		$this->controller = (isset($this->registry->path[0]) && !empty($this->registry->path[0]))?$this->registry->path[0]:'index';
		$this->action = (isset($this->registry->path[1]) && !empty($this->registry->path[1]))?$this->registry->path[1]:'index';
		
		return $this->registry->controllerPath.'/'.ucfirst($this->controller).'Controller.php';
	}
}
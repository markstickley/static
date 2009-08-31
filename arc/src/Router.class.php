<?php

class Router {
	/*
	* @the registry
	*/
	private $registry;

	private $args = array();
	
	public $controllerFile;
	public $controllerName;
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
		$this->loadController();
		$this->runControllerAction();
	}
	
	
	public function loadController($controllerName=null){
		// Set up and run the controller class
		// First get the absolute path to the controller file
		if(is_null($controllerName)){
			$this->controllerName = $this->getControllerFromPath($this->registry->path);
		}
		else{
			$this->controllerName = $controllerName;
		}
		$this->controllerFile = $this->getControllerFile($this->controllerName);
		
		// Better check that file exists
		if(!is_readable($this->controllerFile)){
			HTML::error404('Missing controller: <em>'.$this->controllerName.'</em>');
		}
		
		// Next include that file
		require_once($this->controllerFile);
		
		// Now create a new instance of that controller
		$controllerClass = ucfirst($this->controllerName).'Controller';
		$this->controller = new $controllerClass($this->registry);
	}
	
	public function runControllerAction($action=null){
		if(is_null($action)){
			$this->action = $this->getControllerActionFromPath($this->registry->path);
		}
		else{
			$this->action = $action;
		}
		
		if(is_null($this->controller)){
			throw new Exception('Cannot run action \''.$this->action.'\' on a null controller');
		}
		
		//check and call the action on the controller
		$actionFunction = $this->action.'Action';
		if(!is_callable(array($this->controller,$actionFunction))){
			if(is_callable(array($this->controller,'indexAction'))){
				$actionFunction = 'indexAction';
			}
			else{
				HTML::error404('Action not found: <em>'.$this->action.'</em>');
			}
		}
		$this->controller->$actionFunction(); //run the action
	}
	
	//returns the name of the current controller
	public function getControllerName(){
		return $this->controllerName;
	}
	
	private function getControllerFromPath($path){
		return (isset($path[0]) && !empty($path[0]))?$path[0]:'index';
	}
	
	private function getControllerActionFromPath($path){
		return (isset($path[1]) && !empty($path[1]))?$path[1]:'index';
	}
	
	private function getControllerFile($controller=null){
		if(is_null($controller)){
			$controller = $this->getControllerFromPath(); //use the path of the current page by default
		}
				
		return $this->registry->controllerPath.'/'.ucfirst($controller).'Controller.php';
	}
}
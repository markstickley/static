<?php
Abstract Class BaseController{

	protected $registry;
	protected $view;
	
	function __construct($registry) {
		$this->registry = $registry;
		$this->view = new View($registry);
	}

	//redirects to a new URL
	function redirect($url){
		header('Location: '.$url);
	}
	
	//reroutes to a new controller / action
	function reroute($action, $controller=null){
		if(is_null($controller)){
			$controller = $this->registry->router->getControllerName();
		}
		
		$this->registry->router->loadController($controller);
		$this->registry->router->runControllerAction($action);
	}

	// This forces all controllers to have an IndexAction
	abstract function IndexAction();
}
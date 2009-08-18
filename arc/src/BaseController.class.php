<?php
Abstract Class BaseController{

	protected $registry;
	protected $view;
	
	function __construct($registry) {
		$this->registry = $registry;
		$this->view = new View($registry);
	}

	// This forces all controllers to have an IndexAction
	abstract function IndexAction();
}
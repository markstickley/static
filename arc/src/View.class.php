<?php
/**
* View. The view class.
*/
class View{
	
	private $registry;
	private $vars = array();
	private $layoutFile;
	private $layout;
	private $viewFile; // absolute path to the view file
	private $view; // name of the view
	private $viewContents; // The rendered contents of the view
	
	function __construct($registry){
		$this->registry = $registry;
		$this->setLayout();
	}
	
	//magic get function. If the property doesn't exist it tries the $vars array.
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
	
	//sets the layout. If no layout is provided then it looks for the default layout setting.
	public function setLayout($layout=null){
		$this->layout = $layout?$layout:$this->registry->defaultLayout;
	}
	
	// Assign variables to the view.
	public function assign($index, $value){
		$this->vars[$index] = $value;
	}
	
	//renders the view
	public function render($view=null, $layout=null){
		if($layout!=null){
			$this->setLayout($layout);
		}
		
		// The content comes from the view and gets inserted into the layout (if it asks for it)
		// First, get the path of the view file
		$this->viewFile = $this->getViewPath($view);

		if(file_exists($this->viewFile)){
			// start output buffer
			ob_start();
			//include the view contents
			include($this->viewFile);
			$this->viewContents = ob_get_contents();
			ob_end_clean();
		}
		else{
			HTML::error404("View not found: <em>$this->view</em>");
		}
		
		//Next get the layout and include it.
		$this->layoutFile = $this->registry->layoutPath.'/'.$this->layout.'.php';

		include($this->layoutFile);
	}
	
	// Calculates the path to the view. If a view is provided then it uses that one
	private function getViewPath($view=null){
		//if there's a format, use it.
		$format = $this->registry->format?'.'.$this->registry->format:'';
		$view = is_null($view)?$this->registry->router->action:$view;
		
		$this->view = $this->registry->router->controller.'/'.$view.$format;
		return $this->registry->viewPath.'/'.$this->view.'.php';
	}
	
	private function content(){
		return $this->viewContents;
	}
}

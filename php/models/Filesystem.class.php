<?php
/**
* Filesystem. Simulates a filesystem with files and folders and stuff.
*/
class Filesystem{
	
	private $db;
	
	function __construct(){
		$this->db = Database::getInstance();
	}
	
/*
* getFile - gets a file
* @arg	$path	Mixed	The path to the file (string) OR the numerical file id (int)
* @returns	File	Instance of File
*/
	public function getFile($path){
		if(is_string($path)){
			$id = File::getIdFromPath($path);
			if(!$id){
				return false;
			}
		}
		else if(is_numeric($path)){
			$id = $path;
		}
		else{
			return false;
		}
		
		try{
			$file = new File($id);
			return $file;
		}
		catch(Exception $e){
			return false;
		}
	}
	
/*
* getFolder - gets a folder
* @arg	$path	Mixed	The path to the folder (string) OR the numerical folder id (int)
* @returns	Folder	Instance of Folder
*/
	public function getFolder($path){
		if(is_string($path)){
			$id = Folder::getIdFromPath($path);
			if(!$id){
				return false;
			}
		}
		else if(is_numeric($path)){
			$id = $path;
		}
		else{
			return false;
		}
		
		try{
			$folder = new Folder($id);
			return $folder;
		}
		catch(Exception $e){
			return false;
		}
	}
	
/*
* isFile - evaluates a path for validity
* @arg	$path	String	The string representing the path
* @returns	Boolean	True if the path is valid, false otherwise
*/
	public function isFile($path){
		if(!is_string($path) || is_numeric($path)){
			return false;
		}
		
		return $this->getFile($path)?true:false;
	}

/*
* isFolder - evaluates a path for validity
* @arg	$path	String	The string representing the path
* @returns	Boolean	True if the path is valid, false otherwise
*/	
	public function isFolder($path){
		if(!is_string($path) || is_numeric($path)){
			return false;
		}
		
		return $this->getFolder($path)?true:false;
	}
	
}

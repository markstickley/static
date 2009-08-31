<?php
/**
* Folder - represents a folder
*/
class Folder{
	
	private $db;
	private $properties = array();
	private $path;
	private $id;
	
	// id can be an int or a string (the path). For an int, the resource has to already exist.
	function __construct($id){
		$this->db = Database::getInstance();
		
		if(is_numeric($id)){
			$this->id = $id;
		}
		else if(is_string($id)){
			if($thisid = self::getIdFromPath($id)){
				$this->id = $thisid;
			}
		}
		else{
			throw new Exception('Constructor argument for Folder must be an Int or a String: '.$id);
		}
		
		if(isset($this->id)){
			$this->read();
		}
		else{
			$this->path = $id;
		}
	}
	
	// CRUD operations
	
	public function create($visibility='private'){ //or POST
		// This should be called when the object is created with a path that doesn't exist yet.
		// The function will work back through the path until it finds a folder that exists,
		//  then creates the subsequent subfolders to create the full path
		
		if(!isset($this->path)){
			return false;
		}
		
		// sanitise $visibility
		$visibility = in_array($visibility, array('private','public'))?$visibility:'private';
		
		if($this->getIdFromPath($this->path)){ //if the path somehow exists then just read in the info and the jobs a good'un
			$this->read();
			return true;
		}
		else{
			$pathParts = array();
			$currentPath = $this->path;
			do{
				//chop off the last section of the path and pop it in $pathParts for later
				$splitPath = explode('/', $currentPath);
				array_push($pathParts, array_pop($splitPath));
				$currentPath = implode('/',$splitPath);
			} while(!($currentPathId = $this->getIdFromPath($currentPath)));
			
			//now we have the closest existing parent folder id in $currentPathId and the sub-folders to be made in $pathParts
			//loop through the folders in $pathParts creating them one at a time
			while(!is_null($pathPart = array_pop($pathParts))){
				$sql = "INSERT INTO folders SET name='".$pathPart."', parent=".$currentPathId.", created=NOW(), modified=NOW(), visibility='".$visibility."'";
				if(!$this->db->query($sql)){
					throw new Exception("Error inserting new folder '".$pathPart."' with parent id ".$currentPathId);
				}
				$currentPathId = $this->db->insert_id();
			}
			
			$this->id = $currentPathId;
			$this->read();
			return true;
		}		
	}
	
	public function read(){ //or GET
		//reads the folder object and stores the data in $this->properties. Requires $this->id to be set.
		if(!isset($this->id)){
			throw new Exception('Id not set in Folder object');
		}
		
		$sql = "SELECT * FROM folders WHERE id=".$this->id;
		
		$properties = $this->db->queryrow($sql,'assoc');
		
		if(!is_array($properties)){
			throw new Exception('Folder Id not found: '.$this->id);
		}
		
		$this->properties = $properties;
		
		return true;
	}
	
	public function update(){ //or PUT
		// Takes the contents of $this->properties and puts it back in the database
		$sql = '';
		
		foreach($this->properties as $k=>$v){
			if($k!='id'){ //id should not be included
				if(!empty($sql)){
					$sql .= ', ';
				}
				if(in_array($k, array('parent'))){ //don't want quotes around these keys' values
					$sql .= $k."=".$v;
				}
				else{
					$sql .= $k."='".$v."'";
				}
			}
		}
		
		$sql = "UPDATE folders SET ".$sql." WHERE id=".$this->properties['id'];
		
		if($this->db->query($sql)){
			return true;
		}
		else{
			throw new Exception('Error updating folder with id: '.$this->properties['id']);
		}
	}
	
	//To delete a folder, we have to loop through and delete everything inside that folder first.
	public function delete(){
		$contents = $this->getContents(true);
		$success = true;
		
		//first try deleting all the contents of the folder.
		try{
			foreach($contents as $item){
				$item->delete();
			}
		}
		catch(Exception $e){
			return false;
		}
		
		//if that didn't work it will have thrown an exception and returned so now we know it's safe to try and delete this folder
		$sql = "DELETE FROM folders WHERE id=".$this->properties['id'];
		if(!$this->db->query($sql)){
			throw new Exception('Error deleting folder entry: '.$this->getFilesystemUrl());
			return false;
		}
		
		return true;
	}
	
	
	
	public function setName($name){
		if(!preg_match('/[0-9a-zA-Z_-]+/Ui', $name)){
			throw new Exception('Invalid name: use only a-z 0-9 - and _');
			return false;
		}
		else{
			$this->properties['name'] = $name;
			return true;
		}
	}
	
	public function setVisibility($visibility){
		$visibility = strtolower($visibility);
		if(!in_array($visibility, array('private','public'))){
			throw new Exception('Invalid value for visibility: must be either private or public');
			return false;
		}
		else{
			$this->properties['visibility'] = $visibility;
			return true;
		}
	}
	
	public function setParent($parent){
		if(!is_numeric($parent)){
			throw new Exception('Invalid value for parent');
			return false;
		}
		$dbcheck = $this->db->query("SELECT id FROM folders WHERE id=".$parent);
		if($parent!=0 && !$dbcheck){
			throw new Exception('Parent does not exist in database');
			return false;
		}
		else{
			$this->properties['parent'] = $parent;
			return true;
		}
	}
	
	public function getId(){
		return $this->properties['id'];
	}
	
	public function getName(){
		return $this->properties['name'];
	}
	
	// Format should be a date format. Will return it as is if left null
	public function getCreated($format=null){
		return Util::formatDate($this->properties['created'],$format);
	}
	
	// Format should be a date format. Will return it as is if left null
	public function getModified($format=null){
		return Util::formatDate($this->properties['modified'],$format);
	}

	// Who has permission to see this file? Will implement when user / group model is in place
	public function getPermissions(){
		return '';
	}
	
	public function getVisibility(){
		return $this->properties['visibility'];
	}
	
	public function getEnclosingFolderId(){
		return $this->properties['parent'];
	}
	
	public function getEnclosingFolderUrl(){
		$parent = $this->getEnclosingFolderId();
		$url = '';
		while($parent != 0){
			$sql = "SELECT parent, name FROM folders WHERE id=".$parent;
			$result = $this->db->queryrow($sql,'assoc');
			if(!$result){ //if no row is returned then the database is broken somehow. Should never happen.
				throw new Exception('Folder with id: '.$parent.' not found');
			}
			else{
				$parent = $result['parent'];
				$url = '/'.$result['name'].$url;
			}
		}
		return WEBROOT.'/f'.$url;
	}
		
	public function getEnclosingFolder(){
		try{
			return new Folder($this->getEnclosingFolderId());
		}
		catch(Exception $e){
			return false;
		}
	}
	
	public function getFilesystemUrl(){
		return $this->getEnclosingFolderUrl().'/'.$this->getName();
	}

/*
* Gets the content of the folder
* @param	Boolean	foldersfirst	When true puts the folders first (a la Windows), when false (default) mixes folders in with files (a la Mac).
* @returns	Array	Alphabetically sorted array of the folder contents as File and Folder objects
*/
	public function getContents($foldersfirst=false){
		$items = array();
		
		//first get folders
		$sql = "SELECT * FROM folders WHERE parent=".$this->properties['id']." ORDER BY name";
		$results = $this->db->query($sql,'assoc');
		foreach($results as $result){
			$items[] = new Folder($result['id']);
		}
		
		//now get all the files
		$sql = "SELECT * FROM files WHERE parent=".$this->properties['id']." ORDER BY name";
		$results = $this->db->query($sql,'assoc');
		foreach($results as $result){
			$items[] = new File($result['id']);
		}
		
		//if the folders are to be sorted alphabetically with the files, do that.
		if(!$foldersfirst){
			usort($items, create_function('$a,$b','return strcmp($a->getName(), $b->getName());'));
		}
		
		return $items;
	}
	
	public static function getRootContents($foldersfirst=false){
		$items = array();
		$db = Database::getInstance();
		
		//first get folders
		$sql = "SELECT * FROM folders WHERE parent=0 ORDER BY name";
		$results = $db->query($sql,'assoc');
		foreach($results as $result){
			$items[] = new Folder($result['id']);
		}
		
		//now get all the files
		$sql = "SELECT * FROM files WHERE parent=0 ORDER BY name";
		$results = $db->query($sql,'assoc');
		foreach($results as $result){
			$items[] = new File($result['id']);
		}
		
		//if the folders are to be sorted alphabetically with the files, do that.
		if(!$foldersfirst){
			usort($items, create_function('$a,$b','return strcmp($a->getName(), $b->getName());'));
		}
		
		return $items;
	}
	
	// Takes a path (string) and looks up in the database to return a folder id that matches, or false if not found
	public static function getIdFromPath($path){
		$db = Database::getInstance();
		
		if(!is_string($path)){ return false; }
		
		$pathParts = explode('/', $path);
	
		$whereclauses = array();
		$sql = "SELECT * FROM folders f0";
		for($i=0;$i<count($pathParts);$i++){
			$pathPart = $pathParts[$i];
			if($i!=(count($pathParts)-1)){
				$sql .= " LEFT JOIN folders f".($i+1)." ON (f".$i.".id = f".($i+1).".parent)";
			}
			$whereclauses[] = "f".$i.".name='".$pathPart."'";
		}
		$sql .= " WHERE ".implode(' AND ', $whereclauses);
		
		$result = $db->queryrow($sql,'assoc');
		
		return $result?$result['id']:false;
	}
}
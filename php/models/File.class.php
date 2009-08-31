<?php
/**
* File - represents a file
*/
class File{
	
	private $db;
	private $properties = array();
	private $id;
	private $path;
	
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
			throw new Exception('Constructor argument for File must be an Int or a String: '.$id);
		}
		
		if(isset($this->id)){
			$this->read();
		}
		else{
			$this->path = $id;
		}
	}
	
	// CRUD operations
	
	public function create($file, $visibility='private'){ // or POST
		// This should be called when the object is created with a path that doesn't exist yet.
		// The function will work back through the path until it finds a folder that exists,
		//  then creates the subsequent subfolders to create the full path
		
		if(!isset($this->path)){
			return false;
		}
		
		//check everything's in place with $file
		if(!isset($file['name']) || !isset($file['type']) || !isset($file['size']) || !isset($file['tmp_name']) || !isset($file['error']) || !is_readable($file['tmp_name'])){
			return false;
		}
		
		// sanitise $visibility
		$visibility = in_array($visibility, array('private','public'))?$visibility:'private';
		
		if($this->getIdFromPath($this->path)){
			//if the path somehow exists then fail because we don't want to accidentally overwrite another file
			return false;
		}
		else{
			$pathParts = array();
			$currentPath = $this->path;
			do{
				//chop off the last section of the path and pop it in $pathParts for later
				$splitPath = explode('/', $currentPath);
				array_push($pathParts, array_pop($splitPath));
				$currentPath = implode('/',$splitPath);
			} while(!($currentPathId = Folder::getIdFromPath($currentPath)));
			//It's ok to use Folder:: here because it will have always lopped off the file by the time it reaches this point
			
			//now we have the closest existing parent folder id in $currentPathId and the sub-folders to be made in $pathParts
			//loop through the folders in $pathParts creating them one at a time
			while(!is_null($pathPart = array_pop($pathParts))){
				if(count($pathParts)==0){
					//It's the last part (the filename)
					$type = 'file';
					$sql = "INSERT INTO files SET name='".$pathPart."', parent=".$currentPathId.", created=NOW(), modified=NOW(), type='".$file['type']."', size=".filesize($file['tmp_name']).", visibility='".$visibility."', filename='".$file['name']."'";
				}
				else{
					//it's a folder
					$type = 'folder';
					$sql = "INSERT INTO folders SET name='".$pathPart."', parent=".$currentPathId.", created=NOW(), modified=NOW(), visibility='".$visibility."'";
				}
				
				if(!$this->db->query($sql)){
					throw new Exception("Error inserting new ".$type." '".$pathPart."' with parent id ".$currentPathId);
				}
				$currentPathId = $this->db->insert_id();
				
				if(count($pathParts)==0){ //if it's the file we need to move it to the filestore from it's temp location
					if(!move_uploaded_file($file['tmp_name'], FILESTORE.'/'.$currentPathId.'-'.$file['name'])){
						//if it failed we need to scrap the database entry and return false. Any folders created are colateral damage.
						$this->db->query("DELETE FROM files WHERE id=".$currentPathId);
						return false;
					}
				}
			}
			
			$this->id = $currentPathId;
			$this->read();
			return true;
		}
	}
	
	public function read(){ // or GET
		//reads the file object and stores the data in $this->properties. Requires $this->id to be set.
		if(!isset($this->id)){
			throw new Exception('Id not set in File object');
		}
		
		$sql = "SELECT * FROM files WHERE id=".$this->id;
		
		$properties = $this->db->queryrow($sql,'assoc');
		
		if(!is_array($properties)){
			throw new Exception('File Id not found: '.$this->id);
		}
		
		$this->properties = $properties;
		
		return true;
	}
	
	public function update(){ // or PUT
		
	}
	
	public function delete(){
		$sql = "DELETE FROM files WHERE id=".$this->properties['id'];
		if(!$this->db->query($sql)){
			throw new Exception('Error deleting file entry: '.$this->getFilesystemUrl());
			return false;
		}
		
		//That's the databae out of the way, better clean up the file as well...
		if(file_exists($this->getPath())){
			if(!unlink($this->getPath())){
				throw new Exception('Error deleting file from server: '.$this->getPath());
				return false;
			}
		}
		//I guess if the file doesn't exist then it doesn't need deleting - no need for an error
		
		return true;
	}
	
	
	// Other functions
	
	public function getId(){
		return $this->properties['id'];
	}
	
	public function getFilename(){
		return $this->properties['name'];
	}
	
	//maps to getFilename
	public function getName(){
		return $this->getFilename();
	}
	
	public function getPath(){
		return FILESTORE.'/'.$this->getFilestoreFilename();
	}
	
	// oom = order of magnitude (Eg. b, k, m, t, p... I ain't supporting anything larger!!)
	public function getSize($oom='b'){
		$size = $this->properties['size'];
		switch($oom){
			case 'k':
				$size = round((float)($size/1024),2);
				break;
			case 'm':
				$size = round((float)($size/1024/1024),2);
				break;
			case 't':
				$size = round((float)($size/1024/1024/1024),2);
				break;
			case 'p':
				$size = round((float)($size/1024/1024/1024/1024),2);
				break;
			case 'b':
			default:
//				$size = $size;
				break;
		}
		return $size;
	}
	
	// Calls getSize but figures out which order of magnitude to use and returns the value with the oom suffix.
	public function getAutoSize(){
		
		$size = $this->getSize('b');
		if($size<1000){
			return $size.'b';
		}
		
		$size = $this->getSize('k');
		if($size<1000){
			return $size.'kb';
		}
		
		$size = $this->getSize('m');
		if($size<1000){
			return $size.'mb';
		}
		
		$size = $this->getSize('t');
		if($size<1000){
			return $size.'tb';
		}
		
		$size = $this->getSize('p');
		return $size.'pb';
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
	
	public function getType(){
		return $this->properties['type'];
	}
	
	public function getSimpleType(){
		$type = $this->getType();
		return substr($type, strpos($type, '/')+1);
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
	
	// This returns a Folder object.
	public function getEnclosingFolder(){
		try{
			return new Folder($this->getEnclosingFolderId());
		}
		catch(Exception $e){
			return false;
		}
	}
	
	public function getFilesystemUrl(){
		return $this->getEnclosingFolderUrl().'/'.$this->getFilename();
	}
	
	public function getDownloadUrl(){
		return str_replace('/f/', '/d/', $this->getFileSystemUrl());
	}
	
	// This function will ultimately try to detect the presence of a plugin which allows a preview of this type of file to be generated
	// For now it returns false
	public function isPreviewable(){
		return false;
	}



	public static function getIdFromPath($path){
		$db = Database::getInstance();
		
		if(!is_string($path)){
			return false;
		}
		
		$pathParts = explode('/', $path);

		$whereclauses = array();
		$sql = "SELECT f.id FROM folders f0";
		for($i=0;$i<count($pathParts)-1;$i++){
			$pathPart = $pathParts[$i];
			if($i!=(count($pathParts)-2)){
				$sql .= " LEFT JOIN folders f".($i+1)." ON (f".$i.".id = f".($i+1).".parent)";
			}
			$whereclauses[] = "f".$i.".name='".$pathPart."'";
		}
		//Add the file clause
		$sql .= " LEFT JOIN files f ON (f.parent = f".(count($pathParts)-2).".id)";
		//Add the conditions for the folders
		$sql .= " WHERE ".implode(' AND ', $whereclauses);
		//And the condition for the file
		$sql .= " AND f.name='".$pathParts[count($pathParts)-1]."'";
		
		$result = $db->queryrow($sql,'assoc');
		
		return $result?$result['id']:false;
	}
	
	
	
	private function getFilestoreFilename(){
		return $this->properties['id'].'-'.$this->properties['filename'];
	}
		
}

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
		if(is_numeric($path)){
			$sql = "SELECT * FROM files WHERE id=".$path;
		}
		else if(is_string($path)){
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
			
			$result = $this->db->query($sql,'assoc');
			
			return $this->db->num_rows()?new File($result['id']):false;
		}
		else{
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
	
	public function isFolder($path){
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

		return $this->db->num_rows($sql)?true:false;
	}
	
}

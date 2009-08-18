<?php
/**
* Database handles all database operations in the site.
*/
class Database{
	
	private $query_log = array();
	private $link;
	private $numrows; //The number of rows returned from the last query
	private $insertid; //The insert ID of the last query, if it generated one.
	
	private static $instance;
	
	function Database($svr='',$nm='',$usr='',$pass=''){
		//Connect to database
		$this->link = mysql_connect(empty($svr)?DBSERVER:$svr, empty($usr)?DBUSER:$usr, empty($pass)?DBPASS:$pass) or die("Error connecting to database");
		mysql_set_charset('utf8',$this->link);
//		mysql_query("SET CHARACTER SET utf8", $this->link);
//		mysql_query('SET NAMES utf8', $this->link);
		mysql_select_db(empty($nm)?DBNAME:$nm, $this->link) or die("Error accessing database");
		
		//setup unary instance
		if (!isset(self::$instance)) {
			self::$instance = $this;
		}
		else{
			return false;
		}
	}
	
	public static function getInstance(){
		/* //If we were building in the connection variables, we could use the following so we never had to explicitly instantiate the class:
		if (!isset(self::$instance)) {
			$c = __CLASS__;
			self::$instance = new $c;
		}
		return self::$instance;
		*/
		
		//But instead we shall just require that it must  have been instantiated once previoiusly:
		return (isset(self::$instance))?self::$instance:false;
	}
	
	public function __clone(){
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	
	function query($sql,$type=''){
		$this->query_log[] = $sql;
		$this->clean($sql);
		$result = mysql_query($sql, $this->link);
		$this->insertid = mysql_insert_id($this->link);
		
		if(is_bool($result)){
			return $result;
		}
		else if(is_resource($result)){
			$this->numrows = mysql_num_rows($result);
			$result_array = array();
			switch($type){
				case 'object':
					while($row = mysql_fetch_object($result)){
						$result_array[] = $row;
					}
					break;
				case 'array':
					while($row = mysql_fetch_array($result,MYSQL_NUM)){
						$result_array[] = $row;
					}
					break;
				case 'both':
					while($row = mysql_fetch_array($result,MYSQL_BOTH)){
						$result_array[] = $row;
					}
					break;
				case 'assoc':
					while($row = mysql_fetch_array($result,MYSQL_ASSOC)){
						$result_array[] = $row;
					}
					break;
				default:
					//If no type is specified, we use our custom result handler
					return new DBResult($result);
					break;
			}
			return $result_array;
		}
		else{
			die("Error processing query");
		}
	}
	
	//Function that runs a query and returns the first row only
	function queryrow($sql,$type=''){
		$result = $this->query($sql,$type);
		if($result){
			return (gettype($result)=='object')?$result->next():$result[0];
		}
		else{
			return false;
		}
	}
	
	//Function that runs a query and returns the specified variable from the first row.
	function queryvar($sql,$var){
		$result = $this->query($sql,'assoc');
		if(!$result || !($row = $result[0])) return false;
		return isset($row[$var])?$row[$var]:false;
	}
	
	//Returns the number of rows found by the last query if no SQL is passed
	//or if SQL is provided, the number of rows the SQL returns
	function num_rows($sql=''){
		if(empty($sql)){
			return $this->numrows;
		}
		else{
			if($this->query($sql)){
				return $this->numrows;
			}
			else{
				return false;
			}
		}
	}
	
	function insert_id(){
		return $this->insertid;
	}
	
	//Attempts to clean SQL to prevent SQL injection and hacking.
	function clean($sql){
		//TODO
		return $sql;
	}
	
	function query_log(){
		return $this->query_log;
	}
	
	//gets the number of rows affected by the last operation
	function get_affected_rows(){
		return mysql_affected_rows($this->link);
	}
	
	//function returns a DBRecord based on the arguments provided
	function get_record($table, $conditions){
		return DBRecord::new_record($table, $conditions);
	}
}


//Result class that allows manipulation of the result set with a set of useful functions
class DBResult{
	private $result;
	private $pos;
	
	function DBResult($result){
		$this->result = $result;
		$this->pos = 0;
	}
	
	function reset(){
		$this->pos=0;
		mysql_data_seek($this->result, $this->pos);
	}

	function first($type=''){
		if(mysql_num_rows($this->result)==0){
			return false;
		}
		else{
			$this->pos=0;
			mysql_data_seek($this->result, $this->pos);
		}
		
		return $this->fetch_row($type);
	}

	function previous($type=''){
		if(mysql_num_rows($this->result)==0 || $this->pos<=1){
			return false;
		}
		else{
			$this->pos-=2;
			mysql_data_seek($this->result, $this->pos);
		}
		
		return $this->fetch_row($type);
	}
		
	function current($type=''){
		if(mysql_num_rows($this->result)==0 || $this->pos<=0){
			return false;
		}
		else{
			$this->pos-=1;
			mysql_data_seek($this->result, $this->pos);
		}
		
		return $this->fetch_row($type);
	}
	
	function next($type=''){
		if(mysql_num_rows($this->result)==0 || $this->pos>=mysql_num_rows($this->result)){
			return false;
		}
		return $this->fetch_row($type);
	}
	
	function last($type=''){
		if(mysql_num_rows($this->result)==0){
			return false;
		}
		else{
			$this->pos=mysql_num_rows($this->result)-1;
			mysql_data_seek($this->result, $this->pos);
		}
		
		return $this->fetch_row($type);
	}
	
	function fetch_row($type){
		$this->pos++;
		switch ($type){
			case 'object':
				return mysql_fetch_object($this->result);
			case 'array':
				return mysql_fetch_array($this->result,MYSQL_NUM);
			case 'both':
				return mysql_fetch_array($this->result,MYSQL_BOTH);
			case 'assoc':
			default:
				return mysql_fetch_array($this->result,MYSQL_ASSOC);
		}
	}
	
	function num_rows(){
		return mysql_num_rows($this->result);
	}

}

/**
* DBRecord - provides a handy system for managing a single row in a database table.
* Please use the function new_record to get a new DBRecord, instead of: new DBRecord()
* as this allows us to check the arguments.
* 
* NOTICE: This class depends on the Primary Key of the table being used being called 'id'
*/
class DBRecord{
	private $db;
	
	private $table;
	private $conditions;
	private $fields;
	private $id;
	
	function __construct($table, $conditions){
		$this->db = Database::getInstance();
		$this->table = $table;
		$this->conditions = $conditions;
		$result = $this->db->query("SELECT * FROM ".$table." WHERE ".$conditions, 'assoc');
		$this->fields = $result[0];
		$this->id = $this->fields['id'];
	}
	
	static function new_record($table, $conditions){
		if(empty($table) || empty($conditions)) return false;
		
		$db = Database::getInstance();
		$result = $db->query("SELECT * FROM ".$table." WHERE ".$conditions, 'assoc');
		if(is_bool($result)){
			return $result;
		}
		else if($db->num_rows()>1){
			return false;
		}
		else{
			$c = __CLASS__;
			return new $c();
		}
	}
	
	function field($field,$val=null){
		if(!in_array($field, $this->fields)){
			return false;
		}
		else{
			if(val!=null){
				$this->fields[$field] = mysql_escape_string($val);
				return true;
			}
			else{
				return $this->fields[$field];
			}
		}
	}
		
	function save(){
		$vars = '';
		foreach($this->fields as $k => $v){
			$vars.= empty($vars)?'':', ';
			$vars.= '`'.$k.'`=`'.$v.'`';
		}
		return $this->db->query('UPDATE `'.$this->table.'` SET '.$vars.' WHERE id=`'.$this->id.'`');
	}
}

?>
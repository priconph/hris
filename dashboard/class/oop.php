<?php
require_once __DIR__ . '/../../includes/mssql_pdo.php';

//Set Class name here, it's a good practice to Capitalize the first letter, I've just read it somewhere on the internet... so its up to you if will follow...
class OOP extends mysqli 
{
	private static $instance = null;
	
	//db connection config variables
	private $user;	//Username, from HRIS_DB_USER
	private $pass; //Password, from HRIS_DB_PASS
	private $dbName = "db_acl"; //db_name Note: if you are going to use multiple database just place one here.. use sql to call the other one just in case, for example : database2.tablename
	private $dbHost = "localhost"; //host
	
	//NO NEED TO CHANGE HERE 
	public static function getInstance() {
		if(!self::$instance instanceof self) 
		{
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function __clone()
	{
		throw new LogicException('Clone is not allowed.');
	}
	
	public function __wakeup()
	{
		throw new LogicException('Deserializing is not allowed.');
	}
	
	private function __construct() {
		$user = getenv('HRIS_DB_USER');
		$pass = getenv('HRIS_DB_PASS');
		if ($user === false || $pass === false) {
			throw new RuntimeException('HRIS_DB_USER or HRIS_DB_PASS is not set');
		}
		$this->user = $user;
		$this->pass = $pass;
		parent::__construct($this->dbHost, $this->user, $this->pass, $this->dbName);
		if(mysqli_connect_error())
		{
			exit('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
		}
		// TODO: confirm table charset
		$this->set_charset('utf8mb4');
	}

	////////////////////////////////////UP TO HERE////////////////////////////////////////
	
	////////////////////////////////////PUBLIC FUNCTIONS STARTS HERE//////////////////////////////////////////////
	public function select_query($array_fields,$table,$joins,$sql_where,$sql_order,$sql_limit){
		$query = "SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $array_fields) . " FROM {$table} {$joins} {$sql_where} {$sql_order} {$sql_limit};";
		 // echo $query.'<br><br>';
//		return $query;
		return $this->query($query);
	}
    
    public function select_query_script($array_fields,$table,$joins,$sql_where,$sql_order,$sql_limit){
		$query = "<br>SELECT SQL_CALC_FOUND_ROWS " . implode(", ", $array_fields) . " FROM {$table} {$joins} {$sql_where} {$sql_order} {$sql_limit};<br>";
//		 echo $query.'<br><br>';
		return $query;
	}
    
	public function insert_query($table,$array_fields,$array_values){
		$new_values = $this->add_quotations($array_values); 
		$query 		= "INSERT INTO {$table} (". implode(", ", $array_fields).") VALUES (". implode(",", $new_values) .");";
       // echo $query;
		$this->query($query);
		$this->save_logs('1',$query);
		return "New record has been saved";
	}

	public function update_query($table,$array_fields,$array_values,$pkid){
		$update_array =  $this->create_update_param($array_fields,$array_values);
		$query 		  = "UPDATE {$table} SET ". implode(",", $update_array) ."WHERE pkid='{$pkid}';";
		$this->query($query);
		$this->save_logs('2',$query);
       // echo $query;
//		return $query;
		return "Record has been updated";
	}

	public function update_query_detailed($table,$array_fields,$array_values,$where){
		$update_array =  $this->create_update_param($array_fields,$array_values);
		$query 		  = "UPDATE {$table} SET ". implode(",", $update_array) .$where;
//        echo $query.'<br>';
		$this->query($query);
		$this->save_logs('2',$query);
		return $query;
//		return "Record has been updated";
	}

	public function delete_query($table,$pkid){
		$query = "DELETE FROM {$table} WHERE `pkid` = '{$pkid}';";
		$this->query($query);
		$this->save_logs('3',$query);
		return "Record has been deleted";
	}

	public function delete_query_details($table,$pkid){
		$query = "DELETE FROM {$table} WHERE `fkid_ot_main` = '{$pkid}';";
		$this->query($query);
		$this->save_logs('3',$query);
		return "Record has been deleted";
	}
	
	public function add_quotations($array_values){
		$array_values_r = array();
		for($i=0;$i<count($array_values);$i++){
			$array_values_r[$i] = "'".$array_values[$i]."'";
		}
		return $array_values_r;
	}

	public function create_update_param($array_fields,$array_values){
		$array_update = array();
		for($i=0;$i<count($array_fields);$i++){
			$array_update[$i] = "`".$array_fields[$i]."` = "."'".$array_values[$i]."' ";
		}
		return $array_update;
	}
	
	public function save_logs($type,$query){
		$date_today = date('Y-m-d H:i:s');
		$new_query = str_replace("'",'"',$query);
		$logs = "INSERT INTO `tbl_logs` (`type_of_query`,`query`,`date`) VALUES('".$type."','".$new_query."','".$date_today."');";
		$this->query($logs);
	}
	
		public function check_unset($variable){
		if(!isset($variable)){
			$new_variable = '';
		}else{
			$new_variable = $variable;
		}
		return $new_variable;
	}
	
	public function check_if_array($array){
		if(!is_array($array)){
			$new_array = array($array);
		}else{
			$new_array = $array;
		}
		return $new_array;
	}
	
}

//THIS CLASS CONNECTS INTO THE YPICS SERVER OR MSSQL Server	
class YPICS{
	//MALACHIXD CONNECTION INFO: MSSQL_HOST, MSSQL_DB, MSSQL_USER, MSSQL_PASS (see includes/mssql_pdo.php)
	private $db_handleM = null; //handler 
	
	//FUNCTION TO CONNECT TO MALACHI SERVER	
    private function connect_DBM(){
        try {
            $this->db_handleM = mssql_pdo_connect();
            return true;
        } catch (PDOException | RuntimeException $e) {
            error_log('YPICS connect_DBM: ' . $e->getMessage());
            return false;
        }
    }
   
    //FUNCTION TO CLOSE MALACHI SERVER (the shared PDO instance closes at script end)
    private function close_DBM(){
        $this->db_handleM = null;
    }
	
	/////////////////////////////////PUBLIC FUNCTIONS///////////////////////////////////////////////	
	//FUNCTION TO LOAD PO NUM FROM MALACHI DATABASE
	public function selectPoNum(){ //sample
		$db_found = $this->connect_DBM();
		if($db_found){
			$SQL = "SELECT * FROM VwProdSchedMaster ORDER BY PONum";
			try {
				return mssql_pdo_query($SQL);
			} catch (PDOException $e) {
				error_log('YPICS selectPoNum: ' . $e->getMessage());
				return false;
			}
		}
		else{
			$errorMsg = "Database not found, Please contact your administrator";
		}
		$this->close_DBM();
	}

}
?>

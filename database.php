<?php
class DatabaseGateway
{
	private $host;
	private $user;
	private $pass;
	private $dbName;

	private static $instance;

	private static $connection;
	private $results;
	private $numRows; // optional
	private $numFields;
	private $totalRows;

	private function __construct()
	{
	}

	// singleton pattern
	static function getInstance()
	{
	
		if(!self::$instance)
		{
			self::$instance = new self(); 
										 	// $instance = new database() ...and the same $instance property value is
		}									//  sharedfor every instance of the class database.
		return self::$instance;
	}

	function connect($host, $user, $pass, $dbName)
	{
		$this->host = $host;
		$this->user = $user;
		$this->pass = $pass;
		$this ->dbName = $dbName;
		 // append "p:" for a persistent db connection
		 /*  The idea behind persistent connections is that a connection between a client process 
		 and a database can be reused by a client process, rather than being created and destroyed 
		 multiple times. This reduces the overhead of creating fresh connections every time one is 
		 required, as unused connections are cached and ready to be reused.
		 
		Connection pooling
		The mysqli extension supports persistent database connections, which are a special kind of 
		pooled connections. By default, every database connection opened by a script is either explicitly 
		closed by the user during runtime or released automatically at the end of the script. A persistent 
		connection is not. Instead it is put into a pool for later reuse, if a connection to the same server
		 using the same username, password, socket, port and default database is opened. 
		 Reuse saves connection overhead.

         Every PHP process is using its own mysqli connection pool. Depending on the web server deployment model, 
         a PHP process may serve one or multiple requests. Therefore, a pooled connection may be used by one or 
         more scripts subsequently.
		 */
		if (!self::$connection) {
			mysqli_report(MYSQLI_REPORT_STRICT);  // Tell MySQL to throw report errors.
	//		mysqli.allow_persistent;
			try {
			self::$connection = mysqli_connect("p:".$this->host,
					$this->user,
					$this->pass,
					$this->dbName);
			} catch (mysqli_sql_exception $e) {
				throw $e;
			}
	//		echo "<p> new db connection</p>";
	//		$temp = mysqli_get_connection_stats(self::$connection);
	//		echo "<p> persistent:  {$temp["pconnect_success"]}";
	//		echo ", reused:  {$temp["connection_reused"]}</p>";
		}
	//	else {
	//		echo "<p> existing db connection</p>";
	//	} 
				
	}

	public function doInsert($sql)
	{

		try {
			$this->results = mysqli_query(self::$connection, $sql);
		} catch (mysqli_sql_exception $e) {
			throw $e;
		}

	}	
		
	public function doQuery($sql)
	{

		try {
		$this->results = mysqli_query(self::$connection, $sql);
		} catch (mysqli_sql_exception $e) {
			throw $e;
		}
			
			if (stristr($sql,'SELECT') && $this->results && $this->results->num_rows > 0) {
			 
			// NOTE: The line below was giving me this error:
			// "Trying to get property of non-object"
			//$this->numRows = $this->results->num_rows;

			$this->numRows = $this->results->num_rows;
			$this->numFields = mysqli_field_count(self::$connection);
			$sql = "SELECT FOUND_ROWS() AS `found_rows`;";
			$rows = mysqli_query(self::$connection, $sql);
			$rows = mysqli_fetch_assoc($rows);
			$this->totalRows = $rows['found_rows'];
			
		//	$this->numFields = $this->results->num_fields;
			
		} // end if
		/*
		else {
				echo $this->results;
		}
		*/
	}

	public function loadObjectList()
	{
		$obj = "No Results";
		 
		if ($this->results)
		{
			try {
			$obj = mysqli_fetch_assoc($this->results);  //returns an associative array {key,value} pairs
		
			} catch (mysqli_sql_exception $e) 
			{
				throw $e;
			}
		}
		return $obj;
	}
	public function getNumRows()
	{ 
		return $this->numRows;
	}
	public function getNumFields()
	{
		return $this->numFields;
	}
	public function getTotalRows()
	{
		return $this->totalRows;
	}	
	public function returnResults()
	{
		$data = array();
		$i=0;
		try {
		while($data = mysql_fetch_assoc($this->results)){
			$rows[$i] = $data;
		}	
		$i++;
		} catch (mysqli_sql_exception $e) 
			{
				throw $e;
			}
		$this->freeResults();   
		return $rows;
	}	
	public function freeResults() {
		try {
		mysqli_free_result($this->results);  //known to have a bug with xdebug
		} catch (mysqli_sql_exception $e) 
			{
				throw $e;
			}
	}
	
}	 // end class
?>
<?php
/**
 * @author:
 * Created Date: 7th Aug 2012
 * Modified By:
 * Modified Date: 7th Aug 2012
 * @desc:  This is the database connection file contain database connection details
 * ls .			                                           
 */
class  Dbconfig {
	private $hostname;
	private $username;
	private $password;
	private $database;
	private $connect;
	private $select_db;
	/**
	 * @author:
	 * @desc: This function used to connect database
	 */
	public function ConnectionOpen() {

		$this->hostname = "localhost";
		$this->username = "crmuser";
		$this->password = "Sidbi@2019";
		$this->database = "sidbi_crm";
		
		$this->connect = mysql_connect($this->hostname,$this->username,$this->password)or die(mysql_error());
		if(!$this->connect) {
			echo "Mysql Not Connected";
		}/* else {
		echo 'Database connected';
		}*/
		$this->select_db = mysql_select_db($this->database);
		if(!$this->select_db){
			echo "Database Not Connected";
		}
	} // end of connectionopen
	/**
	 * @author:
	 * @desc: This function is used to terminate the connection with database
	 */
	public function ConnectionClose() {
		mysql_close($this->connect);
	}
	/**
	 * @author:
	 * @desc: This function is used to get the database name
	 */
	public function GetDb(){
		$name = $this->database;
		
		return $name;
	}

	/**
	 * @author:
	 * @desc: This function is used to get the Host name
	 */
	public function HostName(){
		$name = $this->hostname;
		
		return $name;
	}
}// end of Class 
?>

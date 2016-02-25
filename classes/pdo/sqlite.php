<?php
/**
 * UniDB PDO Sqlite Driver
 * @file:      pdo/sqlite.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

class Sqlite_Database_PDO extends Sqlite_Database {
	use UniDB_PDO;
	protected $pdo_identifier = 'sqlite';
	
	public function connect($ip, $user, $password, $db="") {
		if($db)
			return UniDB_PDO::connect($ip, $user, $password);
		
		$this->ip = $ip;
		$this->user = $user;
		$this->password = $password;

		return true;
	}
	
	public function select_db($db) {
		try {
			$string = $this->build_pdo_string($this->ip, $this->user, $this->password, $db);
			$this->conn = new PDO($string, $this->user, $this->password);
			$this->db = $db;
		} catch (PDOException $e) {
			$this->conn = false;
			return $this->log_error('Database selection failed: '.$e->getMessage());
		}
			
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}
	
	public function build_pdo_string($ip, $user, $password, $db) {
		$string = $this->pdo_identifier . ':';
		$file_ext = isset($this->options['file_ext']) ? $this->options['file_ext'] : '.db';
		$string .= $ip . '/' . $db . $file_ext;
		if ($user)
			$string .= ";user=".$user;
		if($password)
			$string .= ";password=".$password;
		return $string;
	}
}

?>
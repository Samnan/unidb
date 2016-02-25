<?php
/**
 * UniDB PDO Driver
 * @file:      pdo.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

trait UniDB_PDO {

	public function connect($ip, $user, $password, $db="") {
		if (!extension_loaded('pdo_'.$this->pdo_identifier)) {
			return $this->log_error('PDO extension is not installed');
		}
		try {
			$string = $this->build_pdo_string($ip, $user, $password, $db);
			$this->conn = new PDO($string, $user, $password);
		} catch (PDOException $e) {
			$this->conn = false;
			return $this->log_error('Database connection failed to the server: '.$e->getMessage());
		}
			
		$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$this->ip = $ip;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;

		return true;
	}

	public function disconnect() {
		$this->conn = null;
		return true;
	}

	public function query($sql, $stack=0) {
		if (!$this->conn) {
			return $this->log_error("DB: Connection has been closed");
			return false;
		}

		$this->result[$stack] = "";

		$this->lastQuery = $sql;
		$this->queryTime = $this->get_time();
		try {
			$this->result[$stack] = $this->conn->query($sql);
		} catch (PDOException $e) {
			return $this->log_error( $this->conn->errorInfo()[2] );
		}
		
		$this->queryTime = $this->get_time() - $this->queryTime;

		return true;
	}

	public function get_insert_id() {
		return $this->conn->lastInsertId();
	}

	public function fetch_row($stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = PDO::FETCH_BOTH;
		else if ($type == "num")
			$type = PDO::FETCH_NUM;
		else if ($type == "assoc")
			$type = PDO::FETCH_ASSOC;

		if (!is_object($this->result[$stack])) {
			return $this->log_error("fetch_row[$stack] failed");

		}
		return $this->result[$stack]->fetch($type);
	}

	public function fetch_row_num($num, $stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = PDO::FETCH_BOTH;
		else if ($type == "num")
			$type = PDO::FETCH_NUM;
		else if ($type == "assoc")
			$type = PDO::FETCH_ASSOC;

		if (!is_object($this->result[$stack])) {
			return $this->log_error("fetch_row[$stack] failed");

		}
		return $this->result[$stack]->fetch($type, PDO::FETCH_ORI_ABS, $num);
	}

	/* due to PDO driver limitation, number of records in query is not available */
	public function num_rows($stack=0) {
		$count = 0;
		return $count;
	}

	public function num_affected_rows() {
		return $this->result[0]->rowCount();
	}
	
	public function escape($str) {
		return $this->conn->quote($str);
	}
	
	public function build_pdo_string($ip, $user, $password, $db) {
		$string = $this->pdo_identifier . ':';
		if ( strpos($ip, ':') !== FALSE ) {
			list($host, $port) = explode(':', $ip); 
			$string .= 'host='.$host.';port='.$port;
		} else {
			$string .= 'host='.$ip;
		}
		if ($user)
			$string .= ";user=".$user;
		if($password)
			$string .= ";password=".$password;
		if($db)
			$string .= ";dbname=".$db;
		return $string;
	}

}

?>
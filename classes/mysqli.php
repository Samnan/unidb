<?php
/**
 * UniDB Driver Mysqli
 * @file:      mysqli.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

require_once( 'core.php' );
class Mysqli_Database extends Core_Database {

	function __construct() {
		parent::__construct();
	}

	public function connect($ip, $user, $password, $db="")	{
		if (!function_exists('mysqli_connect')) {
			return $this->log_error('Mysqli client library is not installed');
		}

		$this->conn = @mysqli_connect($ip, $user, $password);
		if (!$this->conn)
			return $this->log_error('Database connection failed to the server');

		if ($db && !@mysqli_select_db($this->conn, $db))
			return $this->log_error(mysqli_error($this->conn));

		$this->ip = $ip;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;

		return true;
	}

	public function disconnect() {
		@mysqli_close($this->conn);
		$this->conn = false;
		return true;
	}
	
	public function get_features() {
		$this->query("SHOW VARIABLES LIKE 'version'", '_features');
		if ($this->num_rows('_features') == 1) {
			$row = $this->fetch_row('_features');
			$version = (float) $row['Value'];
			if ($version >= 5.0) {
				$this->features['views'] = TRUE;
				$this->features['functions'] = TRUE;
				$this->features['procedures'] = TRUE;
				$this->features['triggers'] = TRUE;
				$this->query("SHOW VARIABLES LIKE 'event_scheduler'", '_features');
				if ($this->num_rows('_features') == 1) {
					$this->features['events'] = TRUE;
				}
			}
		}
		
		return $this->features;
	}

	public function select_db($db) {
		$this->db = $db;
		return mysqli_select_db($this->conn, $this->db);
	}

	public function query($sql, $stack=0) {		// call with query($sql, 1) to store multiple results
		if (!$this->conn) {
			return $this->log_error("DB: Connection has been closed");
		}

		$this->result[$stack] = "";

		$this->lastQuery = $sql;
		$this->queryTime = $this->get_time();
		$this->result[$stack] = @mysqli_query($this->conn, $sql);
		$this->queryTime = $this->get_time() - $this->queryTime;

		if ($this->result[$stack] === FALSE) {
			return $this->log_error( mysqli_error($this->conn) );
		}

		return true;
	}

	public function get_insert_id() {
		return mysqli_insert_id($this->conn);
	}

	public function fetch_row($stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = MYSQLI_BOTH;
		else if ($type == "num")
			$type = MYSQLI_NUM;
		else if ($type == "assoc")
			$type = MYSQLI_ASSOC;

		if (!$this->result[$stack]) {
			return $this->log_error("fetch_row[$stack] failed");
		}
		return @mysqli_fetch_array($this->result[$stack], $type);
	}

	public function fetch_row_num($num, $stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = MYSQL_BOTH;
		else if ($type == "num")
			$type = MYSQLI_NUM;
		else if ($type == "assoc")
			$type = MYSQLI_ASSOC;

		if (!$this->result[$stack]) {
			return $this_log_error("fetch_row_num[$stack] failed");
		}

		mysqli_data_seek($this->result[$stack], $num);
		return @mysqli_fetch_array($this->result[$stack], $type);
	}

	public function num_rows($stack=0) {
		return mysqli_num_rows($this->result[$stack]);
	}

	public function num_affected_rows() {
		return mysqli_affected_rows($this->conn);
	}

	public function get_databases() {
		$res = mysqli_query($this->conn, "show databases");
		$ret = array();
		while($row = mysqli_fetch_array($res))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_tables( $details = false ) {
		if (!$this->db)
			return array();
		$res = mysqli_query($this->conn, "show table status from `$this->db` where engine is NOT null");
		$ret = array();
		while($row = mysqli_fetch_array($res,  MYSQLI_ASSOC)) {
			$ret[] = $details ?	array(
				$row['Name'], // table name
				$row['Rows'], // number of records,
				$row['Data_length'] + $row['Index_length'], // size of the table
				(empty($row['Update_time']) ? $row['Create_time'] : $row['Update_time'] ), // last update timestamp
			) : $row['Name'];
		}
		return $ret;
	}

	public function get_views() {
		if (!$this->db)
			return array();
		$res = mysqli_query($this->conn, "show table status from `$this->db` where engine is null");
		if (!$res)
			return array();
		$ret = array();
		while($row = mysqli_fetch_array($res))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_procedures() {
		if (!$this->db)
			return array();
		$res = mysqli_query($this->conn, "show procedure status where db = '$this->db'");
		if (!$res)
			return array();
		$ret = array();
		while($row = mysqli_fetch_array($res))
			$ret[] = $row[1];
		return $ret;
	}

	public function get_functions() {
		if (!$this->db)
			return array();
		$res = mysqli_query($this->conn, "show function status where db = '$this->db'");
		if (!$res)
			return array();
		$ret = array();
		while($row = mysqli_fetch_array($res))
			$ret[] = $row[1];
		return $ret;
	}

	public function get_triggers() {
		if (!$this->db)
			return array();
		$res = mysqli_query($this->conn, "select `TRIGGER_NAME` from `INFORMATION_SCHEMA`.`TRIGGERS` where `TRIGGER_SCHEMA` = '$this->db'");
		if (!$res)
			return array();
		$ret = array();
		while($row = mysqli_fetch_array($res))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_events() {
		if (!$this->db)
			return array();
		$res = mysqli_query($this->conn, "select `EVENT_NAME` from `INFORMATION_SCHEMA`.`EVENTS` where `EVENT_SCHEMA` = '$this->db'");
		if (!$res)
			return array();
		$ret = array();
		while($row = mysqli_fetch_array($res))
			$ret[] = $row[0];
		return $ret;
	}

	public function escape($str) {
		return "'".mysqli_escape_string($this->conn, $str)."'";
	}

	public function quote($str) {
		if(strpos($str, '.') === false)
			return '`' . $str . '`';
		return '`' . str_replace('.', '`.`', $str) . '`';
	}
	
	protected function get_expr_string($str) {
		switch($str) {
			case 'datetime':
				return 'NOW()';
			case 'date':
				return 'CURDATE()';
			case 'time':
				return 'CURTIME()';
		}
		
		return $str;
	}

}
?>
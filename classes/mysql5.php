<?php
/**
 * UniDB Driver Mysql5
 * @file:      mysql5.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

require_once( 'core.php' );
class Mysql5_Database extends Core_Database {

	function __construct() {
		parent::__construct();
	}

	public function connect($ip, $user, $password, $db="") {
		if (!function_exists('mysql_connect')) {
			return $this->log_error('Mysql5 client library is not installed');
		}

		$this->conn = @mysql_connect($ip, $user, $password);
		if (!$this->conn)
			return $this->log_error('Database connection failed to the server');

		if ($db && !@mysql_select_db($db, $this->conn))
			return $this->log_error(mysql_error($this->conn));

		$this->ip = $ip;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;

		return true;
	}

	public function disconnect() {
		@mysql_close($this->conn);
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
		return mysql_select_db($this->db);
	}

	public function query($sql, $stack=0) {
		if (!$this->conn) {
			return $this->log_error("DB: Connection has been closed");
			return false;
		}

		$this->result[$stack] = "";

		$this->lastQuery = $sql;
		$this->queryTime = $this->get_time();
		$this->result[$stack] = @mysql_query($sql, $this->conn);
		$this->queryTime = $this->get_time() - $this->queryTime;

		if (!$this->result[$stack]) {
			return $this->log_error( mysql_error($this->conn) );
		}

		return true;
	}

	public function get_insert_id() {
		return mysql_insert_id($this->conn);
	}

	public function fetch_row($stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = MYSQL_BOTH;
		else if ($type == "num")
			$type = MYSQL_NUM;
		else if ($type == "assoc")
			$type = MYSQL_ASSOC;

		if (!$this->result[$stack]) {
			return $this->log_error("fetch_row[$stack] failed");

		}
		return @mysql_fetch_array($this->result[$stack], $type);
	}

	public function fetch_row_num($num, $stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = MYSQL_BOTH;
		else if ($type == "num")
			$type = MYSQL_NUM;
		else if ($type == "assoc")
			$type = MYSQL_ASSOC;

		if (!$this->result[$stack]) {
			return $this_log_error("fetch_row_num[$stack] failed");
		}

		mysql_data_seek($this->result[$stack], $num);
		return @mysql_fetch_array($this->result[$stack], $type);
	}

	public function num_rows($stack=0) {
		return mysql_num_rows($this->result[$stack]);
	}

	public function num_affected_rows() {
		return mysql_affected_rows($this->conn);
	}

	public function get_databases() {
		$res = $this->query("show databases", '_databases');
		$ret = array();
		while($row = $this->fetch_row('_databases'))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_tables() {
		if (!$this->db)
			return array();
		$res = $this->query("show table status from `$this->db` where engine is NOT null", '_tables');
		$ret = array();
		while($row = $this->fetch_row('_tables')) {
			$ret[] = $row['Name'];
		}
		return $ret;
	}

	public function get_views() {
		if (!$this->db)
			return array();
		$res = $this->query("show table status from `$this->db` where engine is null", '_views');
		if (!$res)
			return array();
		$ret = array();
		while($row = $this->fetch_row('_views'))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_procedures() {
		if (!$this->db)
			return array();
		$res = $this->query("show procedure status where db = '$this->db'", '_procs');
		if (!$res)
			return array();
		$ret = array();
		while($row = $this->fetch_row('_procs'))
			$ret[] = $row[1];
		return $ret;
	}

	public function get_functions() {
		if (!$this->db)
			return array();
		$res = $this->query("show function status where db = '$this->db'", '_funcs');
		if (!$res)
			return array();
		$ret = array();
		while($row = $this->fetch_row('_funcs'))
			$ret[] = $row[1];
		return $ret;
	}

	public function get_triggers() {
		if (!$this->db)
			return array();
		$res = $this->query("select `TRIGGER_NAME` from `INFORMATION_SCHEMA`.`TRIGGERS` where `TRIGGER_SCHEMA` = '$this->db'", '_triggers');
		if (!$res)
			return array();
		$ret = array();
		while($row = $this->fetch_row('_triggers'))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_events() {
		if (!$this->db)
			return array();
		$res = $this->query("select `EVENT_NAME` from `INFORMATION_SCHEMA`.`EVENTS` where `EVENT_SCHEMA` = '$this->db'", '_events');
		if (!$res)
			return array();
		$ret = array();
		while($row = $this->fetch_row('_events'))
			$ret[] = $row[0];
		return $ret;
	}

	public function escape($str) {
		return "'".mysql_escape_string($str)."'";
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
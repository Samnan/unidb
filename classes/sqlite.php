<?php
/**
 * UniDB Driver SQLite
 * @file:      sqlite.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

require_once( dirname(__FILE__) . '/core.php' );
class Sqlite_Database extends Core_Database {

	function __construct() {
		parent::__construct();
	}

	public function connect($ip, $user = '', $password = '', $db = '') {
		if (substr($ip, -1) != '/')
			$ip .= '/';

		if ( !is_dir($ip) )
			return $this->log_error('SQLite database folder does not exist');

		if (!function_exists('sqlite_open')) {
			return $this->log_error('SQLite client library is not installed');
		}

		$file_ext = isset($this->options['file_ext']) ? $this->options['file_ext'] : '.db';
		if ($db && !($this->conn = sqlite_open($ip . $db . $file_ext, 0666)) )
			return $this->log_error(sqlite_error_string());

		$this->ip = $ip;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;

		return true;
	}

	public function disconnect() {
		@sqlite_close($this->conn);
		$this->conn = false;
		return true;
	}
	
	public function get_features() {
		$this->features['views'] = TRUE;
		$this->features['triggers'] = TRUE;
		
		return $this->features;
	}

	public function select_db($db) {
		$this->db = $db;
		$file_ext = isset($this->options['file_ext']) ? $this->options['file_ext'] : '.db';
		if ( ! ($this->conn = sqlite_open($this->ip . $db . $file_ext , 0666)) )
			return $this->log_error(sqlite_error_string(sqlite_last_error()));

		return true;
	}

	public function create_db( $name ) {
		if ( empty($name) || is_file($this->ip.$name) ) {
            return false;
		}

		$file_ext = isset($this->options['file_ext']) ? $this->options['file_ext'] : '.db';
		if ( !preg_match('/'.$file_ext.'$/', $name) )
			$name .= $file_ext;
        $result = touch( $this->ip.$name );
		if ($result) {
        	chmod( $this->ip.$name, 0666 );
			return true;
		}
		return false;
	}

	public function query($sql, $stack=0) {
		if (!$this->conn) {
			return $this->log_error("DB: Connection has been closed");
		}

		$this->result[$stack] = "";

		$this->lastQuery = $sql;
		$this->queryTime = $this->get_time();
		$this->result[$stack] = @sqlite_query($sql, $this->conn);
		$this->queryTime = $this->get_time() - $this->queryTime;

		if (!$this->result[$stack]) {
			return $this->log_error( sqlite_error_string(sqlite_last_error($this->conn)) );
		}

		return true;
	}

	public function get_insert_id() {
		return sqlite_last_insert_rowid($this->conn);
	}

	public function fetch_row($stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = SQLITE_BOTH;
		else if ($type == "num")
			$type = SQLITE_NUM;
		else if ($type == "assoc")
			$type = SQLITE_ASSOC;

		if (!$this->result[$stack]) {
			return $this->log_error("fetch_row[$stack] failed");
		}
		return @sqlite_fetch_array($this->result[$stack], $type);
	}

	public function fetch_row_num($num, $stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = SQLITE_BOTH;
		else if ($type == "num")
			$type = SQLITE_NUM;
		else if ($type == "assoc")
			$type = SQLITE_ASSOC;

		if (!$this->result[$stack]) {
			return $this_log_error("fetch_row_num[$stack] failed");
		}

		sqlite_seek($this->result[$stack], $num);
		return @sqlite_fetch_array($this->result[$stack], $type);
	}

	public function num_rows($stack=0) {
		return sqlite_num_rows($this->result[$stack]);
	}

	public function num_affected_rows() {
		return sqlite_changes($this->conn);
	}

	public function get_databases() {
		$file_ext = isset($this->options['file_ext']) ? $this->options['file_ext'] : '.db';
		$ret = array();
		$d = opendir($this->ip);
        while(($entry = readdir($d)) != false) {
            if ($entry!="." && $entry!=".." && is_file($this->ip.$entry) &&
            		( preg_match('/'.$file_ext.'$/', $entry) || preg_match('/.sqlite$/', $entry) ) ) {
				$ret[] = $entry;
            }
        }
		closedir($d);
		return $ret;
	}

	public function get_tables() {
		if (!$this->db)
			return array();
		$this->query("select name from SQLITE_MASTER where type = 'table' order by 1");
		$ret = array();
		while($row = $this->fetch_row())
			$ret[] = $row[0];
		return $ret;
	}

	public function get_views() {
		if (!$this->db)
			return array();
		$this->query("select name from SQLITE_MASTER where type = 'view' order by 1");
		$ret = array();
		while($row = $this->fetch_row())
			$ret[] = $row[0];
		return $ret;
	}

	public function get_triggers() {
		if (!$this->db)
			return array();
		$this->query("select name from SQLITE_MASTER where type = 'trigger' order by 1");
		$ret = array();
		while($row = $this->fetch_row())
			$ret[] = $row[0];
		return $ret;
	}

	public function truncate($tbl) {
		return $this->query('DELETE FROM '.$this->quote($tbl));
	}

	public function escape($str) {
		return '"'.sqlite_escape_string($str).'"';
	}

	public function quote($str) {
		if(strpos($str, '.') === false)
			return '[' . $str . ']';
		return '[' . str_replace('.', '].[', $str) . ']';
	}
	
	protected function get_expr_string($str) {
		switch($str) {
			case 'datetime':
				return "datetime('now')";
			case 'date':
				return "date('now')";
			case 'time':
				return "time('now')";
		}
		
		return $str;
	}

}
?>
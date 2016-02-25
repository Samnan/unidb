<?php
/**
 * UniDB Driver SQLite3
 * @file:      sqlite.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

require_once( dirname(__FILE__) . '/sqlite.php' );
class Sqlite3_Database extends Sqlite_Database {

	protected $stack_last;

	public function connect($ip, $user = '', $password = '', $db = '') {
		if (substr($ip, -1) != '/')
			$ip .= '/';

		if ( !is_dir($ip) )
			return $this->log_error('SQLite database folder does not exist');

		if (!class_exists('SQLite3')) {
			return $this->log_error('SQLite3 client library is not installed');
		}

		if ($db) {
			try {
				$file_ext = isset($this->options['file_ext']) ? $this->options['file_ext'] : '.db';
				$this->conn = new SQLite3($ip . $db . $file_ext);
			} catch(Exception $e) {
				return $this->log_error('Access denied or failed to open database');
			}
		}

		$this->ip = $ip;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;

		return true;
	}

	public function disconnect() {
		if ($this->conn) {
			$this->conn->close();
		}
		$this->conn = false;
		return true;
	}

	public function select_db($db) {
		$this->db = $db;
		$file_ext = isset($this->options['file_ext']) ? $this->options['file_ext'] : '.db';
		try {
			$this->conn = new SQLite3($this->ip . $db . $file_ext);
		} catch(Exception $e) {
			return $this->log_error('Access denied or failed to open database');
		}

		return true;
	}

	public function query($sql, $stack=0) {
		if (!$this->conn) {
			return $this->log_error("DB: Connection has been closed");
		}


		$this->result[$stack] = "";
		$this->stack_last = $stack;
		$this->lastQuery = $sql;
		$this->queryTime = $this->get_time();
		$this->result[$stack] = $this->conn->query($sql);
		$this->queryTime = $this->get_time() - $this->queryTime;

		if (!$this->result[$stack]) {
			return $this->log_error( $this->conn->lastErrorMsg() );
		}

		return true;
	}

	public function get_insert_id() {
		return $this->conn->lastInsertRowID();
	}

	public function fetch_row($stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if($type == "both")
			$type = SQLITE3_BOTH;
		else if ($type == "num")
			$type = SQLITE3_NUM;
		else if ($type == "assoc")
			$type = SQLITE3_ASSOC;

		if (!is_object($this->result[$stack])) {
			return $this->log_error("fetch_row[$stack] failed");
		}
		return $this->result[$stack]->fetchArray( $type );
	}

	public function fetch_row_num($num, $stack=0, $type="") {
		// @@TODO: workaround for fetching specific row from sqlite3 driver needed
		return $this->log_error("Fetch numbered row not supported for SQLite3 driver");
	}

	public function num_rows($stack=0) {
		
		if ($this->result[$stack] !== TRUE && $this->result[$stack] !== FALSE) {
			$sql = 'SELECT COUNT(*) FROM (' . $this->lastQuery . ')';
			if ( $this->query($sql, '_numrows') ) {
				$result = $this->fetch_row('_numrows');
				return $result[0];
			}
		}
		return 0;
	}

	public function num_affected_rows() {
		return $this->result[$this->stack_last]->changes();
	}
}
?>
<?php
/**
 * UniDB Database Core
 * @file:      core.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

class Database_Expression {

	protected $string;

	function __construct( $str ) {
		$this->string = $str; 
	}
	
	public function __toString() {
		return $this->string;
	}
}

class Core_Database {
	protected $ip, $user, $password, $db;

	protected $conn;
	protected $result;

	protected $lastQuery;
	protected $lastError;
	protected $queryTime;

	protected $features;
	
	protected $options;

	function __construct() {
		$this->conn = null;
		$this->result = array();
		
		$this->features = array(
			'schemas'    => FALSE,
			'views'      => FALSE,
			'functions'  => FALSE,
			'procedures' => FALSE,
			'triggers'   => FALSE,
			'events'     => FALSE,
			'sequences'  => FALSE
		);
		
		$this->options = array(
			'result_type' => "both",
			'file_ext'    => ".db" // only needed for SQLite, ignored by other drivers
		);
	}

	public function connect($ip, $user, $password, $db="") {
		return false;
	}

	public function disconnect() {
		return false;
	}
	
	public function get_features() {
		return $this->features;
	}

	public function select_db($db) { }

	public function create_db( $name ) {
		return false;
	}

	public function query($sql, $stack=0) {
		return false;
	}

	public function insert($table, $values) {
		if (!is_array($values))
			return false;

		$sql = "insert into " . $this->quote($table) . " (";

		foreach($values as $field=>$value)
			$sql .= " $field,";

		$sql = substr($sql, 0, strlen($sql) - 1);

		$sql .= ") values (";

		foreach($values as $field=>$value) {
			$sql .= is_object($value) ? $value . "," : ( $value === NULL ? "NULL," : $this->escape($value) . "," );
		}

		$sql = substr($sql, 0, strlen($sql) - 1);

		$sql .= ")";

		return $this->query($sql);
	}

	public function update($table, $values, $condition="") {
		if (!is_array($values))
			return false;

		$sql = "update " . $this->quote($table) . " set ";

		foreach($values as $field=>$value) {
			$sql .= $this->quote($field) . " = " . 
				( is_object($value) ? $value . "," : ( $value === NULL ? "NULL," : $this->escape($value) . "," ) );
		}

		$sql = substr($sql, 0, strlen($sql) - 1);

		if ($condition != "")
			$sql .= " $condition";

		return $this->query($sql);
	}

	public function delete( $table, $condition ) {
		$sql = "delete from " . $this->quote($table) . " " . $condition;
		return $this->query( $sql );
	}

	public function get_insert_id() {
		return 0;
	}

	public function get_result($stack=0) {
		return NULL;
	}

	public function fetch_row($stack=0, $type="") {
		return NULL;
	}

	public function fetch_row_num($num, $stack=0, $type="") {
		return NULL;
	}

	public function num_rows($stack=0) {
		return 0;
	}

	public function num_affected_rows() {
		return 0;
	}

	public function get_databases() {
		return array();
	}

	public function get_schemas() {
		$this->log_error(__CLASS__ . ': Schemas are not supported');
		return array();
	}

	public function get_tables() {
		return array();
	}

	public function get_views() {
		return array();
	}

	public function get_procedures() {
		return array();
	}

	public function get_functions() {
		return array();
	}

	public function get_triggers() {
		return array();
	}

	public function get_events() {
		return array();
	}

	public function truncate($tbl) {
		return $this->query('truncate table '.$this->quote($tbl));
	}

	public function drop($name, $type) {
		$result = false;
		$query = 'drop ' . $this->escape($type) . ' '.$this->quote($name);
		$result = $this->query($query);
		return $result;
	}

	public function escape($str) {
		return $str;
	}

	public function quote($str) {
		return $str;
	}

	public function get_limit($count, $offset = 0) {
		return " limit $offset, $count";
	}

	public function get_query_time() {
		return sprintf("%.2f", $this->queryTime * 1000) . " ms";
	}

	public function log_error($str) {
		$this->lastError = $str;
		error_log($str);
		return false;
	}

	public function get_time() {
		list($usec, $sec) = explode(" ",microtime());
		return ((float)$usec + (float)$sec);
	}

	public function get_last_query() {
		return $this->lastQuery;
	}

	public function get_last_error($str) {
		return $this->lastError;
	}
	
	public function expr($str) {
		return new Database_Expression($this->get_expr_string($str));
	}
	
	/* providing an invalid option will be simply ignored */
	public function set_option($k, $v) {
		if (isset($this->options[$k]))
			$this->options[$k] = $v;
	}
	
	/* this can be used to set multiple options in one call using an array of key/value pairs */
	public function set_options($arr) {
		foreach($arr as $k=>$v) {
			if (isset($this->options[$k]))
				$this->options[$k] = $v;
		}
	}
	
	protected function get_expr_string($str) {
		return $str;
	}
}
?>
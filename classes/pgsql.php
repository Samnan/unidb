<?php
/**
 * UniDB Driver Pgsql
 * @file:      pgsql.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

require_once( 'core.php' );
class Pgsql_Database extends Core_Database {

	public function __construct() {
		parent::__construct();
	}

	public function connect($ip, $user, $password, $db="") {
		if (!function_exists('pg_connect')) {
			return $this->log_error('PostgreSQL client library is not installed');
		}

		$conn_str = $this->build_conn_string($ip, $user, $password, $db);
		$this->conn = @pg_connect($conn_str);
		if (!$this->conn)
			return $this->log_error('Database connection failed to the server');

		$this->ip = $ip;
		$this->user = $user;
		$this->password = $password;
		$this->db = $db;

		return true;
	}

	public function disconnect() {
		@pg_close($this->conn);
		$this->conn = false;
		return true;
	}
	
	public function get_features() {
		$this->features['schemas'] = TRUE;
		$this->features['views'] = TRUE;
		$this->features['functions'] = TRUE;
		$this->features['procedures'] = TRUE;
		$this->features['triggers'] = TRUE;
		$this->features['sequences'] = TRUE;
		return $this->features;
	}

	public function select_db($db) {
		$this->db = $db;
		$conn_str = $this->build_conn_string($this->ip, $this->user, $this->password, $db);
		$this->conn = @pg_connect($conn_str);
		if (!$this->conn)
			return $this->log_error('Invalid database specified');

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

		$this->result[$stack] = @pg_query($this->conn, $sql);
		$this->queryTime = $this->get_time() - $this->queryTime;

		if ($this->result[$stack] === FALSE) {
			return $this->log_error( pg_errormessage($this->conn) );
		}

		return true;
	}

	public function get_insert_id() {
		return pg_getlastoid($this->result[$this->stack_last]);
	}

	public function fetch_row($stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = PGSQL_BOTH;
		else if ($type == "num")
			$type = PGSQL_NUM;
		else if ($type == "assoc")
			$type = PGSQL_ASSOC;

		if (!$this->result[$stack]) {
			return $this->log_error("fetch_row[$stack] failed");
		}
		return @pg_fetch_array($this->result[$stack], -1, $type);
	}

	public function fetch_row_num($num, $stack=0, $type="") {
		if($type == "")
			$type = $this->options['result_type'];
		if ($type == "both")
			$type = PGSQL_BOTH;
		else if ($type == "num")
			$type = PGSQL_NUM;
		else if ($type == "assoc")
			$type = PGSQL_ASSOC;

		if (!$this->result[$stack]) {
			return $this_log_error("fetch_row_num[$stack] failed");
		}

		return @pg_fetch_array($this->result[$stack], $num, $type);
	}

	public function num_rows($stack=0) {
		return pg_numrows($this->result[$stack]);
	}

	public function num_affected_rows() {
		return pg_affected_rows( $this->result[$this->stack_last] );
	}

	public function get_databases() {
		$res = pg_query($this->conn, "SELECT datname FROM pg_database WHERE NOT datistemplate ORDER BY datname");
		$ret = array();
		while($row = pg_fetch_array($res))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_schemas() {
		if (!$this->db)
			return array();
		$extra = "WHERE nspname NOT LIKE 'pg@_%' ESCAPE '@' AND nspname != 'information_schema'";
		$res = pg_query($this->conn, "SELECT pn.nspname, pu.rolname AS nspowner, pg_catalog.obj_description(pn.oid, 'pg_namespace') AS nspcomment FROM pg_catalog.pg_namespace pn LEFT JOIN pg_catalog.pg_roles pu ON (pn.nspowner = pu.oid) $extra ORDER BY nspname");
		$ret = array();
		while($row = pg_fetch_array($res))
			$ret[] = $row[0];
		return $ret;
	}

	public function get_tables() {
		if (!$this->db)
			return array();

		// exclude the standard db objects as they are a long list and usually not required
		$extra = "AND table_schema NOT LIKE 'pg@_%' ESCAPE '@' AND table_schema != 'information_schema'";
		$res = pg_query($this->conn, "SELECT table_schema, table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' $extra ORDER BY table_name");
		$ret = array();
		while($row = pg_fetch_array($res)) {
			$ret[] = $row[0] . '.' . $row[1];
		}

		return $ret;
	}

	public function get_views() {
		if (!$this->db)
			return array();
		$extra = "AND table_schema NOT LIKE 'pg@_%' ESCAPE '@' AND table_schema != 'information_schema'";
		$res = pg_query($this->conn, "SELECT table_schema, table_name FROM information_schema.tables WHERE table_schema = current_schema() and table_type = 'VIEW' $extra ORDER BY table_name");
		if (!$res)
			return array();
		$ret = array();
		while($row = pg_fetch_array($res)) {
			$ret[] = $row[0] . '.' . $row[1];
		}
		return $ret;
	}

	public function get_functions() {
		if (!$this->db)
			return array();
		$extra = "WHERE n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";
		$res = pg_query($this->conn, "SELECT n.nspname, p.proname AS name FROM pg_proc p INNER JOIN pg_namespace n ON p.pronamespace = n.oid LEFT OUTER JOIN pg_roles u ON u.oid = p.proowner $extra ORDER BY p.proname, n.nspname");
		if (!$res)
			return array();
		$ret = array();
		while($row = pg_fetch_array($res)) {
			$ret[] = $row[0] . '.' . $row[1];
		}
		return $ret;
	}

	public function get_triggers() {
		if (!$this->db)
			return array();
		$extra = "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";
		$res = pg_query($this->conn, "SELECT n.nspname, tgname FROM pg_trigger t INNER JOIN pg_class c ON t.tgrelid = c.oid INNER JOIN pg_namespace n ON c.relnamespace = n.oid WHERE t.tgisinternal = 'f' $extra ORDER BY t.tgname");
		if (!$res)
			return array();
		$ret = array();
		while($row = pg_fetch_array($res)) {
			$ret[] = $row[0] . '.' . $row[1];
		}
		return $ret;
	}

	public function get_sequences() {
		if (!$this->db)
			return array();
		$extra = "AND n.nspname NOT LIKE 'pg@_%' ESCAPE '@' AND n.nspname != 'information_schema'";
		$res = pg_query($this->conn, "SELECT n.nspname, c.relname AS name, ds.description, n.nspname, d.refobjid as owntab, u.rolname AS usename FROM pg_class c LEFT OUTER JOIN pg_roles u ON u.oid = c.relowner INNER JOIN pg_namespace n ON c.relnamespace = n.oid LEFT OUTER JOIN pg_depend d on c.relkind = 'S' and d.classid = c.tableoid and d.objid = c.oid and d.objsubid = 0 and d.refclassid = c.tableoid and d.deptype = 'i' LEFT OUTER JOIN pg_description ds ON c.oid = ds.objoid WHERE c.relkind = 'S' $extra ORDER BY c.relname");
		if (!$res)
			return array();
		$ret = array();
		while($row = pg_fetch_array($res)) {
			$ret[] = $row[0] . '.' . $row[1];
		}
		return $ret;
	}

	public function escape($str) {
		return "'".pg_escape_string($this->conn, $str)."'";
	}

	public function quote($str) {
		if(strpos($str, '.') === false)
			return '"' . $str . '"';
		return '"' . str_replace('.', '"."', $str) . '"';
	}

	public function get_limit($count, $offset = 0) {
		return " limit $count offset $offset";
	}

	// builds connection string for pgsql connect method from parameters
	protected function build_conn_string($ip, $user, $password, $db) {
		$host = $ip;
		$port = '';
		if (strpos($ip, ':') !== false) {
			list($host, $port) = explode(':', $ip);
		}
		$str = "host=" . pg_escape_string($host) . " user=" . pg_escape_string($user);
		$str .= " password=" . pg_escape_string($password);
		if( !empty($port) )
			$str .= " port=" . pg_escape_string($port);
		if( !empty($db) )
			$str .= " dbname=" . pg_escape_string($db);

		return $str;
	}
	
	protected function get_expr_string($str) {
		switch($str) {
			case 'datetime':
				return 'NOW()';
			case 'date':
				return 'current_date';
			case 'time':
				return 'current_time';
		}
		
		return $str;
	}

}
?>
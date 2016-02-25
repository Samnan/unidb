<?php
/**
 * UniDB PDO Pgsql Driver
 * @file:      pdo/pgsql.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

class Pgsql_Database_PDO extends Pgsql_Database {
	use UniDB_PDO;
	protected $pdo_identifier = 'pgsql';
	
	public function select_db($db) {
		return $this->connect($this->ip, $this->user, $this->password, $db);
	}
}

?>
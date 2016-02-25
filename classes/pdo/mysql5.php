<?php
/**
 * UniDB PDO Mysql5 Driver
 * @file:      pdo/mysql5.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

class Mysql5_Database_PDO extends Mysql5_Database {
	use UniDB_PDO;
	protected $pdo_identifier = 'mysql';
	
	public function select_db($db) {
		$this->db = $db;
		return $this->query('USE ' . $this->quote($this->db));
	}
}

?>
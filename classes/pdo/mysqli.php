<?
/**
 * UniDB PDO Mysqli Driver
 * @file:      pdo/mysqli.php
 * @package:   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

class Mysqli_Database_PDO extends Mysqli_Database {
	use UniDB_PDO;
	protected $pdo_identifier = 'mysql';
	
	public function select_db($db) {
		$this->db = $db;
		return $this->query('USE ' . $this->quote($this->db));
	}
}

?>
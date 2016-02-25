<?php
/**
 * UniDB Main CLass
 * @file:      unidb.php
 * @package   UniDB - MySQL,PostgreSQL, SQLite
 * @author     Samnan ur Rehman
 * 
 
 *
 * @license    GNU GPLv3
 */

class UniDB {
	private $driver;

	function __construct( $driver, $interface = NULL ) {
		require_once( dirname(__FILE__) . '/' . $driver . '.php' );
		$class = ucfirst($driver) . '_Database';
		if ( strcasecmp($interface, 'pdo') == 0 ) {
			if( version_compare(PHP_VERSION, '5.4', '>=')  ) {
				require_once( 'pdo/pdo.php' );
				require_once( 'pdo/'.$driver.'.php' );
				$class .= '_PDO';
			} else {
				die('UniDB PDO requires PHP 5.4 or above');
			}
		}
		$this->driver = new $class;
	}

	public function __call($name, $arguments) {
		return call_user_func_array( array($this->driver, $name), $arguments );
	}

}
?>
<?php
/**
 * DB Objects Demo config
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

 	/*
	 * please update the configuration below as per your requirements
	 */

 	// the database type used for demo. change to test with database type
 	$driver = 'sqlite3';

 	// the name of database to be used for demo
	$database = 'test';

	$config = array(
		'mysql4' => array(
			'host' => 'localhost', 'user' => 'root' , 'pass' => 'root'
		),
		'mysql5' => array(
			'host' => 'localhost', 'user' => 'root' , 'pass' => 'root'
		),
		'mysqli' => array(
			'host' => 'localhost', 'user' => 'root' , 'pass' => 'root'
		),
		'pgsql' => array(
			'host' => 'localhost', 'user' => 'postgres' , 'pass' => 'postgres'
		),
		'sqlite' => array(
			'host' => dirname(__FILE__) . '/sqlite', 'user' => '' , 'pass' => '', 'file_ext' => '.db'
		),
		'sqlite3' => array(
			'host' => dirname(__FILE__) . '/sqlite', 'user' => '' , 'pass' => '', 'file_ext' => '.sqlite3'
		)
	);
?>
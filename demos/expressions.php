<?php
/**
 * DB raw expressions demo
 * Explains how to use current datetime or other dynamic expressions in insert/update queries
 *
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

 	include( './config.php' );  // this is only required for demo

	include_once( '../classes/unidb.php' );

	echo '<p>Using ' . $driver . ' driver</p>';
	// create new UniDB object, providing the type of driver as argument (defined in config)
	$db = new UniDB( $driver );

	// get the configuration for this database from the config array
	$info = $config[ $driver ];
	
	// this is not required, but can be optionally used for SQLite databases only
	if (isset($info['file_ext'])) {
		$db->set_option('file_ext', $info['file_ext']);	
	}

	// connect to the database and perform some tasks
	// as a good practice, always check for connection status before performing other tasks
	if ( !$db->connect( $info['host'], $info['user'], $info['pass'] ) ) {
		die('Database connection failed.
			Please check that the require client libraries are properly installed
			and the connection configuration is correct before running the demo');
	}

	$db->select_db( $database );

	// example select query with expression
	$sql = "SELECT " . $db->expr('COUNT(*)') . " from test_table";
	// use the sql as you like

	// example of using expression in insert method
	$data = array(
		'id' => NULL,
		'name' => 'John Doe',
		'dept_id' => 10,
		'the_time' => $db->expr('datetime'),  // see the docs for list of supported expressions
		'info' => 'Some demo text'
	);

	// run a basic crud operation on a table

	// insert a record
	if ( !$db->insert( 'test_table', $data ) ) {
		echo '<p>Query failed: '. $db->get_last_query() . '</p>';
		echo '<p>Check the error log for more details</p>';
	}
	
	$db->disconnect();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>UniDB Demo</title>
<style type="text/css">
body {
	font-family: verdana,arial,sans-serif;
	font-size:14px;
	color:#333333;
	border-width: 1px;
	border-color: #666666;
	border-collapse: collapse;
}
</style>
</head>
<body>
	<p>Please see the code for this file to find out how easy it is to use UniDB class in your applications.</p>
</body>
</html>
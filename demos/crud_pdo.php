<?php
/**
 * DB Objects Demo using PDO driver
 * @author     Samnan ur Rehman
 * 
 

 * @license    GNU GPLv3
 */

 	include( './config.php' );  // this is only required for demo

	include_once( '../classes/unidb.php' );

	echo '<p>Using ' . $driver . ' driver with PDO interface</p>';
	// create new UniDB object, providing the type of driver as argument (defined in config)
	// the second parameter tells UniDB to use PDO drivers
	$db = new UniDB( $driver, 'pdo' );

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

	$data = array(
		'id' => NULL,
		'name' => 'John Doe',
		'dept_id' => 10,
		'info' => 'Some demo text'
	);

	// run a basic crud operation on a table

	// insert a record
	if ( !$db->insert( 'test_table', $data ) ) {
		echo '<p>Query failed: '. $db->get_last_query() . '</p>';
		echo '<p>Check the error log for more details</p>';
	}

	// THIS WILL NOT WORK WITH PGSQL PDO
	$id = $db->get_insert_id();

	// unset id otherwise the update query will try to set it to NULL
	unset($data['id']);
	$data['dept_id'] = 20;

	// update a record
	if ( !$db->update( 'test_table', $data, 'WHERE id=' . $id ) ) {
		echo '<p>Query failed: '. $db->get_last_query() . '</p>';
		echo '<p>Check the error log for more details</p>';
	}

	//delete a record
	//if ( !$db->delete( 'test_table', 'WHERE id=' . $id ) ) {
	//	echo '<p>Query failed: '. $db->get_last_query() . '</p>';
	//	echo '<p>Check the error log for more details</p>';
	//}

	// insert another record, testing the escape functionality of database
	$data['name'] = "John' Doe";
	if ( !$db->insert( 'test_table', $data ) ) {
		echo '<p>Query failed: '. $db->get_last_query() . '</p>';
		echo '<p>Check the error log for more details</p>';
	}
	
	// generate a simple SQL statement
	$sql = "SELECT * from test_table WHERE name=" . $db->escape("John Doe") . $db->get_limit(5);

	if ( !$db->query( $sql ) ) {
		echo '<p>Query failed: '. $db->get_last_query() . '</p>';
		echo '<p>Check the error log for more details</p>';
	} else {
		echo 'Number of records for John Doe: ' . $db->num_rows() . ' (THIS IS DUE TO PDO driver limits, not a bug)<br>';
		$row = $db->fetch_row();
		echo '<br>ID of John\' Doe in the table is: ' . $row['id'] . '<br>';
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
	<p>Please see the code for this file to find out how to use the PDO drivers with UniDB.</p>
</body>
</html>
basic.php
-----------
	simplest demo for the UniDB class, shows how to connect and disconnect a server/database, do a basic query etc.

database.php
------------
	shows getting the list of databases on server, getting list of table etc

crud.php
--------
	shows how you can do insert/update/delte on a table using UniDB objects

query.php
---------
	shows how to do a simple query and iterate over the results

multi_query.php
---------------
	shows how to do multiple queries in loop using the UniDB object

sqlite3.php
-----------
	shows how to configure SQLite database filename extension before using it. By default '.db' is assumed if not configured


Demo Requirements
=======================================
To properly see the demo working, please create a table called 'test_table' inside your database, with the following structure:
[
	id int autoincrement primary key
	name varchar
	dept_id int
	the_time datetime
	info text
]

The SQLite database provided in the demo/sqlite folder already contains this table. Since SQLite is a 'free form' database, you can use text columns
instead of varchar and date time fields.

If you want to use a database other than 'test', please change the name of the database in 'config.php' first.
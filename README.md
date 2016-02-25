UniDB (MySQL, PostgreSQL, SQLite)
=======================================
PHP database object library with unified codebase for using MySQL, PostgreSQL and SQLite databases

License:  GNU GPLv3


Description
===========
UniDB is an object library that provides a unified codebase for connecting to and using either MySQL, PostgreSQL
or SQLite databases. Without knowing any internals of specifics, you can use the same code to talk to any of these databases.
Changing the underlying database in your application will require no change in your code at all, if you are using the classes provided
in this package. You can also use more than one type of databases in your application using UniDB objects.

Using UniDB, you can get the list of supported object types in a database, like views, trigger and iterate over the list of objects.
The CRUD mechanism in UniDB is extremely simple and elegant, and avoids the data from being polluted with external
script variables as it is contained within the database object. Apart from basic CRUD functionality, UniDB allows a simpler and elegant approach
to work with multiple queries and results set within your PHP application.


Requirements
============
- PHP 5.4 or above
one of more of the following databases/servers
- MySQL server ( version 4 or 5 )
- PostgreSQL ( version 9 )
- SQLite databases ( a sample db is provided in the package )


Usage
===========
Include the desired class file in your php code, create an object and use the object as per your requirements.
See demos for sample code.

Please also make sure specify the correct host, username and password in demo folder config.php file before running the demos.
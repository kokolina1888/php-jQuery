<?php 

class DB_Connect{

	/* 
* Stores a database object
*
* @var object A database object
*/

protected $db;

/* 
* Checks for a DB object or creates one if one isn`t found
*
* @param object $db A database object 

*/

protected function __construct($db=NULL)
{
	if(is_object($db))
	{
		$this->db=$db;
	}
	else 
	{
		// constants are defined in /sys/config/db-cred.inc.php
		$dsn = 'mysql:host=' . DB_HOST . "; dbname=" . DB_NAME;
		try
		{
			$this->db = new PDO($dsn, DB_USER, DB_PASS);
		}
		catch(Exception $e)
		{
			//If the DB connection fails output the error

			die( $e->getMessage() );
		}
	}
}
}



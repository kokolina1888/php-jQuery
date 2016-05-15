<?php 

//declare(strict_types=1);

/*
* Include the necessary configuration info
*/
///sys/config/db-cred.inc.php
// include_once '../sys/config/db-cred.inc.php';

/*
* define constants for configuration info
*/

// foreach ($C as $name => $val) {
// 	define($name, $val);
// }
/*
* enable session if needed
* avoid pesky warning if session already active
*/

$status = session_status();
if ($status == PHP_SESSION_NONE) {
    //there is no active session
    session_start();
}

/*
* Generate an antiCSRF token if one doesn`t exist
*/

if (!isset($_SESSION['token'])) {
    $_SESSION['token'] = sha1(uniqid((string)mt_rand(), TRUE));
}

/*
* Create PDO object
*/
$servername = "localhost";
$username = "root";
$password = "";
$db = "php-jquery_example";
$dsn = 'mysql:host='.$servername.';dbname='.$db;
//$dbo = new PDO($dsn, DB_USER, DB_PASS);
try {
    $dbo = new PDO("mysql:host=$servername;dbname=$db", $username, $password);
    // set the PDO error mode to exception
    $dbo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }

/*
* Define the auto-load function for classes
*/

function __autoload($class)
{
	$filename = '../sys/class/class.'.$class.'.inc.php';
	if(file_exists($filename))
	{
		include_once($filename);
	}
}

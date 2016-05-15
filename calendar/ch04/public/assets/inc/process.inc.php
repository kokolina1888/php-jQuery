<?php 

/*
* enable sessions if needed
* avoid pesky warning if session already active
*/
$status = session_status();
if ($status == PHP_SESSION_NONE) {
	//there is no active session
	session_start();
}
/*
* include necessary files
*/

include_once('../../../sys/config/db-cred.inc.php');

/*
* define constants for config info
*/

foreach ($C as $name => $val) {
	define($name, $val);
}

/*
* create lookup array for form actions
*/
$actions = array();
$actions['event_edit']['object'] = 'Calendar';
$actions['event_edit']['method'] = 'processForm';
$actions['event_edit']['header'] = 'Location: ../../';

$actions['user_login']['object'] = 'Admin';
$actions['user_login']['method'] = 'processLoginForm';
$actions['user_login']['header'] = 'Location: ../../';

$actions['user_logout']['object'] = 'Admin';
$actions['user_logout']['method'] = 'processLogout';
$actions['user_logout']['header'] = 'Location: ../../';

// define('ACTIONS', array(
// 	'event_edit' => array(
// 		'object' => 'Calendar',
// 		'method' => 'processForm',
// 		'header' => 'Location: ../../')));

/*
* Need a PDO object
*/

$dsn = "mysql:host=".DB_HOST."; dbname=".DB_NAME;
$dbo = new PDO($dsn, DB_USER, DB_PASS);

/*
* make sure the anti-CSRF token was passed and that the requested
* action exists in the lookup array
*/

if ($_POST['token'] == $_SESSION['token'] && (null !== $actions[$_POST['action']]))
{
 $use_array = $actions[$_POST['action']];
 $obj 		= new $use_array['object']($dbo);
 $method 	= $use_array['method'];

 if (TRUE === $msg=$obj->$method()) 
 {
 header($use_array['header']);
 exit;
 }
 else 
 {
 	//if an error occured, output it and end execution

 	die($msg);
 }
}
else
{
	//redirect to the main index if the token/action is invalid

	header("Location: ../../");
	exit;
}

	function __autoload($class_name)
	{
		$filename = '../../../sys/class/class.'.strtolower($class_name).'.inc.php';
		if(file_exists($filename))
		{
			include_once($filename);
		}
	}

<?php 

/*
* Enable sessions if needed
* Avoid pesky warning if session already active
*/

$status = session_status();
if($status == PHP_SESSION_NONE)
{
	//there is no active session
	session_start();
}

/*
* Include necessary files
*/

include_once '../../../sys/config/db-cred.inc.php';
/*
* define constants for config info
*/
foreach ($C as $name => $val) {
	define($name, $val);
}
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
    }
catch(PDOException $e)
    {
    echo "Connection failed: " . $e->getMessage();
    }

/*
* define array for form actions
*/

$actions = array();
$actions['event_view']['object'] = 'Calendar';
$actions['event_view']['method'] = 'displayEvent';

$actions['edit_event']['object'] = 'Calendar';
$actions['edit_event']['method'] = 'displayForm';

$actions['event_edit']['object'] = 'Calendar';
$actions['event_edit']['method'] = 'processForm';

$actions['delete_event']['object'] = 'Calendar';
$actions['delete_event']['method'] = 'confirmDelete';

$actions['confirm_delete']['object'] = 'Calendar';
$actions['confirm_delete']['method'] = 'confirmDelete';

/*
* make sure that the anti-CRF token was passed and that the
* requested actions exists in the lookup array
*/

if (isset($actions[$_POST['action']])) 
{
 $use_array = $actions[$_POST['action']];
 $obj = new $use_array['object']($dbo);
 $method = $use_array['method'];

 /*
 * check for an ID and sanitise it if found
 */
 if (isset($_POST['event_id'])) 
 {
 $id = (int)$_POST['event_id'];
 }
 else { $id = NULL; }
 echo $obj->$method($id);


}
function __autoload($class_name)
	{
		$filename = '../../../sys/class/class.'.strtolower($class_name).'.inc.php';
		if(file_exists($filename))
		{
			include_once($filename);
		}
	}
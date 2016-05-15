<?php
/*
* make sure the event ID was passed
*/

if (isset($_GET['event_id'])) 
{
/*
* Make surethe ID is an integer
*/

$id = preg_replace('/[^0-9]/', '', $_GET['event_id']);

/*
* If the ID is not valid, send dthe user to the main page
*/

if (empty($id)) 
{
	header("Location:./");
	exit;
}
}
else 
{
	/*
	* send the user to the main page if no ID is supplied
	*/
	header("Location:./");
	exit;
}
/*
* Output the header
*/
$page_title = "View event";
$css_files = array('style.css', 'admin.css');
include_once('assets/common/header.inc.php');

?>
<div id="content">
	<?php 
	$cal = new Calendar($dbo);
	echo $cal->displayEvent($id); 
	?>
	<a href="./">&laquo; Back to the calendar</a>
</div>
<!-- end #conent -->

<?php 
/*
* output the footer
*/

include_once('assets/common/footer.inc.php');
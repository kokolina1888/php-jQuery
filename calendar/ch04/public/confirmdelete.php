<?php
/*
* Make sure the event ID was passed
*/

if(isset($_POST['event_id']))
{
	/*
	* Collect the event ID from the URL string
	*/

	$id = (int)$_POST['event_id'];
}
else 
{
	/*
	* Send the user to the main page if no ID is supplied
	*/

	header("Location: ./");
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
	/*
	* Load the calendar
	*/
	$cal 	= new Calendar($dbo);
	$markup = $cal->confirmDelete($id);
	echo $markup;
	?>
</div>
<!-- end #conent -->
<?php 
/*
* Output the footer
*/
include_once('assets/common/footer.inc.php');
?>

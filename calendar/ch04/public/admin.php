<?php 
/*
* output the header
*/
$page_title = "Add/Edit Event";
$css_files 	= array("style.css", "admin.css");
include_once('assets/common/header.inc.php');
if (!isset($_SESSION['user'])) {
	header("Location: ./");
	exit;
}
/*
* Load the calendar
*/

?>

<div id="content">
	<?php 
	$cal = new Calendar($dbo);
	echo $cal->displayForm();
	?>
</div>
<!-- end #content -->

<?php 

/*
* output the footer
*/
include_once('assets/common/footer.inc.php');
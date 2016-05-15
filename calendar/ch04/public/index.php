<?php 
$page_title = "Events Calendar";
$css_files = array('style.css', 'admin.css', 'ajax.css');
/*
* Include the header
*/
include_once('assets/common/header.inc.php');
?>
<div id="content">

<?php

/*
* display the calendar HTML
*/
$cal = new Calendar($dbo, '2016-01-01 00:01:00');

echo $cal->buildCalendar();

?>
</div>
<!-- end #content -->
<p>
	<?php 

	echo isset($_SESSION['user']) ? "Logged In" : "Logged Out!";
	
	?>
</p>
<?php 
/*
* Include the footer
*/
include_once('assets/common/footer.inc.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo $page_title;?></title>
	<?php foreach ($css_files as $css){
		echo '<link rel="stylesheet" type="text/css" media="screen, projection" href="assets/css/'.$css .'">';
	}
?>
</head>
<body>
<?php 
include_once('../sys/core/init.inc.php');
?>

	

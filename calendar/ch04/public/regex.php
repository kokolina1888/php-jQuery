<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>RegEx Demo</title>
	<style type="text/css">
	em {
		background-color: #FF0;
		border-top: 1px solid #000;
		border-bottom: 1px solid #000;
	}
	</style>
</head>
<body>
<?php
/*
*Store the sample set of text to use for the example of regex
*/ 
$date[] = '2016-01-14 12:00:00';
$date[] = 'Saturday, May 14th at 7pm';
$date[] = '02/03/10 10:00pm';
$date[] = '2016-11-14 102:00:00';
$pattern = "/^(\d{4}(-\d{2}){2} (\d{2}:){2}(\d{2}))$/i";

foreach ($date as $d) {
	echo '<p>', preg_replace($pattern, "<em>$1</em>", $d), '</p>';
}
?>	
</body>
</html>
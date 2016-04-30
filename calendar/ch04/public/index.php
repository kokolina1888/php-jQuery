<?php 
//declare(strict_types=1);

/*
* Include nessesary files
*/

include_once('../sys/core/init.inc.php');

/*
* Load the calendar for January
*/

$cal = new Calendar($dbo, '2016-01-01 12:00:00');

/*
* display the calendar HTML
*/

echo $cal->buildCalendar();
<?php 
$page_title = "tester";
$css_files = array('style.css', 'admin.css');
include_once('assets/common/header.inc.php');
//load the test admin object

$obj = new Admin($dbo);

//generate a salted hash of "admin"
$pass = $obj->testSaltedHash("admin");
echo "Hash of 'admin': <br>", $pass, "<br><br>";


//load a hash of the word test and output it
$hash1 = $obj->testSaltedHash("test");
echo "Hash 1 without a salt: <br>", $hash1, "<br><br>";

//pause execution for a second to get a different timestamp
sleep(1);

//load a second hash of the word test
$hash2 = $obj->testSaltedHash("test");
echo "Hash 2 without a salt: <br>", $hash2, "<br><br>";

//pause execution for a second to get a different timestamp
sleep(1);

//rehash the word test with the existing salt
$hash3= $obj->testSaltedHash("test", $hash2);
echo "Hash 3 without the salt from hash2: <br>", $hash3;

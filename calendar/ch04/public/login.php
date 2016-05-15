<?php 
/*
* Output the header
*/
$page_title = "Please, Log In";
$css_files = array('style.css', 'admin.css');
include_once('assets/common/header.inc.php');
?>
<div id="content">
	<form action="assets/inc/process.inc.php" method="post">
		<fieldset>
			<legend>Please Log In</legend>
			<label for="uname">Username</label>
			<input type="text" name="uname" value="" id="name">
			<label for="pword">Password</label>
			<input type="text" name="pword" value="" id="pword">
			<label for="uname">Username</label>
			<input type="hidden" name="action" value="user_login" id="user_login">
			<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>" id="user_login">
			<input type="submit" name="login_submit" value="Log In">
			or <a href="./">cancel</a>
		</fieldset>
	</form>
</div>
</div>
<!-- end #content -->
<?php 
/*
* output the footer
*/

include_once('assets/common/footer.inc.php');
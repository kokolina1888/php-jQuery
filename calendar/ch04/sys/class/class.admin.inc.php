<?php 

/**
* manages administrative actions
*
*/

class Admin extends DB_Connect
{
	
	/**
	* determines length of the salt to use in hashed passwords
	*
	* @var int the length of the password salt to use
	*/

	private $_saltLength = 7;

	/**
	* Stores or creates a DB object and sets the salt length
	*
	* @param object $db a database object
	* @param int $saltLength length for the password hash
	*/

	public function __construct($db=NULL, $saltLength=NULL)
	{
		parent::__construct($db);
		/*
		* if an int was passed, set the lenth of the salt
		*/

		if (is_int($saltLength)) 
		{
			$this->_saltLength = $saltLength;
		}
	}
	/**
	* checks log in credentials for a valid user
	*
	* @param mixed true on success, message on error
	*/

	public function processLoginForm()
	{
		/*
		* fails if the proper action was not submitted
		*/

		if ($_POST['action'] != 'user_login') 
		{
		 return "Invalid action supplied for processLoginForm";
		}
		/*
		* escapes the user input for security
		*/
		$uname = htmlentities($_POST['uname'], ENT_QUOTES);
		$pword = htmlentities($_POST['pword'], ENT_QUOTES);
echo $uname, $pword;
		/*
		* retrieves the matching info from the DB if it exists
		*/

		$sql = "SELECT * FROM users
				WHERE user_name = :uname LIMIT 1";
		try
		{
			$stmt = $this->db->prepare($sql);
			$stmt->bindParam(':uname', $uname, PDO::PARAM_STR);
			$stmt->execute();
			$user = array_shift($stmt->fetchALL());
			$stmt->closeCursor();
		}
		catch( Exception $e)
		{
			die ( $e->getMessage());
		}

		/*
		* fails if username doesn`t match a DB entry
		*/

		if (!isset($user)) 
		{
		return "Your username or password is invalid";
		}
		/*
		* get the hash of the user-supplied password
		*/

		$hash = $this->_getSaltedHash($pword, $user['user_pass']);

		/*
		* checks if the hashed password matches the stored hash
		*/


		if ( $user['user_pass'] == $hash) 
		{
		/*
		* stores user info in the session as an array
		*/

		$_SESSION['user'] = array(
			'id' 	=>$user['user_id'],
			'name' 	=>$user['user_name'],
			'email'	=>$user['user_email']);
		return TRUE;

		}
		/*
		* fails if the password don`t match
		*/
		else 
		{

			return "Your username or password is invalid.";
		}

		//finish processing ...
	}

	public function processLogout()
	{
		/*
		* Fails if the proper action was not submitted
		*/
		if ($_POST['action'] != 'user_logout') 
		{
		return "Invalid action supplied";
		}
		/*
		* Removes the user array from the current session
		*/
		session_destroy();
		return TRUE;

	}//end of processLogout
	/**
	* generates a salted hash of a supplied string
	* @param string $string string to be hashed
	* @param string $salt extracts the hash from here
	* @return string salted hash
	*/

	private function _getSaltedHash($string, $salt=NULL)
	{
		/*
		* gemerate a salt if no salt is passed
		*/

		if($salt==NULL)
		{
			$salt = substr(md5((string)time()), 0, $this->_saltLength);
		}
		/*
		* extract the salt from the string
		*/
		else 
		{
			$salt = substr($salt, 0, $this->_saltLength);

		}
		/*
		* Add the salt to the hash and return it
		*/

		return $salt . sha1($salt.$string);
	}

	public function testSaltedHash($string, $salt=NULL)
	{
		return $this->_getSaltedHash($string, $salt);
	}
}
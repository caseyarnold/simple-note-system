<?php
/* @name 	models.php
 * @author 	Casey Arnold
 * @created 21 March 2017 10:37
 * @updated 22 March 2017 14:07
 * @descr 	This file stores the two models used for database interaction, one for the users 
 * table and another for the notes
 */
class Note 
{
	private $note;
	private $user_id;

	/**
	 * __construct($note)
	 *
	 * takes the user array and stores it internally
	 *
	 * @param array $note an array which contains a note
	 * @return void
	 */
	public function __construct($note)
	{
		$this->note = $note;
	}

	/**
	 * static get_all_notes($user_id) 
	 *
	 * returns the all notes posted by the specified user
	 *
	 * @param int $user_id the user id that will be used for the note selection
	 * @return mysqli object
	 */
	public static function get_all_notes($user_id) 
	{
		global $mysqli;

		return $mysqli->query("SELECT * FROM `notes` WHERE `user_id` = '".$user_id."'");
	}

	/**
	 * set_user_id($user_id)
	 *
	 * stores the user_id into the class, used for note creation
	 *
	 * @param int $user_id the user id that will be stored within the class
	 * @return mysqli object
	 */
	public function set_user_id($user_id)
	{
		$this->user_id = $user_id;
	}

	/**
	 * create_note()
	 *
	 * creates the note once ensuring that the proper data has been supplied 
	 * and returns a boolean
	 *
	 * @return boolean
	 */
	public function create_note()
	{
		global $mysqli;

		if(isset($this->note['title']) && isset($this->note['content']) && isset($this->user_id))
		{

			$query = $mysqli->query("INSERT INTO `notes` (`title`, `content`, `user_id`) VALUES ('".$this->note['title']."', '".$this->note['content']."', ".$this->user_id.");");

			return true;
		}
		else
		{
			return false;
		}	
	}
}

class User 
{
	private $user;

	/**
	 * __construct($user)
	 *
	 * takes the user array and stores it internally
	 *
	 * @param array $user an array which contains a user's information
	 * @return void
	 */
	public function __construct($user)
	{
		$this->user = $user;

		if(!isset($this->user['username']) || !isset($this->user['password']))
			die('You did not supply a valid user object.');
	}

	/**
	 * static login_required()
	 *
	 * redirects the user if they aren't logged in, saves the url to redirect
	 * the user to the desired page once they have logged in
	 *
	 * @return void
	 */
	public static function login_required()
	{
		if(!User::is_logged_in()) 
		{
			$_SESSION['return_uri'] = $_SERVER['REQUEST_URI'];

			header("Location: index.php?page=login&msg=You+must+login+to+view+this+page");
		}
	}

	/**
	 * try_login()
	 *
	 * attempts to log the user in, first ensuring the xss key is proper,
	 * if all is successful, it sets a user hash to identify the user
	 * on other pages as well as session information and redirects the user
	 *
	 * @return boolean
	 */
	public function try_login()
	{
		global $mysqli;

		$data = $mysqli->query("SELECT * FROM `users` 
			WHERE `username` = '".$this->user['username']."' AND `password` = '".sha1($this->user['password'])."'");
 
		if($data->num_rows != 0)
		{
			// update user hash 
			$user_hash = User::generate_user_hash($this->user);
			$mysqli->query("UPDATE `users` SET `user_hash` = '".$user_hash."' 
				WHERE `username` = '".$this->user['username']."'");

			session_start();

			$_SESSION['user_hash'] = $user_hash;
			return true;
		}
		else
		{
			$this->login_errors[] = "You entered an incorrect password or username combination.";
			
			return false;
		}

	}

	/**
	 * static generate_user_hash($user)
	 *
	 * generates a unique user hash each time using the username,
	 * a randomly generated number and a static key
	 *
	 * @return void
	 */
	public static function generate_user_hash($user)
	{
		return sha1($user['username'] . '43A#Q21krL$O5' . rand(100000, 99999999));
	}

	/**
	 * static user($field)
	 *
	 * returns the requested field for the currently active user, see get_user_data
	 * to get by user_id
	 *
	 * @param string $field default to username, corresponds to the field we wish to 
	 * retrieve from the database for the current user
	 * @return mixed requested database field
	 */
	public static function user($field = 'username')
	{
		global $mysqli;

		$query = $mysqli->query("SELECT * FROM `users` WHERE `user_hash` = '".$_SESSION['user_hash']."'")->fetch_object();

		return $query->$field;
	}

	/**
	 * static get_user_data($user_id, $field)
	 *
	 * returns the requested field for the currently the supplied user_id
	 *
	 * @param int $user_id user_id which we will use to retrieve field 
	 * @param string $field default to username, corresponds to the field we wish to 
	 * retrieve from the database for the current user
	 * @return mixed requested database field
	 */
	public static function get_user_data($user_id, $field)
	{
		global $mysqli;

		$query = $mysqli->query("SELECT `".$field."` FROM `users` WHERE `id` = '".$user_id."'")->fetch_object();

		return $query->$field;
	}
	
	/**
	 * static get_user_data($user_id, $field)
	 *
	 * returns true or false depending on whether the user key is valid and
	 * a session exists
	 *
	 * @return boolean
	 */
	public static function is_logged_in()
	{
		global $mysqli;

		/* a user could have an empty user_hash and be "logged in" as someone with a  field
		in the database without this line */
		if(!isset($_SESSION['user_hash'])) return false;

		$data = $mysqli->query("SELECT `user_hash` FROM `users` WHERE `user_hash` = '".$_SESSION['user_hash']."'");

		if($data->num_rows != 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
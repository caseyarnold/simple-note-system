<?php
/* @name 	controllers.php
 * @author 	Casey Arnold
 * @created 21 March 2017 10:37
 * @updated 22 March 2017 14:06
 * @descr 	This is the controller file
 */
class Controller 
{
	/**
	 * render($page)
	 *
	 * includes the file specified as well as the 
	 * header and footer, helpful as it ensures
	 * all output is the last thing to be rendered
	 *
	 * @param string $page the path to the file to be included
	 * @return void
	 */
	public static function render($page)
	{
		include_once ('html/header.html');
		include_once ($page);
		include_once ('html/footer.html');
	}

	/**
	 * home()
	 *
	 * includes the home file
	 *
	 * @return void
	 */
	public function home()
	{
		Controller::render('html/home.html');
	}

	/**
	 * login_post()
	 *
	 * processes the post data submitted by the login
	 * form
	 *
	 * @return void
	 */
	public function login_post()
	{
		$user['username'] = $_POST['username'];
		$user['password'] = $_POST['password'];
		$xss = $_POST['xss'];

		if(!$xss || $xss != $_SESSION['xss'])
		{
			die('You must have a valid XSS string in the form to proceed');		
		} 

		unset($_SESSION['xss']);
		$user = new User($user);

		if($user->try_login())
		{
		 	if(isset($_SESSION['return_uri']))
		 	{
		 		$uri = $_SESSION['return_uri'];
		 		unset($_SESSION['return_uri']);
		 		header("Location: ".$uri);
		 	}
		 	else
		 	{
		 	 	header('Location: index.php');
		 	}
		}
		else
		{
			session_destroy();
			header('Location: index.php?page=login');
		}
	}
	
	/**
	 * login()
	 *
	 * displays the login form and generates the xss key
	 *
	 * @return void
	 */
	public function login()
	{
		$_SESSION['xss'] = sha1(rand(1000000,100000000));

		Controller::render('html/login_form.html');
	}

	/**
	 * logout()
	 *
	 * logs the user out, redirects them
	 *
	 * @return void
	 */
	public function logout()
	{
		session_destroy();

		header('Location: index.php');
	}

	/**
	 * view_notes()
	 *
	 * gets the object of all notes created by the user
	 * then renders them
	 *
	 * @return void
	 */
	public function view_notes() 
	{
		$_SESSION['notes'] = Note::get_all_notes(User::user('id'));

		Controller::render('html/view_notes.html');
	}

	/**
	 * add_note()
	 *
	 * generates as xss key and renders the add note
	 * form
	 *
	 * @return void
	 */
	public function add_note()
	{
		$_SESSION['xss'] = sha1(rand(1000000,100000000));

		Controller::render('html/add_note_form.html');
	}

	/**
	 * add_note_post()
	 *
	 * processes the post data submitted by the add note
	 * form
	 *
	 * @return void
	 */
	public function add_note_post()
	{
		$note['title'] = $_POST['title'];
		$note['content'] = $_POST['content'];
		$xss = $_POST['xss'];

		if(!$xss || $xss != $_SESSION['xss'])
		{
			die('You must have a valid XSS string in the form to proceed');		
		} 

		unset($_SESSION['xss']);
		$note = new Note($note);
		$note->set_user_id(User::user('id'));

		if($note->create_note())
		{
		 	header('Location: index.php?page=view');
		}
		else
		{
			header('Location: index.php?page=add_note');
		}
	}
}

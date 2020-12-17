<?php
/* @name 	index.php
 * @author 	Casey Arnold
 * @created 21 March 2017 10:37
 * @updated 22 March 2017 14:06
 * @descr 	This file includes all the rest and acts as a "router" for the application
 */
require_once('defines.php');
require_once('models.php');
require_once('controllers.php');

$page = $_GET['page'];

// check to ensure the user is logged in before 

$c = new Controller;

if($page == "index" || !$page)
{	
	$c->home();
}
elseif($page == "login") 
{	
	if ($_POST)
	{
		$c->login_post();
	}
	else
	{
		$c->login();
	}

}
elseif($page == "view") 
{
	User::login_required();

	$c->view_notes();
}
elseif($page == "logout")
{		
	User::login_required();

	$c->logout();
}
elseif($page == "add_note")
{
	User::login_required(); 

	if($_POST)
	{
		$c->add_note_post();
	}
	else
	{
		$c->add_note();
	}
}
else
{
	// "404" page, redirecting is easier

	header("Location: index.php");
}

// end the mysqli link
$mysqli->close();
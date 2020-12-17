<?php
/* @name 	defines.php
 * @author 	Casey Arnold
 * @created 21 March 2017 10:37
 * @updated 22 March 2017 14:06
 * @descr 	A file to store defines used in the rest of the application, also starts the session
 * as well as establishes a connection to the database 
 */
if(!isset($_SESSION)) session_start();

$mysqli = @new mysqli('127.0.0.1', 'note_user', 'REDACTED', 'note_db');

if($mysqli->connect_error)
{
    die('Connect Error (' . $mysqli->connect_errno . ') '
            . $mysqli->connect_error);
}

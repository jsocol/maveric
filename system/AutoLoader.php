<?php
/**
 * Class AutoLoading
 *
 * Classes are dynamically loaded as needed to minimize
 * overhead. Classes must follow the following naming 
 * and location conventions:
 *
 * Controllers:
 *   Controllers have names like DefaultController or
 *   UserController. The class name must match the 
 *   file name, minus the file extension (usually .php).
 *   The first letter of the controller class and file 
 *   and the first letter of "Controller" must be 
 *   capitalized, and no other letters may be.
 *   Controllers must extend ApplicationController and
 *   be kept in the /controllers/ directory.
 *
 * Helpers:
 *   Helper classes can be used to encapsulate or
 *   simplify additional functionality. They must be
 *   named like FormHelper or InputHelper, eg: the 
 *   first letter and the "H" in Helper must be
 *   capitalized and no others. Helpers must be kept
 *   in the /helpers/ directory.
 *
 * Exceptions:
 *   If you need to define additional exceptions,
 *   they must be named like NoConnectionException.
 *   Any number of letters may be capitalized but
 *   the class and file names must end with 
 *   Exception. Additionally the class must extend
 *   the MavericException class. Exceptions must
 *   be kept in the /system/exceptions/ directory.
 *
 * Models:
 *   Model names should match their tables. For example,
 *   if a table is named "users" the model should be
 *   User and should be stored in User.php in the 
 *   /models/ directory. Models should extend the abstract
 *   class Model and should implement the abstract 
 *   methods.
 *
 * Failing to follow these conventions will cause
 * AutoloadExceptions and pretty much break your application.
 */

function __autoload ( $Class )
{
	if ( preg_match('/Controller$/', $Class) ) {
		if ( file_exists(PATH_CONTROLLERS.$Class.EXT) ) {
			include_once PATH_CONTROLLERS.$Class.EXT;
		}
	} elseif ( preg_match('/Helper$/', $Class) ) {
		if ( file_exists(PATH_HELPERS.$Class.EXT) ) {
			include_once PATH_HELPERS.$Class.EXT;
		}
	} elseif ( preg_match('/Exception$/', $Class) ) {
		if ( file_exists(PATH_SYSTEM.'exceptions/'.$Class.EXT) ) {
			include_once PATH_SYSTEM.'exceptions/'.$Class.EXT;
		}
	} elseif ( file_exists(PATH_MODELS.$Class.EXT) ) {
		include_once PATH_MODELS.$Class.EXT;
	} else {
		throw new AutoloadException($Class);
	}
}

?>

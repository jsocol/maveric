<?php
/**
 * Maveric: Yet Another PHP MVC Framework
 *
 * Many of the things in this file should be
 * moved into the /config/ dir. In general,
 * you should never need to edit this file.
 */

// Set up the Maveric environment.
define('PATH', dirname(__FILE__));
ini_set('include_path', ini_get('include_path').';'.PATH);
require_once PATH.'/config/env.php';

// Set error-reporting level
if ( !defined('MAVERIC_PHP_ERROR_LEVEL') ) {
	define('MAVERIC_PHP_ERROR_LEVEL', (MAVERIC_MODE == 'development' ? E_ALL ^ E_NOTICE
																	 : 0));
}

error_reporting(MAVERIC_PHP_ERROR_LEVEL);

// Get the rest of the configuration info
require_once PATH_CONFIG.'db.php';
require_once PATH_CONFIG.'routes.php';

// Some useful global constants
require_once PATH_SYSTEM.'Constants.php';

// Get the version number
require_once PATH_SYSTEM.'Version.php';

// Include the debugging functions
require_once PATH_SYSTEM.'Log.php';

// Set up autoloading
require_once PATH_SYSTEM.'AutoLoader.php';

// Set up error-handling
set_exception_handler(array('MavericException', 'handler'));

// Load the things that can't be autoloaded
require_once PATH_SYSTEM.'Controller.php';
require_once PATH_SYSTEM.'Router.php';

// Load the database connections
require_once PATH_SYSTEM.'DB.php';

// Set custom headers
@session_start();
@session_regenerate_id();
header("X-Powered-By: Maveric PHP ".maveric_version()." (PHP/".phpversion().")");

switch ($_SERVER['REQUEST_METHOD']):
	case 'GET':
		$method = METHOD_GET;
		break;
	case 'POST':
		$method = METHOD_POST;
		break;
	case 'PUT':
		$method = METHOD_PUT;
		break;
	case 'DELETE':
		$method = METHOD_DELETE;
		break;
	default:
		$method = METHOD_UNKNOWN;
endswitch;

// Transform the requested URI into a meaningful 
// route.
list(list($controller, $action), $params) = Router::find($_SERVER['REDIRECT_URL']);

// Create a new instance of the controller
$ControllerName = ucfirst($controller).'Controller';
$Controller = new $ControllerName;
$Controller->params = $params;

// Do the requested action
$Controller->$action();

// Output to the browser
$Controller->output();



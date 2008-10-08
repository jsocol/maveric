<?php
/**
 * Maveric: Yet Another PHP MVC Framework
 *
 * Many of the things in this file should be
 * moved into the /config/ dir. In general,
 * you should never need to edit this file.
 */

error_reporting(E_ALL ^ E_NOTICE);
ini_set('memory_limit', '128M');
ini_set('date.timezone', 'GMT');

define('PATH', dirname(__FILE__));
define('EXT', '.php');

require_once PATH.'/system/Debug.php';

require_once PATH.'/config/db.php';
require_once PATH.'/config/routes.php';

require_once PATH.'/system/Controller.php';
require_once PATH.'/system/AutoLoader.php';

set_exception_handler(array('MavericException', 'handler'));

require_once PATH.'/system/DB.php';
require_once PATH.'/system/Router.php';

@session_start();
@session_regenerate_id();

list(list($controller, $action), $params) = Router::find($_SERVER['REDIRECT_URL']);

$ControllerName = ucfirst($controller).'Controller';
$Controller = new $ControllerName;
$Controller->params = $params;

$Controller->$action();
$Controller->output();

?>

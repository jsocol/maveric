<?php
/**
 * Define several constants for use throughout the system.
 * These constants are not based on environment.
 */

/**
 * Routing Constants
 */
define("METHOD_POST", 1);
define("METHOD_GET", 2);
define("METHOD_PUT", 4);
define("METHOD_DELETE", 8);
define("METHOD_ALL", METHOD_POST|METHOD_GET|METHOD_PUT|METHOD_DELETE);
define('METHOD_UNKNOWN', 16);

/**
 * Error Constants
 */
define("MAVERIC_E_NONE",0);
define("MAVERIC_E_MESSAGE",1);
define("MAVERIC_E_DEBUG",2);
define("MAVERIC_E_WARNING",4);
define("MAVERIC_E_ERROR",8);

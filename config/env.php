<?php
/**
 * Environment Configuration
 *
 * Set up the PHP environment for Maveric.
 * For the most part, you shouldn't have to 
 * change anything here.
 */

/**
 * Maveric Mode
 *
 * Values are 'production' or 'development'
 */
define('MAVERIC_MODE', 'development');

/**
 * Maveric Log Type (NYI)
 *
 * How to log. Possible values are 'none', 'file',
 * and 'syslog'.
 */
define('MAVERIC_LOG_TYPE', 'none');

/**
 * Maveric Log File (NYI)
 * 
 * Where to log, if MAVERIC_LOG_TYPE is set to 'file'.
 * PHP must be able to write to this file.
 */
define('MAVERIC_LOG_FILE', '/var/log/maveric');

/**
 * Maveric Log Level (NYI)
 *
 * When to write to the Maveric log. Possible 
 * values are 'none', 'message', 'debug', 'warn', and 'error'.
 */
define('MAVERIC_LOG_LEVEL', 'debug');

/**
 * PHP Error Reporting Level
 *
 * Uncomment this line to specify a custom
 * value for PHP's error_reporting() function.
 * The default is '0' when MAVERIC_MODE is
 * 'production', and E_ALL ^ E_NOTICE when
 * it's 'development'.
 */
//define('MAVERIC_PHP_ERROR_LEVEL', E_ALL ^ E_NOTICE);

/**
 * PHP INI Settings
 *
 * Configure the PHP environment.
 */
ini_set('memory_limit',  '32M');
ini_set('date.timezone', 'GMT');

/**
 * Path Settings
 *
 * You can define custom paths here. By default,
 * the entire Maveric install is in the web root.
 * For additional security, you can move everything
 * but "index.php", ".htaccess" and "/config/env.php"
 * out of the web root.
 * 
 * You can use the constant DS for the directory 
 * separator.
 */
define('PATH_CONFIG',      PATH.DS.'config'.DS);
define('PATH_SYSTEM',      PATH.DS.'system'.DS);
define('PATH_CONTROLLERS', PATH.DS.'controllers'.DS);
define('PATH_HELPERS',     PATH.DS.'helpers'.DS);
define('PATH_MODELS',      PATH.DS.'models'.DS);
define('PATH_VIEWS',       PATH.DS.'views'.DS);



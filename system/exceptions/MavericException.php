<?php
/**
 * MavericException
 *
 * MavericException is the base Exception class for
 * Maveric. All custom exceptions must extend this
 * class, which extends PHP's Exception class.
 *
 * Custom exceptions must have a public constructor
 * that calls the parent constructor with two arguments,
 * a message and a code.
 *
 * All exceptions are stored in the /system/exceptions/
 * directory.
 */

/**
 * MavericException
 *
 * Maveric's base exception class.
 */
class MavericException extends Exception
{
	public $controller;
	public $action;

	/**
	 * __construct
	 *
	 * @argument message string
	 * @argument code integer
	 */
	public function __construct ( $message = false, $code = 0 )
	{
		parent::__construct($message, $code);
		$this->controller = $GLOBALS['controller'];
		$this->action     = $GLOBALS['action'];
	}

	/**
	 * message
	 *
	 * Outputs the protected message
	 *
	 * @return message string
	 */
	public function message()
	{
		return $this->message;
	}

	/**
	 * type
	 *
	 * Returns the current exception class
	 *
	 * @return classname string
	 */
	public final function type ()
	{
		return get_class($this);
	}

	/**
	 * handler
	 *
	 * Static exception handling function for uncaught
	 * exceptions.
	 *
	 * @argument exception Exception
	 */
	public final static function handler ( Exception $e )
	{
		include_once PATH.'/views/application/exception'.EXT;
		exit();
	}

	/**
	 * error_handler
	 *
	 * Custom function for unhandled PHP errors and
	 * user errors.
	 *
	 * @argument errno integer
	 * @argument errstr string
	 * @argument errfile string
	 * @argument errline integer
	 * @argument errcontext string
	 */
	public final static function error_handler ( $errno, $errstr, $errfile = false, $errline = false, $errcontext = false )
	{
		// do something with errors.
		echo "<p>Error: $errstr</p>";
	}
}

?>

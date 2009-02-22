<?php
/**
 * The Maveric Debugger
 * 
 * @since 0.2
 * @author James Socol <me@jamessocol.com>
 * @version 1.0
 * @package Maveric
 * @license MIT License
 */

/**
 * The Maveric Debugging/Logging Class
 * 
 * Handles logging messages and debugging features.
 * 
 * @since 0.2
 * @author James Socol <me@jamessocol.com>
 * @version 1.0
 * @package Maveric
 * @license MIT License
 */
final class Debug
{
	/**
	 * Flag whether a connection to the logging facility has
	 * been opened.
	 * @var boolean
	 */
	private static $logopen = false;
	
	/**
	 * Store a file handle for file logging.
	 * @var resource
	 */
 	private static $logfile;
	
	/**
	 * Map the MAVERIC_E_* constants to the LOG_* constants
	 * @var array
	 */
	private static $sysloglevels = array(
		MAVERIC_E_MESSAGE => LOG_INFO,
		MAVERIC_E_DEBUG   => LOG_NOTICE,
		MAVERIC_E_WARNING => LOG_WARNING,
		MAVERIC_E_ERROR   => LOG_ERR
	);
		
	/**
	 * Write to the log, if the message level is greater than
	 * or equal to the log level. If the message cannot be
	 * written, it is an uncatchable fatal error.
	 * 
	 * @since 0.2
	 * @author James Socol <me@jamessocol.com>
	 * @param string $message The message to write.
	 * @param int $level The level of the message. (See MAVERIC_E_* constants)
	 * @return void
	 * @throws MavericException
	 */
 	public static function log ( $message, $level )
 	{
 		// If the message level is too low, don't log
 		switch (MAVERIC_LOG_LEVEL) {
			case 'message': // Log everything
				if ( $level < MAVERIC_E_MESSAGE ) return;
				break;
			case 'debug':
				if ( $level < MAVERIC_E_DEBUG ) return;
				break;
			case 'warn':
				if ( $level < MAVERIC_E_WARNING ) return;
				break;
			case 'error':
				if ( $level < MAVERIC_E_ERROR ) return;
				break;
			case 'none': // Not logging anything, fall through to default
			default:
				return; 
 		} // switch MAVERIC_LOG_LEVEL
 		
 		// Depends on logging type:
 		switch (MAVERIC_LOG_TYPE) {
 			case 'syslog': // Logging to the syslog
 				
 				// open the syslog, if not already
 				if ( !Debug::$logopen ) {
 					openlog("Maveric: ", LOG_PID|LOG_PERROR, LOG_USER);
 					Debug::$logopen = true;
 				} // if $logopen
 				
 				// Write the log message
 				syslog(Debug::$sysloglevels[$level],$message);
 				
 				break;
 			case 'file': // Logging to file
 			
 				// open the log file
 				if ( !Debug::$logopen ) {
 					Debug::$logfile = @fopen(MAVERIC_LOG_FILE, 'a');
 					if ( false === Debug::$logfile ) {
 						throw new MavericException("Could not open logfile for writing!", MAVERIC_E_ERROR);
 					}
 					Debug::$logopen = true;
 				} // if $logopen
 				
 				// Write the log message
 				fwrite(Debug::$logfile, $message."\n");
				break;
			case 'none': // Not logging, fall through to default
			default:
 		} // switch MAVERIC_LOG_TYPE
 	} // function log
}
<?php
/**
 * Contains global-context debugging functions
 */

/**
 * trace
 *
 * Output a message to a text log file.
 *
 * @argument message string
 */
function trace($msg)
{
	$log = fopen(PATH.'/trace.txt', 'a');
	fwrite($log, "$msg\n");
	fclose($log);
}
?>

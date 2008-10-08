<?php
/**
 * Contains global-context debugging and logging
 * functions
 */

/**
 * trace
 *
 * Output a message to a trace file. Can be useful
 * for debugging purposes.
 *
 * @argument message string
 */
function trace($msg)
{
	$log = fopen(PATH.'/trace.txt', 'a');
	fwrite($log, date("[d/m/Y H:i:s] ")."$msg\n");
	fclose($log);
}

function maveric_log ( $msg = false )
{
	switch(MAVERIC_LOG_TYPE):
	endswitch;
}
?>

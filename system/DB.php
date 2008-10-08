<?php
/**
 * Database Connection String
 *
 * Currently only supports MySQL with the MySQLi extension.
 */

$db = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port, $db_sock);
if ( mysqli_connect_errno() ) {
	unset($db);
	throw new NoConnectionException(mysqli_connect_error());
}
?>

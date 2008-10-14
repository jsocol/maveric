<?php

class BadQueryException extends MavericException
{
	public function __construct ( $message = false, $code = 0 )
	{
		parent::__construct($message, $code);
	}

	public function message ()
	{
		if ( MAVERIC_MODE == 'production' ) {
			parent::message();
		} else {
			global $db;
			return $db->error;
		}
	}
}

?>

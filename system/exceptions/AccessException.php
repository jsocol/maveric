<?php

class AccessException extends MavericException
{
	public function __construct ( $message = false, $code = 0 )
	{
		parent::__construct($message, $code);
	}
}

?>
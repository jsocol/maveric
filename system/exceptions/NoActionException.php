<?php

class NoActionException extends MavericException
{
	public function __construct ( $message = false, $code = 0 )
	{
		parent::__construct($message, $code);
	}
	
	public function message()
	{
		return "This controller has no {$this->message} method.";
	}
}

?>
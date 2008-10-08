<?php

class NoControllerException extends MavericException
{
	public function __construct ( $message = false, $code = 0 )
	{
		parent::__construct($message, $code);
	}
	
	public function message()
	{
		return "The controller {$this->message} does not exist.";
	}
}

?>
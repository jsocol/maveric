<?php

class NoViewException extends MavericException
{
	public function __construct ( $message = false, $code = 0 )
	{
		parent::__construct($message, $code);
	}
	
	public function message ()
	{
		return "The file {$this->message} could not be found.";
	}
}

?>
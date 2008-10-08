<?php

class AutoloadException extends MavericException
{
	public function __construct ( $message = false, $code = 0 )
	{
		parent::__construct($message, $code);
	}
	
	public function message()
	{
		return "Could not find class {$this->message}.";
	}
}

?>

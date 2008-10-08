<?php
/**
 * ApplicationController
 *
 * The base controller for user controllers. Can
 * contain any custom functions for this application
 * and overrides for the built-in functions. May define
 * a global _prefilter function. Can be used to create
 * objects used by all controllers.
 */

class ApplicationController extends Controller
{
	/**
	 * $input InputHelper
	 */
	protected $input;

	/**
	 * $base global base_url (reference)
	 */
	protected $base;

	/**
	 * __construct
	 *
	 * Creates a new InputHelper and creates a
	 * reference to the global $base_url variable.
	 *
	 * Must call parent::__construct
	 */
	public function __construct ()
	{
		$this->input = new InputHelper;
		$this->base =& $GLOBALS['base_url'];
		parent::__construct();
	}
}

?>

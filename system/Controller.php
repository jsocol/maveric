<?php
/**
 * Controller
 *
 * Controller is the root of the Controller hierarchy.
 * It contains functions common to all controllers and
 * sets up the basic structure.
 *
 * All methods may be overridden.
 *
 * ApplicationController directly extends Controller.
 * All user controllers must extend 
 * ApplicationController, and may not extend 
 * Controller directly.
 *
 * User controllers must have constructors that
 * call the parent::__constructor method of 
 * ApplicationController, which must call the 
 * parent::__constructor of Controller.
 */

/**
 * Controller
 *
 * Base Controller class.
 */
abstract class Controller
{

	/**
	 * __construct
	 *
	 * Execute any defined _prefilter method.
	 */
	public function __construct ()
	{
		$this->_prefilter();
	}

	/**
	 * _prefilter
	 *
	 * Used to run checks before executing the
	 * controller methods.
	 */
	protected function _prefilter() {}

	/**
	 * output
	 *
	 * Output view templates to the browser.
	 */
	public function output ()
	{
		global $controller, $action;
		if ( file_exists(PATH_VIEWS."$controller/$action".EXT) ) {
			include_once PATH_VIEWS.'application/header'.EXT;
			include_once PATH_VIEWS."$controller/$action".EXT;
			include_once PATH_VIEWS.'application/footer'.EXT;
		} else
			throw new NoViewException("$controller/$action".EXT);
	}

	/**
	 * link_to
	 *
	 * Generate internal links on the site.
	 *
	 * Currently only creates links in the 
	 * controller/action/id format.
	 *
	 * @argument link_text string
	 * @argument link_settings Array
	 */
	protected function link_to ()
	{
		global $base_url;
		
		if ( NULL === $_text = @func_get_arg(0) ) throw new NoArgumentException;
		
		if ( NULL === $_options = @func_get_arg(1) ) throw new NoArgumentException;
		switch ( gettype($_options) ):
			case 'string':
				$_url = $_options;
				break;
			case 'array':
				$_parts = $_options;
				if ( !$_parts['controller'] ) {
					$_parts['controller'] = $GLOBALS['controller'];
				}
				if ( !$_parts['action'] ) {
					$_parts['action'] = $GLOBALS['action'];
				}
				if ( !$_parts['id'] ) {
					$_parts['id'] = $_parts[0];
				}
				if ( $_parts['id']->id ) {
					$_parts['id'] = $_parts['id']->id;
				}
				
				$_url = $_parts['controller'].'/'.$_parts['action'].'/'.$_parts['id'];
				
				if ( $_parts['confirm'] ) {
					$_confirm = " onclick='return confirm(\"{$_parts['confirm']}\");'";
				}
				
				unset($_parts['controller'], $_parts['action'], $_parts['id'], $_parts[0], $_parts['confirm']);
				
				foreach ( $_parts as $_k => $_v ) {
					$_query[] = $_k."=".urlencode($_v);
				}
				if ( $_query ) {
					$_query = '?'.implode('&amp;', $_query);
				}
				break;
			default:
				throw new BadArgumentException;
		endswitch;
		
		$server = (stripos($_SERVER['SERVER_PROTOCOL'],'s')===false)?'http://':'https://';
		$server .= $_SERVER['SERVER_NAME'];
		
		$_link = "<a href='{$server}{$base_url}/{$_url}{$_query}' title='{$_text}'{$_confirm}>{$_text}</a>";
		
		return $_link;
	}

	/**
	 * redirect_to
	 *
	 * Automatically redirects the current script
	 * to the URL given. Can build links with
	 * controller/action/id format.
	 *
	 * @argument link_options Array
	 */
	protected function redirect_to ()
	{
		global $base_url;
		$_input = @func_get_arg(0);
		if ( is_null($_input) ) throw new NoArgumentException;
		switch ( gettype($_input) ):
			case 'string':
				$_redirect = $_input;
				break;
			case 'array':
				$_parts = $_input;
				if ( !$_parts['controller'] ) {
					$_parts['controller'] = $GLOBALS['controller'];
				}
				if ( !$_parts['action'] ) {
					$_parts['action'] = $GLOBALS['action'];
				}
				if ( !$_parts['id'] ) {
					$_parts['id'] = $_parts[0];
				}
				if ( $_parts['id']->id ) {
					$_parts['id'] = $_parts['id']->id;
				}

				$_redirect = $_parts['controller'].'/'.$_parts['action'].'/'."{$_parts['id']}";

				unset($_parts['controller'], $_parts['action'], $_parts['id'], $_parts[0], $_parts['confirm']);
				
				foreach ( $_parts as $_k => $_v ) {
					$_query[] = $_k."=".urlencode($_v);
				}
				if ( $_query ) {
					$_query = '?'.implode('&', $_query);
				}

				break;
			default:
				throw new BadArgumentExecption;
		endswitch;
		
		if ( $_redirect ) {
			header("Location: {$base_url}/{$_redirect}{$_query}");
		}
	}

	/**
	 * validates
	 *
	 * Validate string or data against an (optional) format.
	 *
	 * The first argument (required) is a value. Returns true
	 * as long as the value is not NULL (ie: is set) so 0
	 * and the empty string will return true if there is no
	 * second argument.
	 *
	 * The second argument is either a string or an array.
	 * A string can be 'integer', 'float', 'numeric', 'array',
	 * 'object', 'string' or a class name. validates will 
	 * return true if the first argument is the same type.
	 *
	 * If an array is given, it must have an index called
	 * 'with' which is a regular expression. validates will 
	 * return true if the first argument matches the 
	 * expression.
	 *
	 * @argument subject mixed
	 * @argument test mixed
	 */
	protected function validates ()
	{
		if ( NULL === $_test = @func_get_arg(0) ) return false;
		if ( (!$_conds = @func_get_arg(1)) && (isset($_test)) ) {
			return true;
		}
		
		switch ( gettype($_conds) ):
			case 'array':
				if ( $_conds['with'] ) {
					return preg_match($_conds['with'], $_test);
				}
			case 'string':
				switch ( $_conds ):
					case 'integer':
						return ctype_digit($_test);
						break;
					case 'float':
					case 'numeric':
						return is_numeric($_test);
						break;
					case 'array':
						return is_array($_test);
						break;
					case 'object':
						return ('object' == gettype($_test));
						break;
					case 'string':
						return ('string' == gettype($_test));
						break;
					default:
						return ($_test instanceof $_conds);
				endswitch;
		endswitch;
		
		return false;
	}
}

?>

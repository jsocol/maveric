<?php
/**
 * InputHelper.php
 */

class InputHelper
{
	protected $get;
	protected $post;
	protected $cookie;
	protected $session;
	protected $files;
	
	public function __construct()
	{
		$_arrays = $arrays = array(
			'get'     => $_GET,
			'post'    => $_POST,
			'cookie'  => $_COOKIE,
			'file'    => $_FILES,
			'session' => $_SESSION);
		
		foreach ( $_arrays as $_name => $_array ) {
			$this->{$_name} = array();
			foreach ( $_array as $_k => $_v ) {	
				if ( $_name == 'session' ) {
					$this->{$_name}[$_k] = $_v;
				} else {
					$this->{$_name}[$_k] = $this->_clean($_v);
				}
			}
		}
		return $this;		
	}
	
	/** These protected functions are used to specifically
		sanitize data if request. _clean just calls _trim,
		possibly recursively. The rest only make sense on
		strings. **/
	
	protected function _clean()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		return $this->_trim($_args[0]);
	}
	
	/** Do a database escape **/
	
	protected function _escape()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		if ( is_array($_args[0]) ) {
			foreach ( $_args[0] as &$_arg ) {
				$_arg = $this->_escape($_arg);
			}
		}
		
		if ( !is_string($_args[0]) ) return $_args[0];
		
		global $db;
		if ( !$db ) throw new NoConnectionException;
		
		return $db->real_escape_string($_args[0]);
	}
	
	/** URL-encode **/
	
	protected function _encode()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		if ( is_array($_args[0]) ) {
			foreach ( $_args[0] as &$_arg ) {
				$_arg = $this->_encode($_arg);
			}
		}
		
		if ( !is_string($_args[0]) ) return $_args[0];
		
		return urlencode($_args[0]);
	}
	
	/** Convert HTML entities **/
	
	protected function _entities()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		if ( is_array($_args[0]) ) {
			foreach ( $_args[0] as &$_arg ) {
				$_arg = $this->_entities($_arg);
			}
		}
		
		if ( !is_string($_args[0]) ) return $_args[0];
		
		return htmlentities($_args[0]);
	}
	
	/** Strip tags **/
	
	protected function _strip()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		if ( is_array($_args[0]) ) {
			foreach ( $_args[0] as &$_arg ) {
				$_arg = $this->_strip($_arg);
			}
		}
		
		if ( !is_string($_args[0]) ) return $_args[0];
		
		return strip_tags($_args[0]);
	}
	
	/** Strip <script> tags **/
	
	protected function _script()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		if ( is_array($_args[0]) ) {
			foreach ( $_args[0] as &$_arg ) {
				$_arg = $this->_script($_arg);
			}
		}
		
		if ( !is_string($_args[0]) ) return $_args[0];
		
		return preg_replace('/<\/?script[^>]*>/i', '', $_args[0]);
	}
	
	/** Escape PHP tags **/
	protected function _php()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		if ( is_array($_args[0]) ) {
			foreach ( $_args[0] as &$_arg ) {
				$_arg = $this->_php($_arg);
			}
		}
		
		if ( !is_string($_args[0]) ) return $_args[0];
		
		return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $_args[0]);
	}
	
	/** Trim a string **/
	
	protected function _trim()
	{
		if ( !$_args = @func_get_args() ) return NULL;
		
		if ( is_array($_args[0]) ) {
			foreach ( $_args[0] as &$_arg ) {
				$_arg = $this->_trim($_arg, $_args[1]);
			}
		}
		
		if ( !is_string($_args[0]) ) return $_args[0];
		
		switch ( $_args[1] ):
			case 'right':
				return rtrim($_args[0]);
				break;
			case 'left':
				return ltrim($_args[0]);
				break;
			case 'both':
			default:
				return trim($_args[0]);
				break;
		endswitch;
	}
	
	/** These accessor methods take 0-2 parameters. No params
		will return the whole array. One param is the key you
		want (ie: $input->get('file') ~= $_GET['file']). Two
		params is the key you want followed by a comma-separated
		list of the following possible values
			- escape, does a database escape
			- encode, url-encodes
			- entities, encode HTML entities
			- php, encode only PHP tags
			- script, strip <script> tags
			- strip, strip all HTML tags
			- trim, trim the string. (done automatically.)
		If your request key returns an array, and you've specified
		any scrubbing, it will be done as much as possible,
		recursively. If it's an object, you pretty much just get
		the object back.
		
		All of these run through the _retrieve() function so I
		didn't have to repeat the same thing five times. **/

	public function get()
	{
		$_options = @func_get_args();
		return $this->_retrieve($this->get, $_options);
	}
	
	public function post()
	{
		$_options = @func_get_args();
		return $this->_retrieve($this->post, $_options);
	}
	
	public function cookie()
	{
		$_options = @func_get_args();
		return $this->_retrieve($this->cookie, $_options);
	}
	
	public function session()
	{
		$_options = @func_get_args();
		return $this->_retrieve($this->session, $_options);
	}
	
	public function files()
	{
		$_options = @func_get_args();
		return $this->_retrieve($this->files, $_options);
	}

	/** to DRY, since the accessor methods are all
		the same, here's the retrieve method. **/
	
	protected function _retrieve(Array &$array, Array $options = NULL)
	{
		if ( !$options ) {
			return $array;
		}
		
		if ( !is_string($options[0]) ) throw new BadArgumentException('Only a string may be used as an array key.');
		
		$_value = $array[$options[0]];
		
		if ( $options[1] && !is_string($options[1]) ) throw new BadArgumentException('Argument #2 should be a comma-separated list.');
		
		if ( $options[1] ) {
			$_filters = preg_split('/\s*,\s*/', trim($options[1]));
			foreach ( $_filters as $_filter ):
				$_method = '_'.$_filter;
				if ( in_array($_method, get_class_methods($this)) ) {
					$_value = $this->$_method($_value);
				}
			endforeach;
		}
		
		return $_value;
	}
}

?>

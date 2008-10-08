<?php
/**
 * form.php - Form helper object
 */

class FormHelper
{
	public $action;
	public $method = 'get';
	public $onsubmit;
	public $name;
	protected $radios;
	protected $options;
	
	public function __construct ( Array $options = NULL )
	{
		foreach ( $options as $_attr => $_value ) {
			$this->{$_attr} = $_value;
		}
	}
	
	public function open ()
	{
		$_tag = "<form action='{$this->action}' method='{$this->method}'";
		if ( $this->onsubmit ) $_tag .= " onsubmit=\"{$this->onsubmit}\"";
		if ( $this->name ) $_tag .= " name='{$this->name}'";
		if ( $this->id ) $_tag .= " id='{$this->id}'";
		if ( $this->enctype ) $_tag .= " enctype='{$this->enctype}'";
		$_tag .= ">\n";

		// check for file upload/max file size
		if ( $this->MAX_FILE_SIZE )
			$_tag .= '<input type="hidden" name="MAX_FILE_SIZE" value="' . $this->MAX_FILE_SIZE . "\" />\n";

		return $_tag;
	}
	
	public function close ()
	{
		return "</form>\n";
	}
	
	public function label ( $for, $text )
	{
		return "<label for=\"$for\">$text</label>";
	}
	
	public function text ( $name, Array $options = NULL )
	{
		$_tag = "<input type='text' name='$name' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) $_tag .= "id='{$name}' ";
		$_tag .= "/>";
		
		return $_tag;
	}

	// for file uploading
	public function file ( $name, Array $options = NULL )
	{
		$_tag = "<input type='file' name='$name' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) $_tag .= "id='{$name}' ";
		$_tag .= "/>";
		
		return $_tag;
	}

	public function hidden ( $name, Array $options = NULL )
	{
		$_tag = "<input type='hidden' name='$name' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) $_tag .= "id='{$name}' ";
		$_tag .= "/>";
		
		return $_tag;
	}
	
	public function password ( $name, Array $options = NULL )
	{
		$_tag = "<input type='password' name='$name' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) $_tag .= "id=\"{$name}\" ";
		$_tag .= "/>";

		return $_tag;
	}
	
	public function textarea ( $name, Array $options = NULL )
	{
		$_tag = "<textarea name='$name' ";
		if ( is_array($options) )
		{
			foreach ( $options as $_attr => $_value )
			{
				if ( $_attr == 'value' )
					continue;
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) $_tag .= "id='{$name}' ";
		$_tag .= ">{$options['value']}</textarea>";

		return $_tag;
	}

	public function checkbox ( $name, Array $options = NULL )
	{
		$_tag = "<input type='checkbox' name='$name' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) $_tag .= "id='{$name}' ";
		$_tag .= "/>";

		return $_tag;
	}
	
	public function radio ( $name, Array $options = NULL )
	{
		$_tag = "<input type='radio' name='$name' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) {
			$_id = $name.(++$this->radios[$name]);
			$_tag .= "id='$_id' ";
		}
		$_tag .= "/>";
		
		return $_tag;
	}
	
	public function submit ( $value, Array $options = NULL )
	{
		$_tag = "<input type='submit' value='$value' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		$_tag .= "/>";
		
		return $_tag;
	}
	
	public function reset ( $value, Array $options = NULL )
	{
		$_tag = "<input type='reset' value='$value' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		$_tag .= "/>";
		
		return $_tag;
	}
	
	public function button ( $value, Array $options = NULL )
	{
		$_tag = "<input type='button' value='$value' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		$_tag .= "/>";
		
		return $_tag;
	}
	
	public function add_option ( $name, Array $options = NULL )
	{
		$this->options[$name][] = $options;
	}
	
	public function select ( $name, Array $options = NULL )
	{
		if ( !is_array($this->options[$name]) ) $this->options['name'] = array();
		
		$_tag = "<select name='$name' ";
		if ( is_array($options) ) {
			foreach ( $options as $_attr => $_value ) {
				$_tag .= "$_attr=\"$_value\" ";
			}
		}
		if ( !$options['id'] ) $_tag .= "id='{$name}' ";
		$_tag .= ">\n";
		
		foreach ( $this->options[$name] as $_opt ) {
			$_tag .= "<option value=\"";
			$_tag .= ( $_opt['value'] ) ? $_opt['value'] : $_opt['text'];
			$_tag .= "\" ";
			$_tag .= ( $_opt['selected'] ) ? 'selected="selected"' : '';
			$_tag .= ">";
			$_tag .= ( $_opt['text'] ) ? $_opt['text'] : $_opt['value'];
			$_tag .= "</option>\n";
		}
		
		$_tag .= "</select>\n";
		
		return $_tag;
	}
}

?>
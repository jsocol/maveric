<?php

/**
 * Tests1
 *
 * @project Cocode
 * @author James Socol
 * @copyright 2009
 */

$db = new mysqli('localhost','cocode','password','cocode');

require_once 'system/Inflect.php';
require_once 'models/Model.php';

class User extends Model
{
	protected $has_many = array(
		'snippets',
		'comments'
	);
	
	protected $password;
	
	public function nice_name ()
	{
		return ($this->name)?$this->name:$this->login;
	}
	
	public function check_password ($test)
	{
		return ($this->password == crypt($test,$this->password));
	}
	
	public function set_password ($pwd)
	{
		$this->password = crypt($pwd);
	}
}

class Snippet extends Model
{
	protected $belongs_to = array(
		'user',
		'parent'=>array('model'=>'snippet')
	);
	
	protected $has_many = array(
		'comments',
		'children'=>array('model'=>'snippet','foreign_key'=>'parent_id')
	);
}

class Comment extends Model
{
	protected $belongs_to = array(
		'user',
		'snippet'
	);
}




// Setting Model
class Setting extends Model
{
	public function save ()
	{
		
		$this->value = serialize($this->value);
		
		parent::save();
		
		$this->value = unserialize($this->value);
	}
}



////////////////////////////////////////////

$sn1 = new Snippet(12);

echo $sn1->user->comments[0]->body;
//$set->value = "Local Computer";
//$set->save();

?>

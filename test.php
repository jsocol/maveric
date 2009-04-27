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
		'children'=>array('model'=>'snippet','field'=>'parent_id')
	);
}

class Comment extends Model
{
	protected $belongs_to = array(
		'user',
		'snippet'
	);
}

////////////////////////////////////////////

$sn = new Snippet(1);

?>
<h1><?php echo $sn->name; ?> by <?php echo $sn->user->nice_name(); ?></h1>

<div><pre><?php echo $sn->body; ?></pre></div>

<?php foreach($sn->comments as $c): ?>
<div><?php echo $c->body; ?></div>
<?php endforeach; ?>
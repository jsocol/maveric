<?php
/**
 * Abstract Model Class
 *
 * Base class for all user Models.
 * 
 * @TODO
 *  - delete()
 *  - has_and_belongs_to_many
 *  - test has_one
 *  - DOCUMENT!
 */

class Model
{
	/**
	 * Static list of table fields to prevent re-lookups.
	 */
 	private static $fields = array();
	
	/**
	 * Name of the table. (Will be infered if not set)
	 */
 	protected $table = false;
	
	/**
	 * Initial data retrieved from database with find()
	 */
	protected $initial = array();
	
	/**
	 * All tables have an "id" primary key
	 */
	public $id;
	
	/**
	 * has_one relationships
	 * Dependent table has foreign keys
	 */
 	protected $has_one = array();
 	
 	/**
 	 * has_many
 	 * Dependent table has foreign keys
 	 */
 	protected $has_many = array();
 	
 	/**
 	 * belongs_to
 	 * Table must have a foreign key for each belongs_to
 	 */
 	protected $belongs_to = array();
 	
 	/**
 	 * has_and_belongs_to_many
 	 * requires a join table with no primary key "id"
 	 */
 	protected $has_and_belongs_to_many = array();

	/**
	 * Constructor
	 */
	public function __construct ($find = false)
	{
		// If the name of the table is not set, set it
		if (false===$this->table) {
			$this->table = strtolower(pluralize(get_class($this)));
		}
		
		global $db;
		
		// Set the columns:
		if(self::$fields[$this->table]){
			foreach(self::$fields[$this->table] as $f){
				$this->initial[$f] = false;
			}
		} else {
			$res = $db->query("DESCRIBE {$this->table};");
			if($res){
				while($r = $res->fetch_assoc()){
					$this->initial[$r['Field']] = false;
					self::$fields[$this->table][] = $r['Field'];
				}
			} else {
				return null;
			}
		}
		
		// If there's an argument, pass it to find
		if ($find) {
			$this->find($find);
		}
		
		return $this;
	}

	public function find()
	{
		global $db;
		$arg = func_get_arg(0);
		
		// if it's an array, treat as key-value pairs
		if ( is_array($arg) ) {
			$where = array();
			foreach($arg as $f=>$v) {
				$where[] = "$f = '$v' "; 
			}
			$res = $db->query("SELECT * FROM {$this->table} WHERE ".implode(' AND ', $where).' LIMIT 1;');
		}
		// or else treat as an ID
		else {
			$res = $db->query("SELECT * FROM {$this->table} WHERE id = '$arg' LIMIT 1;");
		}
		
		// if the query was a success
		if($res && $d=$res->fetch_assoc()){
			$this->initial = $d;
			
			foreach ($d as $k=>$val) {
				$this->{$k} = $val;
			}
		}
		
		return $this;
	}
	
	public function find_all ( Array $conds = null, Array $order = null )
	{
		global $db;
		$where = array();
		if($conds) {
			foreach($conds as $f=>$v){
				if ('NULL'==$v){
					$where[] = "$f is NULL";
				} else {
					$where[] = "$f = '$v'";
				}
			}
		}
		
		$orderby = array();
		if($order){
			foreach($order as $f=>$d){
				if(is_int($f)){
					$orderby[] = "$d";
				} else {
					$orderby[] = "$f $d";
				}
			}
		}
		
		$return = array();
		
		$query = "SELECT id FROM {$this->table}";
		if($where)
			$query .= " WHERE ".implode(' AND ',$where);
		if($orderby)
			$query .= " ORDER BY ".implode(', ', $orderby);
		
		$res = $db->query($query);
		
		$class = get_class($this);
		
		echo $db->error;
		
		while($o = $res->fetch_assoc()){
			$return[] = new $class($o['id']);
		}
		
		return $return;
	}
	
	public function save()
	{
		global $db;

		if (!$this->is_saved) {
			
			if (isset($this->initial['created'])&&0==$this->created) {
				$this->created = time();
			}
			
			if ( $this->initial['id'] ) {
				$data['id'] = $this->initial['id'];
			} else {
				$data['id'] = 'NULL';
			}
		
			$cols = '('.implode(',',array_keys($this->initial)).')';
			
			$updates = array();
			
			foreach ($this->initial as $k=>$v) {
				
				if ('id'==$k) continue;
				
				if (preg_match('/^([\w_]+)_id$/', $k, $p)) {
					if ($this->{$p[1]}) {
						$data[$k] = $this->{$p[1]}->id;
						$new[$k] = $this->{$p[1]}->id;
					} else {
						$data[$k] = 'NULL';
						$new[$k] = false;
					}
				} else {
					$data[$k] = $db->real_escape_string($this->{$k});
					$new[$k] = $this->{$k};
				}
				
				$updates[] = "{$k} = ".self::quote($data[$k]); 
			}
			
			$vals = "(".implode(",", array_map(array('Model','quote'),array_map(array($db, 'real_escape_string'), array_values($data)))).")";
			
			if($this->initial['id']){
				$update = " ON DUPLICATE KEY UPDATE ".implode(', ',$updates);
			}else{
				$update = '';
			}
			
			$db->query("INSERT INTO {$this->table} $cols VALUES $vals $update;");
			//return "INSERT INTO {$this->table} $cols VALUES $vals $update;";
			
			if(!$this->id){
				$this->id = $db->insert_id;
			}
			
			$this->initial = $new;
			$this->initial['id'] = $this->id;
			
			
			// Do the saving for has_and_belongs_to_many collections
			foreach($this->has_and_belongs_to_many as $f=>$k)
			{
				if(is_int($f)){
					$col = $this->{$k};
					$them = singularize($k);
				}else{
					$col = $this->{$f};
					$opts = $this->has_and_belongs_to_many[$f];
					$them = ($opts['model'])?$opts['model']:singularize($f);
					
					if($opts['join']){
						$join = $opts['join'];
					}
					if($opts['foreign_key']){
						$fkey = $opts['foreign_key'];
					}
					if($opts['key']){
						$key = $opts['key'];
					}
				}
				
				$me = strtolower(get_class($this));
				
				
				if(!$join){
					$p = array(pluralize($me),pluralize($them));
				}
				
				$ids = array();
				foreach($col as $o){
					$ids[] = $o->id;
				}
			}
		}
	}
	
	private static final function quote ($str)
	{
		if('NULL'==$str){
			return $str;
		}else{
			return "'$str'";
		}
	}
	
	public function delete() {}
	
	public function __get ($_k)
	{
		// Special case for is_saved
		if ('is_saved'==$_k) {
			$saved = true;
			foreach($this->initial as $k=>$v){
				if(preg_match('/([\w_]+)_id$/',$k,$p)) {
					if ($this->{$p[1]}->id!=$this->initial[$k]) {
						$saved = false;
						break;
					}
				} else {
					if ($this->{$k}!=$this->initial[$k]) {
						$saved = false;
						break;
					}
				}
			}
			
			return ($saved&&(false!=$this->id));
		}
		
		// Other, fancy things.
		
		// If it's in the belongs_to array
		if(in_array($_k, $this->belongs_to)||array_key_exists($_k,$this->belongs_to))
		{
			if(!$this->{$_k."_id"}){
				return null;
			}
			// If it's a simple relationship
			if(in_array($_k,$this->belongs_to)){
				$class = ucfirst($_k);
				$this->{$_k} = new $class($this->{$_k."_id"});
				return $this->{$_k};
			} else {
				$opts = $this->belongs_to[$_k];
				$class = ucfirst($opts['model']);
				$this->{$_k} = new $class($this->{$_k."_id"});
				return $this->{$_k};
			}
		}
		
		// If it's in the has_one array
		if(in_array($_k, $this->has_one))
		{
			$class = ucfirst($_k);
			$field = strtolower(get_class($this)).'_id';
			$this->{$_k} = new $class(array($field=>$this->id));
			return $this->{$_k};
		}
		if(array_key_exists($_k,$this->has_one))
		{
			$opts = $this->has_one[$_k];
			
			$class = ucfirst($opts['model']);
			$field = ($opts['field'])?$opts['field']:$_k.'_id';
			
			$this->{$_k} = new $class(array($field=>$this->id));
			return $this->{$_k};
		}
		
		// If it's in the has_many array
		if(in_array($_k, $this->has_many))
		{
			$class = ucfirst(singularize($_k));
			$t = new $class;
			$field = strtolower(get_class($this)).'_id';
			$this->{$_k} = $t->find_all(array($field=>$this->id));
			return $this->{$_k};
		}
		
		if(array_key_exists($_k,$this->has_many))
		{
			$opts = $this->has_many[$_k];
			
			$class = ucfirst($opts['model']);
			$field = ($opts['field'])?$opts['field']:$_k.'_id';
			
			$t = new $class;
			$this->{$_k} = $t->find_all(array($field=>$this->id));
			
			return $this->{$_k};
		}
		
		// In Has_and_belongs_to_many?
		if(in_array($_k,$this->has_and_belongs_to_many))
		{
			global $db;
			
			$them = singularize($_k);
			$class = ucfirst($them);
			
			$me = strtolower(get_class($this));
			$p = array(pluralize($me),pluralize($them));
			sort($p);
			
			$join = "{$p[0]}_{$p[1]}";
			
			$query = "SELECT {$them}_id FROM $join WHERE {$me}_id = '{$this->id}';";
			
			$res = $db->query($query);

			$this->{$_k} = array();
			if($res) while ($o = $res->fetch_assoc()) {
				$this->{$_k}[] = new $class($o[$them.'_id']);
			}
			
			return $this->{$_k};
		}
		
		if(array_key_exists($_k,$this->has_and_belongs_to_many))
		{
			$opts = $this->has_and_belongs_to_many[$_k];
			
			global $db;
			
			if($opts['model']){
				$them = $opts['model'];
			}else{
				$them = singularize($_k);
			}
			
			$class = ucfirst($them);
			
			$me = strtolower(get_class($this));
			
			$key = ($opts['key'])?$opts['key']:$me.'_id';
			
			$fkey = ($opts['foreign_key'])?$opts['foreign_key']:$them.'_id';
			
			if($opts['through']){
				$join = $opts['through'];
			}else{
				$p = array(pluralize($them),pluralize($me));
				sort($p);
				$join = "{$p[0]}_{$p[1]}";
			}
			
			$query = "SELECT $fkey FROM $join WHERE $key = '{$this->id}';";
			
			$res = $db->query($query);
			
			$this->{$_k} = array();
			
			while($o = $res->fetch_assoc()){
				$this->{$_k}[] = new $class($o[$fkey]);
			}
			
			return $this->{$_k};
		}
	}
}

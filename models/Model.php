<?php
/**
 * Model Base Class
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
	
	private static $tables = array();
	
	private static $loaded = array();
	
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
	 * The constructor sets up the initial list of fields and
	 * prepares the object. If an argument is passed, the 
	 * constructor passes it to {@see find()}
	 * 
	 * @param mixed optional
	 */
	public function __construct ($find = false)
	{
		// If the name of the table is not set, set it
		if (false===$this->table) {
			if(!($this->table = self::$tables[get_class($this)])){
				self::$tables[get_class($this)] = $this->table = strtolower(pluralize(get_class($this)));
			}
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

	/**
	 * Find a row in the database based on search criteria, populating
	 * the current instance with the data it finds.
	 * 
	 * If passed a numeric string or integer, will assume the
	 * argument is an 'id'. If passed an array, will assume the
	 * key=>value pairs correspond with the column=>value names.
	 * Multiple columns are combined with AND.
	 * 
	 * @param mixed search
	 */
	public function find($arg)
	{
		global $db;
				
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
	
	/**
	 * Returns a (possibly empty) array of objects meeting
	 * the search conditions.
	 * 
	 * The first parameter is a (possibly null/empty) array of
	 * search conditions, where key=>value pairs correspond to
	 * column=>value pairs in the table. Multiple conditions are
	 * joined with AND.
	 * 
	 * The second parameter is an array of ORDER BY fields. The
	 * default ordering is ASC, but can be specified by using
	 * the column as a key, and ASC or DESC as the value.
	 * 
	 * The third parameter is a limit, which is either a number
	 * or numeric string, or two comma-separated numbers (ie: 5
	 * or '4,10').
	 * 
	 * All three parameters are optional.
	 * 
	 * @param array $conds search conditions
	 * @param array $order order by clause
	 * @param mixed $limit
	 * @return array
	 */
	public function find_all ( Array $conds = null, Array $order = null, $limit = false )
	{
		global $db;
		$where = array();
		if($conds) {
			foreach($conds as $f=>$v){
				$v = $db->real_escape_string($v);
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
		if($limit)
			$query .= " LIMIT $limit";
		
		$res = $db->query($query);
		
		$class = get_class($this);
		
		if(!$db->error)
		while($o = $res->fetch_assoc()){
			$return[] = new $class($o['id']);
		}
		
		return $return;
	}
	
	/**
	 * Save the current object into the database.
	 */
	public function save()
	{
		global $db;

		if (!$this->is_saved) {
			
			if (isset($this->initial['created'])&&0==$this->created) {
				$this->created = time();
			}else if(0!=$this->initial['created']){
				$this->created = $this->initial['created'];
			}
			
			if(isset($this->initial['updated'])){
				$this->updated = time();
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
					$data[$k] = $this->{$k};
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
					sort($p);
					$join = "{$p[0]}_{$p[1]}";
				}
				
				if(!$key){
					$key = $me.'_id';
				}
				
				if(!$fkey) {
					$fkey = $them.'_id';
				}
				
				$ids = array();
				foreach($col as $o){
					$ids[] = "({$this->id},{$o->id})";
				}
				
				// erase the current list
				$db->query("DELETE FROM `{$join}` WHERE `$key` = {$this->id};");
				
				// set the new list
				$db->query("INSERT IGNORE INTO `{$join}` (`$key`,`$fkey`) VALUES "
						  .implode(',',$ids).";");
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
	
	/**
	 * Permanently delete a row from the database, and clear this
	 * object.
	 * 
	 * Deletes dependent rows (anything that belongs_to the current
	 * object) unless the 'dependent' property of the association
	 * is set to 'ignore'. That is, the has_many and has_one arrays
	 * should have dependent => ignore set.
	 */
	public function delete ()
	{
		// only delete if this row is saved
		if($this->id) {
			
			global $db;

			foreach($this->has_one as $k => $v) {
				if(is_int($k)) {
					$o = $this->{$v};
					$o->delete();
				} else {
					if($v['dependent']!='ignore') {
						$o = $this->{$k};
						$o->delete();
					}else{
						$me = ($v['foreign_key'])?$v['foreign_key']:strtolower(get_class($this)).'_id';
						$o = $this->{$k};
						$o->{$me} = 'NULL';
						$o->save();
					}
				}
			}
			
			foreach($this->has_many as $k => $v) {
				if(is_int($k)) {
					foreach ($this->{$v} as $o) {
						$o->delete();
					}
				} else {
					if($v['dependent']!='ignore') {
						foreach ($this->{$k} as $o) {
							$o->delete();
						}
					}else{
						$me = ($v['foreign_key'])?$v['foreign_key']:strtolower(get_class($this)).'_id';
						foreach($this->{$k} as $o) {
							$o->{$me} = 'NULL';
							$o->save();
						}
					}
				}
			}
			
			// Do the deleting for has_and_belongs_to_many collections
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
					sort($p);
					$join = "{$p[0]}_{$p[1]}";
				}
				
				if(!$key){
					$key = $me.'_id';
				}
				
				if(!$fkey) {
					$fkey = $them.'_id';
				}
				
				$ids = array();
				foreach($col as $o){
					$ids[] = "({$this->id},{$o->id})";
				}
				
				// erase the current list
				$db->query("DELETE FROM `{$join}` WHERE `$key` = {$this->id};");
			}
			
			$db->query("DELETE FROM `{$this->table}` WHERE id = {$this->id};");
			
			foreach($this->initial as $k => $v) {
				unset($this->$k, $this->initial[$k]);
			}
		}
	}
	
	/**
	 * Overload get method
	 * 
	 * Used to lazily retrieve the associated models through the 
	 * has_one, has_many, belongs_to, and has_and_belongs_to_many
	 * relationships.
	 */
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
			// If it's a simple relationship
			if(in_array($_k,$this->belongs_to)){
				$fkey = "{$_k}_id";
				$class = ucfirst($_k);
				if(!$this->$fkey){
					return null;
				}else{
					$this->{$_k} = new $class($this->{$fkey});
					return $this->{$_k};
				}
			} else {
				$opts = $this->belongs_to[$_k];
				$class = ($opts['model'])?ucfirst($opts['model']):ucfirst($_k);
				$fkey = ($opts['foreign_key'])?$opts['foreign_key']:$_k.'_id';
				if(!$this->$fkey){
					return null;
				}else{
					$this->{$_k} = new $class($this->{$fkey});
					return $this->{$_k};
				}
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
			
			$class = $opts['model']?ucfirst($opts['model']):ucfirst($_k);
			$field = ($opts['foreign_key'])?$opts['foreign_key']:$_k.'_id';
			
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
			
			$class = $opts['model']?ucfirst($opts['model']):ucfirst(singularize($_k));
			$field = ($opts['foreign_key'])?$opts['foreign_key']:strtolower(get_class($this)).'_id';
			
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
				$p = array($_k,pluralize($me));
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

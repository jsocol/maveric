<?php
/**
 * Abstract Model Class
 *
 * Base class for all user Models. Models
 * must implement two static methods [find()
 * and create()] and two nonstatic methods
 * [save() and delete()].
 *
 * Constructors must be protected. New objects
 * should be created with the find() and create()
 * methods. The constructor should take a single,
 * optional argument: an ID corresponding to
 * a table row. If no such row exists, the 
 * constructor should throw a NoRecordException.
 *
 * find()
 *   find() must take one or two arguments. The
 *   first is either a numeric ID corresponding
 *   to a table row or a string, either 'first'
 *   or 'all'. If the first argument is a number,
 *   the second argument is not allowed. If it's
 *   'all', the second argument is optional. If
 *   it's 'first' the second argument is required.
 *
 *   The second argument is either a string or
 *   an array. If it is a string, the second argument
 *   is used as the body of a "WHERE" clause, and
 *   NO additional escaping is done. If it is an
 *   array, the keys are taken as column names and
 *   the values are used as values (and automatically
 *   escaped). All key-value pairs are added to the
 *   "WHERE" clause with "AND" conditions.
 *
 *   If the first argument is a number or 'first',
 *   the find() method must return a single instance
 *   of the model. (If a number is given that does
 *   not correspond to a row, the constructor should 
 *   throw a NoRecordException. If 'first' is used 
 *   and no record matches, find() should return 
 *   NULL.)
 *
 *   If the first argument is 'all', find() should
 *   return an array of instances of the model. This
 *   array can be empty (no rows matched) or contain
 *   a single object (one row matched).
 *
 * create()
 *   create() creates a new object, and must take one
 *   optional argument, an array. Keys of the array
 *   are treated as members of the model (which, in
 *   turn, should correspond to table columns). 
 *   Values of the array are the value of those
 *   members/columns. This should return a (partially)
 *   filled instance of the model.
 *
 *   Calling create() with no arguments should return
 *   an empty instance of the model.
 *
 *   create() MUST NOT save automatically. save() must
 *   be called separately.
 *
 * save()
 *   save() writes the current instance of the model
 *   to the database. This can be an INSERT or an
 *   UPDATE statement (or can be accomplished with
 *   INSERT ... ON DUPLICATE KEY UPDATE ...).
 *
 *   save() should return $this to allow chaining.
 *
 * delete()
 *   delete() removes the current instance of the 
 *   model from the table (ie: deletes the 
 *   corresponding row). Should unset($this) and
 *   return true on successful delete.
 *
 * Exceptions
 *   Any methods may throw NoConnectionException
 *   if no database connection is found (you could,
 *   obviously, write everything to files instead
 *   of using the database).
 *
 *   The constructor should throw NoRecordException
 *   only if passed a numeric ID that does not
 *   correspond to a row in the table.
 *
 *   Any method should throw BadQueryException if
 *   an attempted query results in a SQL syntax
 *   error.
 *
 *   Any method should throw BadArgumentException
 *   if it recieves the wrong arguments. (This
 *   is true for any method in your application.)
 */

abstract class Model
{
	// Keep track of which members are associated with 
	// columns in the table
	protected $_columns = array();
	
	// Table relationships
	public $has_one = array();
	public $has_many = array();
	public $belongs_to = array();
	public $has_and_belongs_to_many = array();

	/**
	 * Retrieve row from database and set the columns
	 * as the values of this object.
	 *
	 * @arg integer
	 * @return Model
	 */
	protected function __construct ( $in_id = false )
	{
		// Need database access.
		global $db;
		if ( !$db ) throw new NoConnectionException;
		
		// What's the table name
		$table = strtolower(pluralize(__CLASS__));
		
		// Select the row from the table
		if ( is_int($in_id) || ctype_digit($in_id) ) {
			$_sql = "SELECT * FROM $table WHERE id = '$in_id'";
			if ( $_r = $db->query($_sql) ) {
				if ( $_row = $_r->fetch_assoc() ) {
					foreach ( $_row as $_column => $_value ):
						$this->{$_column} = $_value;
						$this->_columns[] = $_column;
					endforeach;
					
					// Add in support for table relationships
					
					
					return $this;
				} else {
					throw new NoRecordException;
				}
			} else {
				throw new BadQueryException;
			}
		} else {
			return $this;
		}
	}

	/**
	 * Build SQL query and execute to find rows in the
	 * database and return objects
	 *
	 * @author James Socol
	 * @param mixed
	 * @return mixed
	 */
	public static function find ()
	{
		// We need to know the calling class and the
		// table name
		$Class = get_called_class();
		$table = strtolower(pluralize($Class));
		
		// We require a database connection for this...
		// Want to move the SQL generation into a more
		// Agnostic DB wrapper
		global $db;
		if ( !$db ) throw new NoConnectionException();
		
		// Get the function arguments. There must be atleast
		// one
		$_args = func_get_args();
		if ( !$_args[0] ) throw new NoArgumentException;
		
		// Start finding things
		switch ( gettype($_args[0]) ):
			// If the first argument is an integer, just
			// return a new object
			case 'integer':
				return new $Class($_args[0]);
				break;
			case 'string':
				if ( ctype_digit($_args[0]) ) {
					return new $Class($_args[0]);
				}
				
				// If the first argument is not an integer, 
				// it must be either 'first' or 'all'
				switch ( $_args[0] ):
					case 'first':
						$_limit = ' LIMIT 0,1';
						break;
					case 'all':
						$_limit = '';
						break;
				endswitch;
				
				// If there is a second argument, it's either
				// A string (body of WHERE clause) or an array
				// (conditions for WHERE clause)
				if ( $_args[1] ) {
					$_search = $_args[1];
				}
				break;
		endswitch;
		
		// Start building the sql
		$_sql = "SELECT id FROM $table ";
		
		// Build the WHERE clause
		if ( $_search ) {
			switch ( gettype($_search) ):
				// If $_search is a string, treat it as a literal,
				// the body of the WHERE clause
				case 'string':
					$_where = $_search;
					break;
				// If $_search is an array, the key=>value pairs are
				// considered column=>value pairs, compared with 'LIKE' if
				// the value contains'%', and with '=' otherwise. Multiple
				// conditions are joined with AND.
				case 'array':
					$_conds = array();
					foreach($_search as $col => $value):
						$_conds[] = preg_replace('/[^\w\._]+/','',$col).((strpos($value,'%')!==false)?' LIKE ':' = ')."'".$db->real_escape_string($value)."'";
					endforeach;
					$_where = implode(' AND ', $_conds);
					break;
			endswitch;
		}
		
		// build the whole query
		if ( $_where ) $_where = 'WHERE '. $_where;
		$_sql .= $_where . $_limit;
		
		// Do the query
		if ( !$_r = $db->query($_sql) ) throw new BadQueryException;
		
		// If we're returning 'all', we get an array of objects (which
		// may be empty, one, or multiple). If we're returning 'first',
		// we get a single object.
		switch ( $_args[0] ):
			case 'all':
				$_rows = array();
				while ( $_id = $_r->fetch_assoc() ):
					$_rows[] = new $Class($_id['id']);
				endwhile;
				return $_tags;
				break;
			case 'first':
			default:
				if ( $_id = $_r->fetch_assoc() )
					return new $Class($_id['id']);
				break;
		endswitch;
		
		// If there's nothing else left to do... return
		// NULL.
		return NULL;
	}
	
	/**
	 * factory to create an empty object
	 *
	 * @author James Socol
	 * @return Model
	 */
	public static function create ()
	{
		// Get the calling class
		$Class = get_called_class();
		return new $Class;
	}
	
	/**
	 * write the current object to the database
	 *
	 * @author James Socol
	 * @return Model
	 */
	abstract public function save();
	
	/**
	 * delete the associated row from the database
	 *
	 * @author James Socol
	 */
	abstract public function delete();
}

?>
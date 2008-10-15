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
	public $id;

	abstract protected function __construct();

	abstract public static function find();
	abstract public static function create();
	
	abstract public function save();
	abstract public function delete();
}

/**
 * Function get_called_class()
 * 
 * Hack to add get_called_class() to PHP < 5.3
 */
/**
 * Return called class name
 *
 * @author Michael Grenier
 * @param int $i_level optional
 * @return string
 */
if ( !function_exists('get_called_class') ) {
	function get_called_class ($i_level = 1)
	{
		$a_debug = debug_backtrace();
		$a_called = array();
		$a_called_function = $a_debug[$i_level]['function'];
		for ($i = 1, $n = sizeof($a_debug); $i < $n; $i++)
		{
			if (in_array($a_debug[$i]['function'], array('eval')) || 
				strpos($a_debug[$i]['function'], 'eval()') !== false)
				continue;
			if (in_array($a_debug[$i]['function'], array('__call', '__callStatic')))
				$a_called_function = $a_debug[$i]['args'][0];
			if ($a_debug[$i]['function'] == $a_called_function)
				$a_called = $a_debug[$i];
		}
		if (isset($a_called['object']) && isset($a_called['class']))
			return (string)$a_called['class'];
		$i_line = (int)$a_called['line'] - 1;
		$a_lines = explode("\n", file_get_contents($a_called['file']));
		preg_match("#([a-zA-Z0-9_]+){$a_called['type']}
					{$a_called['function']}( )*\(#", $a_lines[$i_line], $a_match);
		unset($a_debug, $a_called, $a_called_function, $i_line, $a_lines);
		if (sizeof($a_match) > 0)
			$s_class = (string)trim($a_match[1]);
		else
			$s_class = (string)$a_called['class'];
		if ($s_class == 'self')
			return get_called_class($i_level + 2);
		return $s_class;
	}
}

?>
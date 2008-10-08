<?php
/**
 * Router
 *
 * Deconstructs URLs into meaningful paths according
 * to the rules defined in /config/routes.php.
 *
 * Uses Router::find() to parse URL. Returns an array of
 * arrays:
 *   Array (
 *     Array ( controller, action ),
 *     Array ( [param, [param ...]] )
 *   )
 * Where the "params" are defined in the routes.php file.
 */

class Router
{
	public static $shorthand = array(
		':controller' => '([a-zA-Z_][\w_]+)',
		':action'     => '([a-zA-Z_][\w_]+)',
		':id'         => '(\d+)',
		':format'     => '([\w]+)'
	);

	public static function find ( $route )
	{
		global $routes, $base_url;
		$route = preg_replace('#'.$base_url.'\/?#', '', $route, 1);
		
		foreach ( $routes as $_url => $_route ) {
			$_map = array_shift($_route);
			
			if ( $_url == '_default' ) {
				$_map = explode('/', $_map);
				$controller = ucfirst($_map[0]).'Controller';
				if ( !class_exists($controller) ) throw new NoControllerException($controller);
				if ( !in_array($_map[1], get_class_methods($controller)) ) throw new NoActionException($_map[1]);
				return array($_map, array());
			}

			$_url = str_replace(array_keys(self::$shorthand),
				array_values(self::$shorthand),
				$_url);

			if ( !preg_match('#^'.$_url.'$#', $route, $_values) ) {
				continue;
			}
			
			array_shift($_values);
			
			$_map = explode('/', $_map);
			
			foreach ( $_map as &$_p ) {
				if ( preg_match('#\$(\d+)#', $_p, $_v) ) {
					$_p = str_replace('$'.$_v[1], $_values[$_v[1]-1], $_p);
					unset($_values[$_v[1]-1]);
				}
			}
			
			if ( !$_map[1] ) $_map[1] = 'index';
			
			$controller = ucfirst($_map[0]).'Controller';
			
			if ( !class_exists($controller) ) throw new NoControllerException($controller);
			
			if ( !in_array($_map[1], get_class_methods($controller)) ) throw new NoActionException($_map[1]);

			$_values = array_values($_values);
			$_maxk = count($_route);
			$_params = array();
			for ( $j = 0; $j < $_maxk; $j++ ) {
				$_params[$_route[$j]] =  $_values[$j];
			}
			
			return array($_map, $_params);
		}
	}
}

?>

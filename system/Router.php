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

/**
 * Maps URLs to controllers and actions.
 * 
 * Use Router::map() to create a new route. See config/routes.php
 * for examples and help.
 *
 * @author James Socol
 * @since 0.1
 * @package Maveric
 */
class Router
{
	/**
	 * Shortcuts allowed in routes.
	 * @since 0.1
	 */
	public static $shorthand = array(
		':controller' => '([a-zA-Z_][a-zA-Z0-9_]+)',
		':action'     => '([a-zA-Z_][a-zA-Z0-9_]+)',
		':id'         => '(\d+)',
		':format'     => '([\w]+)'
	);

	/**
	 * Mapped routes.
	 * @since 0.2
	 */
	public static $routes = array();
	
	/**
	 * Creates a new route.
	 * 
	 * @since 0.2
	 * @param string $route the new route to map
	 * @param array $options the details of this new route (optional)
	 */
 	public static function map ( $route, Array $options = NULL)
 	{
 		Router::$routes[] = array($route,$options);
 	}

	/**
	 * Determines a route from a URL.
	 * 
	 * This method is expensive. It is intended only to be
	 * called at the start of the request.
	 * 
	 * @param string $url the URL to interpret
	 * @return ???
	 */
	public static function find ( $url )
	{
		// Debug the found URL
		Log::log("Routing URL: $url", MAVERIC_E_MESSAGE);
		
		// Get the parts of the current URL:
		$current = explode('/',$url);
		
		// Loop through the defined routes
		foreach ( Router::$routes as $route ) {
			// Look at the individual parts
			$location = explode('/',$route[0]);
			
			foreach ($location as $part) {
				
			}
		}
	}
}


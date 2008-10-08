<?php

// Where does the Maveric install live on the server?
// ie: /path/to/maveric
$base_url      = ''; // No trailing slash!

/**
 * Routes
 *
 * Routes are defined with regular expressions (for now), like so:
 * 
 *   $routes[ regular expression ] 
 *      = array('controller/action', [param, [param ... ]]);
 * 
 * You do not need to escape forward slashes (/).
 *
 * For example, to map the url /help to the "help" action of the
 * DefaultController, you would use...
 *
 *   $routes['help/?'] = array('default/help');
 *
 * If you want to make the controller or action dynamic, you can
 * use PCRE variables (ie: $1, $2, $3, etc). For example, if you
 * wanted the URL format /controller/action, you could do:
 *
 *   $routes['([a-zA-Z_][\w_]+)/([a-zA-Z_][\w_]+)']
 *     = array('$1/$2');
 *
 * You can also use certain shorthand notation to make your routes
 * more readable. Currently, :controller, :action, :id, and :format.
 * We can make the above example much cleaner:
 *
 *   $routes[':controller/:action'] = array('$1/$2');
 *
 * You can define additional parameters, which will be available 
 * in the $this->params array inside your controller. For example,
 * to map the url /controller/action/id (where ID is a number):
 *
 *   $routes[':controller/:action/:id'] = array('$1/$2', 'id');
 *
 * So visiting /user/view/12 would execute UserController->view()
 * and the params array would include $params['id'] = 12.
 *
 * You don't have to use this standard order. For instance, you could
 * map /<username>/action.format to the UserController like so:
 *
 *   $routes['([\w]+)/:action.:format']
 *     = array('user/$2', 'username', 'format');
 *
 * The default action for any controller is "index", so if you no 
 * action is found, Maveric will try to execute the "index" method of 
 * the controller.
 *
 * Finally, you can define a default route, if no other route matches.
 * The route should be "_default" and it will map to whatever
 * controller and action you define without setting any parameters.
 * This can be useful for 404 pages or mapping to your home page.
 *
 * Routes are processed in order, so routes defined first have higher
 * priority. Make sure the _default route is last.
 */
$routes = array();

/** Generic Routes : **/

// /controller/action/id.format
$routes[':controller/:action/:id.:format'] = array('$1/$2', 'id', 'format');

// /controller/action/id (optional trailing slash)
$routes[':controller/:action/:id/?'] = array('$1/$2', 'id');

// /controller/action.format
$routes[':controller/:action.:format'] = array('$1/$2', 'format');

// /controller/action (optional trailing slash)
$routes[':controller/:action/?'] = array('$1/$2');

// /controller (optional trailing slash, defaults to "index" method)
$routes[':controller/?' = array('$1/index');

/** Route for nothing, ie: $base_url/:
 * Goes to DefaultController->index()
 */
$routes[''] = array('default/index');

/** Set a default controller and action; 
 * Goes to DefaultController->fourohfour 
 */
$routes['_default'] = array('default/fourohfour');

?>

<?php

/* -----------------
FW4 FRAMEWORK - PAGE
--------------------

The page class should be subclassed by the developer to create content pages. Create a subclass for each module (news,blog,contact,services,...) instead of each seperate page. */

class Controller {

	private $routes = array();
	
	public function index() {
		return false;
	}
	
	public function add_route($route) {
		$route->set_classname(self::get_name());
		$this->routes[] = $route;
	}
	
	public function call_function($function_name,$parameters) {
		if (!method_exists($this,$function_name)) return false;
		
		$reflector = new ReflectionClass($this);
		if (count($parameters) < $reflector->getMethod($function_name)->getNumberOfRequiredParameters()) return false;
		
		$collapse_parameters = false;
		$function_parameters = $reflector->getMethod($function_name)->getParameters();
		if (count($function_parameters) && end($function_parameters)->name == 'parameters') $collapse_parameters = true;
		
		if (count($parameters) > count($function_parameters) && !$collapse_parameters) return false;
		
		if ($collapse_parameters) {
			$non_optional = array_splice($parameters,0,count($function_parameters)-1);
			if (count($parameters)) $parameters = array_merge($non_optional,array($parameters));
			else $parameters = $non_optional;
		}
		return call_user_func_array(array($this,$function_name),$parameters);
	}
	
	public function get_routes() { return $this->routes; }
    
    private static function get_name() {
    	return get_called_class();
    }
	
}

function use_library($name) {
	if (file_exists(BASEPATH.'libraries/'.$name.'.php')) include_once(BASEPATH.'libraries/'.$name.'.php');
	else if (file_exists(BASEPATH.'libraries/'.$name.'/'.$name.'.php')) include_once(BASEPATH.'libraries/'.$name.'/'.$name.'.php');
}

function cron_allowed() {
	$user = FW4_User::get_user();
    return ($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'] || $user['id'] === 0);
}

function load_controller($name) {
	$args = func_get_args();
	$name = array_shift($args);
	if (!class_exists(ucfirst($name))) {
		$content_path = View_Loader::get_instance()->get_path();
		if (file_exists($content_path.'/'.$name.'.php')) include($content_path.'/'.$name.'.php');
		else return false;
	}
	if (!class_exists(ucfirst($name))) return false;
	$classObj = new ReflectionClass(ucfirst($name));
    return $classObj->newInstanceArgs($args);
}
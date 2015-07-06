<?php

/* -----------------
FW4 FRAMEWORK - ROUTE
-------------------- */

define('ROUTE_EARLY', 1);
define('ROUTE_DEFAULT', 2);
define('ROUTE_LATE', 3);

class Route {
	
	private $routes = array();
	
	private $slug = '';
	private $full_slug = '';
	private $title = '';
	private $classname = '';
	private $contentname = '';
	private $function = '';
	private $priority = ROUTE_DEFAULT;
	private $parent = false;
	
	public function __construct($slug='',$function='index',$priority=ROUTE_DEFAULT,$title='') {
		$this->slug = $slug;
		$this->title = $title;
		$this->function = $function;
		$this->priority = $priority;
	}
    
    public function add_route($route) { $this->routes[] = $route; }
    public function get_routes() { return $this->routes; }
    public function get_slug() { return $this->slug; }
    public function get_full_slug() { return $this->full_slug; }
    public function get_title() { return $this->title; }
    public function get_classname() { return $this->classname; }
    public function get_contentname() { return $this->contentname?$this->contentname:strtolower($this->classname); }
    public function get_function() { return $this->function; }
    public function get_priority() { return $this->priority; }
    public function get_parent() { return $this->parent; }
    
    public function set_priority($priority) { $this->priority = $priority; }
    
    public function set_classname($classname) {
	    if (!$this->full_slug) $this->full_slug = $this->slug;
    	foreach ($this->routes as &$route) {
    		$route->parent = $this;
    		$route->full_slug = $this->full_slug.'/'.$route->slug;
    		$route->set_classname($classname);
    	}
    	$this->classname = $classname;
    }
    
    public function set_contentname($contentname) {
    	foreach ($this->routes as &$route) {
    		$route->set_contentname($contentname);
    	}
    	$this->contentname = $contentname;
    }
	
}
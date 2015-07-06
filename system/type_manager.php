<?php

/* -------------------------
FW4 FRAMEWORK - TYPE MANAGER
----------------------------

The type manager and type classes allow the addition of new datatypes and admin plugins. */

require_once('type.php');

class FW4_Type_Manager {

	protected static $instance = NULL;
	
	protected $types = array();
	protected $types_loaded = false;

    public static function get_instance() {
    	if (self::$instance === NULL) {
    		self::$instance = new FW4_Type_Manager();
    	}
    	return self::$instance;
    }
    
    public function get_types() {
    	if ($this->types_loaded) return $this->types;
    	
    	if ($handle = opendir(BASEPATH.'types/')) {
    	
    	    while (false !== ($file = readdir($handle))) {
    	        $path_info = pathinfo($file);
    	        if ($path_info['extension'] == "php") {
    	        	if (!isset($this->types[$path_info['filename']])) {
	    	        	include(BASEPATH.'types/'.$file);
	    	        	
	    	        	$classname = ucfirst($path_info['filename']).'_Type';
	    	        	if (!class_exists($classname)) $classname = ucfirst($path_info['filename']);
	    	        	
	    	        	$this->types[$path_info['filename']] = new $classname();
	    	        }
    	        }
    	    }
    	
    	    closedir($handle);
    	}
    	
    	$types_loaded = true;
    	
    	return $this->types;
    }
    
    public function get_type($name) {
    	if (isset($this->types[$name])) return $this->types[$name];
    	
    	if (file_exists(BASEPATH.'types/'.$name.'.php')) {
	    	include(BASEPATH.'types/'.$name.'.php');
        	$classname = ucfirst($name).'_Type';
        	if (!class_exists($classname)) $classname = ucfirst($name);
        	$this->types[$name] = new $classname();
        	
        	return $this->types[$name];
    	}
    	
    	return false;
    }
    
}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View_Loader {

    protected static $instance = NULL;
    	
	protected $data = NULL;
	protected $page = NULL;
		
	protected $pathname = NULL;

    public static function get_instance() {
    	if (self::$instance === NULL) {
    		self::$instance = new View_Loader();
    	}
    	return self::$instance;
    }
    
    public function load($viewname,$_vars=array()) {
		foreach ($_vars as $_key => $_value) {
			$$_key = htmlentities_all($_value,true);
			if ($_key == 'title') Router::$document_title = $$_key;
		}
		$parameters = $_vars;
		unset($_vars);
		
		ob_start();
		
		if (strstr($viewname,'/')) {
		
			$viewpath = explode('/',$viewname);
			
			if (file_exists(CONTENTPATH.Router::get_content_prefix().strtolower($viewpath[0]).'/views/'.strtolower($viewpath[1]).".php")) include(CONTENTPATH.Router::get_content_prefix().strtolower($viewpath[0]).'/views/'.strtolower($viewpath[1]).".php");
			else if (file_exists($this->pathname.'/views/'.strtolower($viewname).".php")) include($this->pathname.'/views/'.strtolower($viewname).".php");
			else if (strlen(Router::get_content_prefix()) > 1 && file_exists(CONTENTPATH.Router::get_content_prefix().'views/'.strtolower($viewname).".php")) include(CONTENTPATH.Router::get_content_prefix().'views/'.strtolower($viewname).".php");
			else if (file_exists(VIEWSPATH.strtolower($viewname).".php")) include(VIEWSPATH.strtolower($viewname).".php");
			
		} else {
		
			if (file_exists($this->pathname.'/views/'.strtolower($viewname).".php")) include($this->pathname.'/views/'.strtolower($viewname).".php");
			else if (strlen(Router::get_content_prefix()) > 1 && file_exists(CONTENTPATH.Router::get_content_prefix().'views/'.strtolower($viewname).".php")) include(CONTENTPATH.Router::get_content_prefix().'views/'.strtolower($viewname).".php");
			else if (file_exists(VIEWSPATH.strtolower($viewname).".php")) include(VIEWSPATH.strtolower($viewname).".php");
			
		}
		
		$result = ob_get_contents();
		
		ob_end_clean();
		
		return $result;
    }
    
    public function set_path($pathname) {
	    $this->pathname = $pathname;
    }
    
    public function get_path() {
	    return $this->pathname;
    }

}

function view($viewname,$vars=array()) {
	return View_Loader::get_instance()->load($viewname,$vars);
}
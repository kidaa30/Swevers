<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Export_Type extends FW4_Type {

    public function print_field($field,$data,$object) {
    }
    
    public function export($data,$field,$object) {
		$classname = ucfirst($object['contentname']);
		$function_name = 'export_'.strval($field['name']);
		if (class_exists($classname) && method_exists($classname,$function_name)) {
			return call_user_func_array($classname.'::'.$function_name, array($data,$field,$object));
		}
		return '';
    }
    
    function get_structure($field,$fields) {
    	return '<structure></structure>';
    }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
		return $data;
	}

}
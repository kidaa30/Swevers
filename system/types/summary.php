<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Summary_type extends FW4_Type {

    public function print_field($field,$data,$object) {}
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		return $data;
	}
	
	public function summary($field,$data,$object) {
		$classname = ucfirst($object['contentname']);
		$function_name = 'summary_'.strval($field['name']);
		if (class_exists($classname) && method_exists($classname,$function_name)) {
			return call_user_func_array($classname.'::'.$function_name, array($data));
		}
		return '';
	}
    
    public function get_structure($field,$fields) {
	    return '<structure></structure>';
    }

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Createdate extends FW4_Type {

    public function print_field($field,$data,$object) {}
    
    public function edit($data,$field,$newdata,$olddata,$object) {
		if (!isset($data['id'])) $data[strval($field['name'])] = time();
		return $data;
	}
    
    function get_structure($field,$fields) {
    	return '<structure><date name="'.$field['name'].'"/></structure>';
    }

}
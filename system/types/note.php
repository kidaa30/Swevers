<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Note extends FW4_Type {

    public function print_field($field,$data,$object) { 
	    if (!isset($field['create_only']) || !isset($data['id'])) {
	    	echo '<div class="usernote">'.strval($field).'</div>';
	    }
    }
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		return $data;
	}
    
    public function get_structure($field,$fields) {
	    return '<structure></structure>';
    }

}
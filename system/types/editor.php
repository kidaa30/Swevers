<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Editor extends FW4_Type {

    public function print_field($field, $data, $object) { }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
		$user = FW4_User::get_user();
    	$data[strval($field['name'])] = $user->id;
		return $data;
	}
    
    function get_structure($field,$fields) {
	    return '<structure>
	    	<number name="'.$field['name'].'" length="10"/>
	    </structure>';
    }

}
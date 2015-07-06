<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Creator extends FW4_Type {

    public function print_field($field,$data,$object) { }
    
    public function insert($data,$field,$newdata,$olddata,$object) {
    	$user = FW4_User::get_user();
    	$data[strval($field['name']).'_id'] = $user->id;
		return $data;
	}
	
	public function update($data,$field,$newdata,$olddata,$object) {
		return $data;
	}
    
    function get_structure($field,$fields) {
    	$sortable = isset($field['sortable'])&&$field['sortable']?true:false;
	    return '<structure>
	    	<number name="'.$field['name'].'_id" length="10"/>
	    	<dbrelation name="'.$field['name'].'" source="users/user" local_key="'.$field['name'].'_id" foreign_key="id"/>
	    </structure>';
    }

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Header extends FW4_Type {

    public function print_field($field,$data,$object) { 
    	$user = FW4_User::get_user();
	    if (!isset($data->id)) {
	    	$has_siblings = false;
		    foreach ($field->xpath('following-sibling::*') as $sibling) {
		    	if ($sibling->getName() == 'header') break;
				if (isset($sibling['require']) && $user->id !== 0) {
					$require_fields = explode('.',$sibling['require']);
					$require_field = $user;
					foreach ($require_fields as $current_field) {
						if (isset($require_field[$current_field]) && $require_field[$current_field]) $require_field = $require_field[$current_field];
						else {
							$require_field = false;
							break;
						}
					}
					if (!$require_field) continue;
				}
			    if ($sibling->getName() != 'object' && $sibling->getName() != 'recursive' && $sibling->getName() != 'slug' && $sibling->getName() != 'family') $has_siblings = true;
		    }
		    if (!$has_siblings) return false;
	    } else {
		    $has_siblings = false;
		    foreach ($field->xpath('following-sibling::*') as $sibling) {
		    	if ($sibling->getName() == 'header') break;
				if (isset($sibling['require']) && $user->id !== 0) {
					$require_fields = explode('.',$sibling['require']);
					$require_field = $user;
					foreach ($require_fields as $current_field) {
						if (isset($require_field[$current_field]) && $require_field[$current_field]) $require_field = $require_field[$current_field];
						else {
							$require_field = false;
							break;
						}
					}
					if (!$require_field) continue;
				}
				if (isset($object['is_version']) && $sibling->getName() == 'object') continue;
				if ($sibling->getName() == 'recursive' && ((isset($sibling['levels']) && FW4_Admin::$recursive_levels >= $sibling['levels']) ||  isset($object['editing_disabled'])) ) continue; 
				if ($sibling->getName() == 'family' && isset($object['editing_disabled'])) continue;
			    if ($sibling->getName() != 'slug' && $sibling->getName() != 'creator' && $sibling->getName() != 'export') $has_siblings = true;
		    }
		    if (!$has_siblings) return false;
	    }
	    FW4_Admin::$has_headers = true; ?>
    	<h2><?=strval($field)?></h2><?
    }
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		return $data;
	}
    
    public function get_structure($field,$fields) {
	    return '<structure></structure>';
    }

}
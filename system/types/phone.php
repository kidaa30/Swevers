<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Phone extends FW4_Type {

    public function print_field($field,$data,$object) {
    	$fieldname = strval($field['name']); 
	    if ((isset($field['readonly']) && isset($data->id)) || isset($object['editing_disabled'])): ?>
			<div class="<?=(FW4_Admin::$in_fieldset?'field':'input')?>">
	    		<label class="for-input<?=isset($field['invalid'])&&$field['invalid']?' invalid':''?>" for="<?=$field['name']?>"<?=(isset($field['invalid']) && $field['invalid']?' class="invalid"':'')?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> 
	    		<div class="value"><?=(isset($data->$fieldname)?phone_format($data->$fieldname):'-')?></div>
	    	</div>
		<? else: ?>
	    	<div class="<?=(FW4_Admin::$in_fieldset?'field':'input')?>">
	    		<label class="for-input<?=isset($field['invalid'])&&$field['invalid']?' invalid':''?>" for="<?=$field['name']?>"<?=(isset($field['invalid']) && $field['invalid']?' class="invalid"':'')?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> 
	    		<input class="phone<?=(isset($field['required']) && $field['required']?' required':'')?>" type="text" name="<?=$field['name']?>" value="<?=(isset($data->$fieldname)?phone_format($data->$fieldname):'')?>" maxlength="20" />
	    	</div><?
	    endif;
    }
    
    function get_structure($field,$fields) {
    	return '<structure><string name="'.$field['name'].'" length="20"/></structure>';
    }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
    	$fieldname = strval($field['name']);
		$data[$fieldname] = $newdata[$fieldname];
		return $data;
	}
	
	public function export($data,$field) {
		$fieldname = strval($field['name']);
		return phone_format($data->$fieldname);
	}

}
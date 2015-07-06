<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tax extends FW4_Type {

    public function print_field($field,$data,$object) { ?>
    	<div class="input">
    		<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?> for="<?=$field['name']?>"<?=(isset($field['invalid']) && $field['invalid']?' class="invalid"':'')?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> 
    		<input class="tax<?=(isset($field['required']) && $field['required']?' required':'')?>" type="text" name="<?=$field['name']?>" value="<?=(isset($data[strval($field['name'])])?$data[strval($field['name'])]:'')?>" maxlength="20" />
    	</div><?
    }
    
    function get_structure($field,$fields) {
    	return '<structure><string name="'.$field['name'].'" length="20"/></structure>';
    }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
		$data[strval($field['name'])] = $this->format_number($newdata[strval($field['name'])]);
		return $data;
	}
    
    private function format_number($string) {
    	$string = preg_replace('/\W/is','',$string);
    	if (!preg_match('/^[a-zA-Z]+/is',$string)) $string = 'BE'.$string;
    	if (strlen($string) < 5) return '';
		return substr($string,0,2).' '.substr($string,2,4).'.'.substr($string,6,3).'.'.substr($string,9);
	}

}
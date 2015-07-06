<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Color extends FW4_Type {

    public function print_field($field,$data,$object) { ?>
    	<div class="input">
    		<fieldset>
    			<input class="colorpicker<?=(isset($field['required']) && $field['required']?' required':'')?>" type="text" name="<?=$field['name']?>" value="<?=(isset($data[strval($field['name'])])?$data[strval($field['name'])]:'')?>" />
    			<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?> for="input-<?=$field['name']?>"<?=(isset($field['invalid']) && $field['invalid']?' class="invalid"':'')?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
    		</fieldset>
    	</div><?
    }
    
    function get_structure($field,$fields) {
    	return '<structure><string name="'.$field['name'].'" length="6"/></structure>';
    }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
		$data[strval($field['name'])] = preg_replace('/[^0-9abcdef]/is','',$newdata[strval($field['name'])]);
		return $data;
	}
    
    function get_scripts() { 
	    return utf8_encode('<script src="'.url(ADMINRESOURCES.'js/spectrum.js').'"></script>
		<script>
			$(function(){
				$("input.colorpicker").spectrum({
				    showInput: true,
				    preferredFormat: "hex",
				    chooseText: "Kies",
					cancelText: "Annuleer"
				});
			});
		</script>');
    }

}
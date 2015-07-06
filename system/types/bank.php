<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bank_Type extends FW4_Type {

    public function print_field($field,$data,$object) { 
	    if ((isset($field['readonly']) && isset($data['id'])) || isset($object['editing_disabled'])): ?>
			<div class="input">
	    		<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?> for="<?=$field['name']?>"<?=(isset($field['invalid']) && $field['invalid']?' class="invalid"':'')?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> 
	    		<div class="value"><?=(isset($data[strval($field['name'])])&&$data[strval($field['name'])]?$data[strval($field['name'])]:'-')?></div>
	    	</div>
		<? else: ?>
	    	<div class="input">
	    		<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?> for="<?=$field['name']?>"<?=(isset($field['invalid']) && $field['invalid']?' class="invalid"':'')?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> 
	    		<input class="bank<?=(isset($field['required']) && $field['required']?' required':'')?>" type="text" name="<?=$field['name']?>" value="<?=(isset($data[strval($field['name'])])?$data[strval($field['name'])]:'')?>" maxlength="40" />
	    	</div><?
	    endif;
    }
    
    function get_structure($field,$fields) {
    	return '<structure><string name="'.$field['name'].'" length="40"'.(isset($field['filterable'])?' filterable="filterable"':'').'/></structure>';
    }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
		$data[strval($field['name'])] = $newdata[strval($field['name'])];
		return $data;
	}
	
	public function export($data,$field) {
		return $data[strval($field['name'])];
	}
    
    function get_scripts() { 
	    return utf8_encode('<script>
			$(function(){
				$("input.bank").blur(function(){
					var value = $(this).val().replace(/\W/g,"");
					if (matches = value.match(/^([a-z]{2})(.{2})(.{4})(.{0,4})(.{0,4})(.{0,4})(.{0,4})(.{0,4})(.{0,4})(.{0,4})/i)) {
						matches.shift();
						var newValue = matches.shift()+matches.shift();
						while (k = matches.shift()) {
							if (k.length > 0) newValue += " "+k;
						}
						$(this).val(newValue);
					} else if (matches = value.match(/^(\d{3})(\d{7})(\d{2})$/i)) {
						$(this).val(matches[1]+"-"+matches[2]+"-"+matches[3]);
					}
				});
			});
		</script>');
    }

}
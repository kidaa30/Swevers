<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Language extends FW4_Type {

    public function print_field($field,$data,$object) { 
	    $fieldname = strval($field['name']); ?>
    	<div class="input">
	    	<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    	<? if ((isset($field['readonly']) && isset($data->id)) || isset($object['editing_disabled'])): ?>
		    	<div class="value">
		    		<? $languages = languages(); ?>
		    		<?=isset($languages[$data->$fieldname])?$languages[$data->$fieldname]:'-'?>
		    	</div>
	    	<? else: ?>
		    	<select name="<?=$field['name']?>">
		    		<? if (!isset($field['required'])): ?><option value=""></option><? endif; ?>
			    	<? foreach (languages() as $code => $language): ?>
				    	<option value="<?=$code?>"<?=isset($data->$fieldname)&&$code==$data->$fieldname?' selected="selected"':''?>><?=$language?></option>
			    	<? endforeach; ?>
		    	</select>
		    <? endif; ?>
    	</div><?
    }
    
    function get_structure($field,$fields) {
    	return '<structure><string name="'.$field['name'].'" length="2"/></structure>';
    }
    
    public function summary($field,$data,$object) {
    	$languages = languages();
    	$fieldname = strval($field['name']);
    	if (isset($languages[$data->$fieldname])) return $languages[$data->$fieldname];
	    return '';
    }

}
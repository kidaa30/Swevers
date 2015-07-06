<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Year_Type extends FW4_Type {

    public function print_field($field,$data,$object) { 
	    $years = array();
	    for ($i = 2000; $i < date('Y')+5; $i++) $years[] = $i; ?>
    	<div class="input">
	    	<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    	<? if ((isset($field['readonly']) && isset($data['id'])) || isset($object['editing_disabled'])): ?>
		    	<div class="value"><?=$data[strval($field['name'])]?></div>
	    	<? else: ?>
		    	<select name="<?=$field['name']?>">
		    		<? if (!isset($data[strval($field['name'])]) && isset($field['required'])) $data[strval($field['name'])] = date('Y'); ?>
		    		<? if (!isset($field['required'])): ?><option value=""></option><? endif; ?>
			    	<? foreach ($years as $year): ?>
				    	<option value="<?=$year?>"<?=$year==$data[strval($field['name'])]?' selected="selected"':''?>><?=$year?></option>
			    	<? endforeach; ?>
		    	</select>
		    <? endif; ?>
    	</div><?
    }
    
    function get_structure($field,$fields) {
    	return '<structure><number name="'.$field['name'].'" length="4" index="index"/></structure>';
    }
    
    public function summary($field,$data,$object) {
    	if (isset($data[strval($field['name'])])) return $data[strval($field['name'])];
	    return '';
    }

}
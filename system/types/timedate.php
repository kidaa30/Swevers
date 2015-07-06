<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timedate extends FW4_Type {

    public function print_field($field,$data,$object) {  
    
    	if (isset($field['defaulttoday']) && $field['defaulttoday'] && !$value && !isset($data['id'])) $value = time(); ?>
    	
		<div class="input"><label for="<?=$fieldname?>"<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$fieldname))?></label>
			<input type="text" name="<?=$fieldname?>" style="width:140px" class="datetime" size="20" value="<?=is_numeric($value)&&$value>0?date('d/m/Y H:i',$value):($value&&!is_numeric($value)?$value:'')?>"/>
		</div>
		
    <? }
    
    public function insert($data, $field, $newdata, $olddata, $object) {
    	if (isset($data[$fieldname])){
    		if (!$data[$fieldname])  {
    			$data[$fieldname] = 0;
    		} else {
    			list($date,$time) = explode(" ", trim($data[$fieldname]));
	    		list($d, $m, $y) = explode("/", $date);
	    		list($h, $i) = explode(":", $time);
	    		$data[$fieldname] = mktime($h,$i,0,$m,$d,$y);
	    	}
    	}
    	return $data;
    }
    
    public function update($fieldname,$data,$tablename,$id,$orig_data) {
    	if (isset($data[$fieldname])){
    		if (!$data[$fieldname])  {
    			$data[$fieldname] = 0;
    		} else {
	    		list($date,$time) = explode(" ", trim($data[$fieldname]));
	    		list($d, $m, $y) = explode("/", $date);
	    		list($h, $i) = explode(":", $time);
	    		$data[$fieldname] = mktime($h,$i,0,$m,$d,$y);
	    	}
    	}
    	return $data;
    }
    
    function get_structure($field,$fields) {
    	$xml = '<structure>';
		$xml .= '<number name="'.$field['name'].'"'.(isset($field['default']) ? ' default="'.$field['default'].'"' : '').(isset($field['index']) ? ' index="'.$field['index'].'"' : '').'/>';
    	$xml .= '</structure>';
    	return $xml;
    }

}
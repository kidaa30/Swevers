<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Price extends FW4_Type {

    public function print_field($field,$data,$object) { 
    	$fieldname = strval($field['name']); ?>
		<div class="input"><label for="<?=$field['name']?>"<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
			<? if ((isset($field['readonly']) && isset($data->id)) || isset($object['editing_disabled'])): ?>
				<div class="value"><?=$data->$fieldname?'&euro; '.number_format($data->$fieldname,2,',','.'):'-'?></div>
			<? else: ?>
				<input type="text" name="<?=$field['name']?>" class="price_input" maxlength="25" value="<?=isset($data->$fieldname)&&$data->$fieldname?'&euro; '.number_format($data->$fieldname,2,',','.'):''?>"/>
			<? endif;?>
		</div>
    <? }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
    	$fieldname = strval($field['name']);
		if ($newdata[$fieldname] === '') $data[$fieldname] = null;
		else $data[$fieldname] = str_replace(',','.', preg_replace('/[^0-9\,]/s','', $newdata[$fieldname]));
		return $data;
	}
    
    function get_structure($field,$fields) {
    	$fieldname = strval($field['name']);
    	return '<structure><float name="'.$fieldname.'"'.(isset($field['index']) ? ' index="'.$field['index'].'"' : '').' length="20"/></structure>';
    }
	
	public function summary($field,$data,$object) {
		$fieldname = strval($field['name']);
		return '&euro;&nbsp;'.number_format($data->$fieldname,2,',','.');
	}
	
	public function export($data,$field) {
    	$fieldname = strval($field['name']);
		return rtrim(rtrim(number_format($data->$fieldname,2,',','.'),'0'),',');
    }
    
    function get_scripts() { 
	    return utf8_encode('<script src="'.url(ADMINRESOURCES.'js/jquery.formatCurrency-1.4.0.min.js').'"></script>
		<script>
			$(function(){
				$("input.price_input").blur(function(){
					$(this).formatCurrency({
						decimalSymbol: ",",
						digitGroupSymbol: ".",
						positiveFormat: "%s %n",
						symbol: euroSign
					});
				});
			});
		</script>');
    }

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Url extends FW4_Type {

    public function print_field($field,$data,$object) { 
    	$fieldname = strval($field['name']); ?>
    	<div class="<?=(FW4_Admin::$in_fieldset?'field':'input')?>">
    		
    		<label class="for-input<?=isset($field['invalid']) && $field['invalid']?' invalid':''?>"><?=$field['label']?></label>
    		<? if ((isset($field['readonly']) && isset($data->id)) || isset($object['editing_disabled'])): ?>
	    		<div class="value"><? if (isset($data->$fieldname) && $data->$fieldname):?><a href="<?=$data->$fieldname?>" target="_blank"><?=$data->$fieldname?></a><? else: ?>-<? endif; ?></div>
			<? else: ?>
			    <? if (isset($field['translatable']) && $field['translatable']): 
				    foreach (languages() as $key => $lang): 
				    	$field_lang = strval($field['name']).'_'.$key; ?>
						<div class="language"><input type="text" class="with_lang_label lowmargin<?=isset($field['required']) && $field['required']?' required':''?>" name="<?=$field['name'].'_'.$key?>" value="<?=isset($data->$field_lang)?str_replace('"','&quot;',$data->$field_lang):''?>" maxlength="250" /><span class="lang_label"><?=strtoupper($key)?></span></div>
					<? endforeach; ?>
			    <? else: ?>
			    	<input class="phone<?=(isset($field['required']) && $field['required']?' required':'')?>" type="text" name="<?=$field['name']?>" value="<?=(isset($data->$fieldname)?$data->$fieldname:'')?>" maxlength="250" />
			    <? endif; ?>
			<? endif; ?>
    	</div><?
    }
    
    function get_structure($field,$fields) {
    	$xml = '<structure>';
    	if (isset($field['translatable']) && $field['translatable']) {
			$xml .= '<string name="'.$field['name'].'" length="250" translatable="true"/>';
		} else {
			$xml .= '<string name="'.$field['name'].'" length="250"/>';
		}
    	$xml .= '</structure>';
    	return $xml;
    }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
    	if (isset($field['translatable']) && $field['translatable']) {
		    foreach (languages() as $key => $lang) {
			    $data[strval($field['name']).'_'.$key] = $this->correctURL($newdata[strval($field['name']).'_'.$key]);
			}
		} else {
		   $data[strval($field['name'])] = $this->correctURL($newdata[strval($field['name'])]);
		}
		return $data;
	}
	
	public function summary($field,$data,$object) {
		$fieldname = strval($field['name']);
		return $data->$fieldname;
	}
	
	public function export($data,$field) {
		$fieldname = strval($field['name']);
		return $data->$fieldname;
	}
	
	private function correctURL($address) {
	    if (!empty($address) && $address{0} != '#' && strpos(strtolower($address), 'mailto:') === FALSE && strpos(strtolower($address), 'javascript:') === FALSE) {
	        $address = explode('/', $address);
	        $keys = array_keys($address, '..');
	
	        foreach($keys AS $keypos => $key) array_splice($address, $key - ($keypos * 2 + 1), 2);
	
	        $address = implode('/', $address);
	        $address = str_replace('./', '', $address);
	        
	        $scheme = parse_url($address);
	        
	        if (empty($scheme['scheme'])) $address = 'http://' . $address;
	
	        $parts = parse_url($address);
	        $address = strtolower($parts['scheme']) . '://';
	
	        if (!empty($parts['user'])) {
	            $address .= $parts['user'];
	
	            if (!empty($parts['pass'])) $address .= ':' . $parts['pass'];
	
	            $address .= '@';
	        }
	
	        if (!empty($parts['host'])) {
	            $host = str_replace(',', '.', strtolower($parts['host']));
	
	            if (strpos(ltrim($host, 'www.'), '.') === FALSE) $host .= '.com';
	
	            $address .= $host;
	        }
	
	        if (!empty($parts['port'])) $address .= ':' . $parts['port'];
	
	        $address .= '/';
	
	        if (!empty($parts['path'])) {
	            $path = trim($parts['path'], ' /\\');
	
	            if (!empty($path) AND strpos($path, '.') === FALSE) $path .= '/';
	                
	            $address .= $path;
	        }
	
	        if (!empty($parts['query'])) $address .= '?' . $parts['query'];
	        if (!empty($parts['fragment'])) $address .= '#' . $parts['fragment'];
	
	        return $address;
	        
	    } else return '';
	}

}
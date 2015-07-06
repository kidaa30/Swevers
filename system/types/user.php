<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends FW4_Type {

    public function print_field($field,$data,$object) { 
	    $value = 0;
	    if (isset($data[strval($field['name'])])) $value = $data[strval($field['name'])];
	    else if (isset($field['default_self'])) {
		    $user = FW4_User::get_user();
		    $value = $user['id'];
	    }
	    
	    $user = FW4_User::get_user();
		if (isset($field['limit']) && $user['id'] != 0) {
			$dolimit = true;
			if (isset($field['limit_condition'])) {
				$invert = false;
				if (substr($field['limit_condition'],0,1) == '!') {
					$invert = true;
					$field['limit_condition'] = substr($field['limit_condition'],1);
				}
				$limit_fields = explode('.',$field['limit_condition']);
				$limit_field = $user;
				foreach ($limit_fields as $current_field) {
					if (isset($limit_field[$current_field])) $limit_field = $limit_field[$current_field];
					else if (isset($limit_field[$current_field])) {
						$limit_field = false;
						break;
					} else {
						$limit_field = true;
						break;
					}
				}
				$dolimit = $invert?!$limit_field:$limit_field;
			}
			
			if ($dolimit) {
				$limit_fields = explode('.',$field['limit']);
				$limit_field = $user;
				foreach ($limit_fields as $current_field) {
					if (isset($limit_field[$current_field])) $limit_field = $limit_field[$current_field];
					else {
						$limit_field = false;
						break;
					}
				}
				$limitvalue = $limit_field;
				$data[strval($field['name'])] = $limitvalue;
			}
		}
		if ($dolimit) return false;
		
	    if (isset($field['readonly']) && !$data['id']) return false; ?>
	    
	    <div class="input"><label for="<?=$field['name']?>"<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    
		    <? if (isset($field['readonly']) || isset($object['editing_disabled'])): ?>
		    	<? $user = FW4_User::get_user_by_id($value); ?>
		    	<div class="value">
		    		<? if ($user): ?>
		    			<?=isset($user['firstname']) && $user['lastname']?$user['firstname'].' '.$user['lastname']:$user['name']?>
		    		<? else: ?>
		    			Niemand
		    		<? endif; ?>
		    	</div>
		    <? else: ?>
				<select name="<?=$field['name']?>">
					<? if (!isset($field['required'])):?>
						<option value=""></option>
					<? endif; ?>
					<? foreach (FW4_User::get_users('user','firstname,lastname') as $user): ?>
						<? if (!$user['id']) continue; ?>
						<? if (isset($field['where'])) {
					    	$condition = explode('=',$field['where']);
					    	$condition_fields = explode('.',trim($condition[0]));
							$condition_field = $user;
							foreach ($condition_fields as $current_field) {
								if (isset($condition_field[$current_field]) && $condition_field[$current_field]) $condition_field = $condition_field[$current_field];
								else {
									$condition_field = 0;
									break;
								}
							}
							if ($condition_field != trim($condition[1])) continue;
				    	} ?>
						<option value="<?=$user['id']?>"<?=$user['id']==$value?' selected="selected"':''?>><?=$user['firstname']?> <?=$user['lastname']?></option>
					<? endforeach;?>
				</select>
			<? endif; ?>
		</div>
    <? }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
    	
		$user = FW4_User::get_user();
		if (isset($field['limit']) && $user['id'] != 0) {
			$dolimit = true;
			if (isset($field['limit_condition'])) {
				$invert = false;
				if (substr($field['limit_condition'],0,1) == '!') {
					$invert = true;
					$field['limit_condition'] = substr($field['limit_condition'],1);
				}
				$limit_fields = explode('.',$field['limit_condition']);
				$limit_field = $user;
				foreach ($limit_fields as $current_field) {
					if (isset($limit_field[$current_field])) $limit_field = $limit_field[$current_field];
					else if (isset($limit_field[$current_field])) {
						$limit_field = false;
						break;
					} else {
						$limit_field = true;
						break;
					}
				}
				$dolimit = $invert?!$limit_field:$limit_field;
			}
			
			if ($dolimit) {
				$limit_fields = explode('.',$field['limit']);
				$limit_field = $user;
				foreach ($limit_fields as $current_field) {
					if (isset($limit_field[$current_field])) $limit_field = $limit_field[$current_field];
					else {
						$limit_field = false;
						break;
					}
				}
				$data[strval($field['name'])] = $limit_field;
		
				return $data;
			}
		}
	
		if (isset($newdata[strval($field['name'])])) $data[strval($field['name'])] = $newdata[strval($field['name'])];
		
		return $data;
	}
	
    public function summary($field,$data,$object) {
    	if (!$structure = FW4_Structure::get_object_structure('user',false)) return '';
    	$row = where('id',intval($data[strval($field['name'])]))->get_row('user');
    	if (!$row) return '';
    	if (!($titlefield = reset($structure->xpath('string')))) return false;
    	if (isset($field['display'])) {
    		$displayvalue = $field['display'];
	    	preg_match_all('/\[([a-z0-9\_]+)\]/is',$field['display'],$matches,PREG_SET_ORDER);
	    	foreach ($matches as $match) {
		    	$displayvalue = str_ireplace($match[0],$row[$match[1]],$displayvalue);
	    	}
	    	return $displayvalue;
    	} else {
	    	return $row[strval($titlefield['name'])];
    	}
    }
    
    function get_structure($field,$fields) {
    	$sortable = isset($field['sortable'])&&$field['sortable']?true:false;
	    return '<structure>
	    	<number name="'.$field['name'].'" length="10"/>
	    </structure>';
    }

}
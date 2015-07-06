<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
// WHERE ATTRIBUTE EXAMPLES:
//
// object_id = [parent.object_choice]
// object_id IN [parent.object_choice_multiple]
// object_choice_multiple CONTAINS [id]

class Choice extends Choice_abstract {
	
	protected static $choice_ids_to_insert = array();

    public function print_field($field,$data,$object) {
    	$limitvalue = false;
    	$dolimit = false;
	    if (isset($field['source'])) {
		    $orig_fieldname = strval($field['name']);
		    $fieldname = strval($field['name']).'_id';
			if (!$structure = FW4_Structure::get_object_structure(strval($field['source']),false)) return false;
			$titlefields = $structure->xpath('string');
			if (!($titlefield = reset($titlefields))) {
				$titlefields = $structure->xpath('number');
				if (!($titlefield = reset($titlefields))) return false;
			}
			$titlefield = strval($titlefield['name']);
		} else $fieldname = strval($field['name']);
		$user = FW4_User::get_user();
		if (isset($field['limit']) && $user['id'] != 0) {
			if (isset($field['limit_condition'])) {
				$dolimit = false;
				$invert = false;
				if (substr($field['limit_condition'],0,1) == '!') {
					$invert = true;
					$field['limit_condition'] = substr($field['limit_condition'],1);
				}
				$limit_fields = explode('.',$field['limit_condition']);
				$limit_field = $user;
				foreach ($limit_fields as $current_field) {
					if (isset($limit_field[$current_field]) && $limit_field[$current_field]) $limit_field = $limit_field[$current_field];
					else if (isset($limit_field[$current_field])) {
						$limit_field = false;
						break;
					} else {
						$limit_field = true;
						break;
					}
				}
				$dolimit = $invert?!$limit_field:$limit_field;
			} else $dolimit = true;
			
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
				$data->$fieldname = $limitvalue;
			}
		}
		if ($dolimit) return false;
		
		if (isset($field['source'])) {
			$source_rows = self::get_source_rows(strval($field['source']),$field);
			if ($source_rows === false) return false;
		}
		?>
    	<div class="<?=FW4_Admin::$in_fieldset?'field':'input'?>">
    		<?  if (isset($field['source']) && isset($field['multiple'])): ?>
    			<? if (isset($data->id)) {
    				$current = array();
    				$fieldname = strval($field['name']);
    				foreach (self::get_current_multiple_choices($object,$field,$data->id) as $row) $current[] = $row->$fieldname;
    			} else $current = array(); ?>
	    		<label><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    		<fieldset>
	    		<? foreach ($source_rows as $row): ?>
	    			<span class="evencol">
	    				<input type="checkbox" name="<?=$field['name']?>[]" value="<?=$row->id?>" id="input-<?=$field['name']?>-<?=$row->id?>"<? if (in_array($row->id,$current)):?> checked="checked"<? endif;?>/> <label for="input-<?=$field['name']?>-<?=$row->id?>"><?=$row->$titlefield?></label>
	    			</span>
	    		<? endforeach; ?>
	    		</fieldset>
    		<? else:?>
				<label class="for-selector"><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
					<? if ((isset($field['readonly']) && isset($data->id)) || isset($object['editing_disabled'])): ?>
						<div class="value">
				    		<?  if (isset($field['source'])) {
					    		if ($data->$orig_fieldname) {
						    		if (isset($field['format'])) {
							    		$displayvalue = $field['format'];
								    	preg_match_all('/\[([a-z0-9\_]+)\]/is',$field['format'],$matches,PREG_SET_ORDER);
								    	foreach ($matches as $match) {
									    	$match_name = $match[1];
									    	$displayvalue = str_ireplace($match[0],$data->$orig_fieldname->$match_name,$displayvalue);
								    	}
								    	echo $displayvalue;
							    	} else {
								    	echo $data->$orig_fieldname->$titlefield;
							    	}
							    } else { echo '-'; }
						    } else {
							    foreach ($field->children() as $child): 
							    	$value = isset($child['value'])?$child['value']:strval($child); ?>
							    	<?=$value==$data->$fieldname?strval($child):''?>
						    	<? endforeach;
						    }
					    ?>
				    	</div>
					<? else: ?>
				    	<select name="<?=$field['name']?>"<? if ($dolimit): ?> disabled="disabled"<? endif; ?><?=(isset($field['enabled_condition'])?' data-enabled-condition="'.$field['enabled_condition'].'"':'')?><?=(isset($field['visible_condition'])?' data-visible-condition="'.$field['visible_condition'].'"':'')?>>
						<?  if (isset($field['source'])) {
								if (!isset($field['required'])) echo '<option value=""></option>';
							    $group_value = false; $group_is_choice = false; $group_values = array();
							    if (isset($field['group_by'])) {
								    $group_by_field = reset($structure->xpath('*[@name="'.strval($field['group_by']).'"]'));
								    if ($group_by_field->getName() == "choice") {
									    $group_is_choice = true;
									    foreach ($group_by_field->children() as $group_child) {
										    $value = strval($group_child);
										    if (isset($group_child['value'])) $value = strval($group_child['value']);
										    $group_values[$value] = strval($group_child);
									    }
								    }
							    } else if (isset($field['optgroup'])) {
								    
								    if (!$parent_structure = FW4_Structure::get_object_structure(substr($structure['path'],0,strrpos($structure['path'],'>')),true)) return false;
									$parenttitlefields = $parent_structure->xpath('string');
									if (!($parenttitlefield = reset($parenttitlefields))) {
										$parenttitlefields = $parent_structure->xpath('number');
										if (!($parenttitlefields = reset($parenttitlefields))) return false;
									}
									$parenttitlefield = strval($parenttitlefield['name']);
									$source_rows = $source_rows->to_array();
									usort($source_rows,function ($a,$b) use ($parenttitlefield,$titlefield) {
										if (isset($a->parent()->_sort_order)) $parenttitlefield = '_sort_order';
										if ($a->parent()->$parenttitlefield == $b->parent()->$parenttitlefield) {
											if (isset($a->_sort_order)) {
												return $a->_sort_order > $b->_sort_order ? 1 : ($a->_sort_order < $b->_sort_order ? -1 : 0);
											} else {
										        return strnatcasecmp($a->$titlefield, $b->$titlefield);
										    }
									    }
									    return ($a->parent()->$parenttitlefield < $b->parent()->$parenttitlefield) ? -1 : 1;
									});
							    }
							    foreach ($source_rows as $child): 
							    	$value = $child->id;
							    	if (isset($field['group_by']) && $group_field = strval($field['group_by']) && $child->$group_field != $group_value): ?>
							    		<? if ($group_value):?></optgroup><? endif; ?>
							    		<? $group_value = $actual_group_value = $child[strval($field['group_by'])]; ?>
							    		<? if ($group_is_choice) $actual_group_value = $group_values[$group_value]; ?>
							    		<optgroup label="<?=$actual_group_value?>">
							    	<? elseif (isset($field['optgroup']) && $child->parent()->$parenttitlefield != $group_value): ?>
							    		<? if ($group_value):?></optgroup><? endif; ?>
							    		<? $group_value = $child->parent()->$parenttitlefield; ?>
							    		<optgroup label="<?=$group_value?>">
							    	<? endif; ?>
							    	<option value="<?=$value?>"<?=isset($data->$fieldname)&&$value==$data->$fieldname?' selected="selected"':''?>>
							    		<? if (isset($field['format'])) {
								    		$displayvalue = $field['format'];
									    	preg_match_all('/\[([a-z0-9\_]+)\]/is',$field['format'],$matches,PREG_SET_ORDER);
									    	foreach ($matches as $match) {
										    	$match_name = $match[1];
										    	$displayvalue = str_ireplace($match[0],$child->$match_name,$displayvalue);
									    	}
									    	echo $displayvalue;
								    	} else {
									    	echo $child->$titlefield;
									    } ?>
							    	</option>
						    	<? endforeach;
						    	if ($group_value): ?></optgroup><? endif;
						    } else {
							    foreach ($field->children() as $child): 
							    	$value = isset($child['value'])?$child['value']:strval($child); ?>
							    	<option value="<?=$value?>"<?=isset($data->$fieldname)&&$value==$data->$fieldname?' selected="selected"':''?>><?=strval($child)?></option>
						    	<? endforeach;
						    }
					    ?>
				    	</select>
				    <? endif; ?>
				<? endif; ?>
	    	</div>
	    <?
    }
    
    public function on_fetch($field,$data) {
	    if (isset($field['multiple']) && !isset($field['source'])) {
		    if (!isset($field['name'])) return;
			$fieldname = strval($field['name']);
			if (is_string($data->$fieldname)) $data->$fieldname = explode(',',$data->$fieldname);
		}
    }
    
    function get_structure($field,$fields) {
    	if (isset($field['source'])) {
	    	$structure = FW4_Structure::get_object_structure(strval($field['source']),false);
	    	$parent_path = substr($structure['path'], 0, strrpos($structure['path'], '>'));
    		if (isset($field['multiple'])) {
	    		return '<structure><object source="'.$field['source'].'" name="'.$field['name'].'"><number source="'.$field['source'].'" name="'.$field['name'].'" length="10" index="index"/></object><dbrelation name="'.$field['name'].'" source="'.$field['source'].'" link_table="'.$fields['stack'].'/'.$field['name'].'" local_key="id" link_local_key="'.$fields['name'].'_id" link_foreign_key="'.$field['name'].'" foreign_key="id"/></structure>';
    		} else {
	    		$xml = '<number source="'.$field['source'].'" name="'.$field['name'].'_id" length="10" index="index"/><dbrelation name="'.$field['name'].'" source="'.$field['source'].'" local_key="'.$field['name'].'_id" foreign_key="id"/>';
	    		if (isset($field['parent_name'])) {
		    		$xml .= '<number source="'.$parent_path.'" name="'.$field['parent_name'].'_id" length="10" index="index"/><dbrelation name="'.$field['parent_name'].'" source="'.$parent_path.'" local_key="'.$field['parent_name'].'_id" foreign_key="id"/>';
	    		}
	    		return '<structure>'.$xml.'</structure>';
	    	}
    	} else {
	    	if (isset($field['multiple'])) {
		    	return '<structure><text name="'.$field['name'].'" multiple="multiple"/></structure>';
		    } else {
		    	$is_numeric = true;
		    	$length = 0;
		    	foreach ($field->children() as $child) {
			    	if (isset($child['value']) && $is_numeric) $is_numeric = is_numeric(strval($child['value']));
			    	else $is_numeric = false;
			    	$length = isset($child['value'])?strlen($child['value']):strlen(strval($child));
		    	}
		    	if ($is_numeric) return '<structure><number name="'.$field['name'].'" length="10"'.(isset($field['index'])?' index="'.$field['index'].'"':'').'/></structure>';
		    	else return '<structure><string name="'.$field['name'].'" length="'.$length.'"/></structure>';
		    }
	    }
    }
    
    public function summary($field,$data,$object) {
    	$fieldname = strval($field['name']);
    	if (isset($field['source'])) {
    		if (isset($field['multiple'])) return ''; //todo
			if (!$structure = FW4_Structure::get_object_structure(strval($field['source']),false)) return false;
			$titlefields = $structure->xpath('string');
	    	if (!($titlefield = reset($titlefields))) return false;
	    	if (isset($field['format'])) {
		    	
		    	if (!$data->$fieldname) return '';
	    		$displayvalue = $field['format'];
		    	preg_match_all('/\[([a-z0-9\_]+)\]/is',$field['format'],$matches,PREG_SET_ORDER);
		    	foreach ($matches as $match) {
			    	$match_name = $match[1];
			    	$displayvalue = str_ireplace($match[0],$data->$fieldname->$match_name,$displayvalue);
		    	}
		    	return $displayvalue;
	    	
	    	} else if (isset($field['optgroup']) && isset($field['optgroup_in_summary'])) {
	    		
	    		$titlefieldname = strval($titlefield['name']);
	    		
	    		if (!$parent_structure = FW4_Structure::get_object_structure(substr($structure['path'],0,strrpos($structure['path'],'>')),true)) return '';
	    		$parenttitlefields = $parent_structure->xpath('string');
				if (!($parenttitlefield = reset($parenttitlefields))) {
					$parenttitlefields = $parent_structure->xpath('number');
					if (!($parenttitlefields = reset($parenttitlefields))) return '';
				}
				$parenttitlefield = strval($parenttitlefield['name']);
		    	return isset($data->$fieldname->$titlefieldname) && isset($data->$fieldname->parent()->$parenttitlefield) ? '<span style="opacity:0.5">'.$data->$fieldname->parent()->$parenttitlefield.':</span> '.$data->$fieldname->$titlefieldname :'';
	    	
	    	} else {
	    		$titlefieldname = strval($titlefield['name']);
		    	return isset($data->$fieldname->$titlefieldname)?$data->$fieldname->$titlefieldname:'';
	    	}
    	} else {
	    	foreach ($field->children() as $child) {
	    		$value = isset($child['value'])?$child['value']:strval($child);
		    	if ($value == $data->$fieldname) {
		    		if (isset($child['summary'])) return strval($child['summary']);
		    		return strval($child);
		    	}
	    	}
    	}
	    return '';
    }
    
    public function export($data,$field) {
    	$fieldname = strval($field['name']);
    	if (isset($field['source'])) {
	    	if (!$data->$fieldname) return '';
    		if (isset($field['multiple'])) return ''; //todo
			if (!$structure = FW4_Structure::get_object_structure(strval($field['source']),false)) return false;
			$titlefields = $structure->xpath('string');
	    	if (!($titlefield = reset($titlefields))) return false;
	    	if (isset($field['display'])) {
	    		$displayvalue = $field['display'];
		    	preg_match_all('/\[([a-z0-9\_]+)\]/is',$field['display'],$matches,PREG_SET_ORDER);
		    	foreach ($matches as $match) {
			    	$target_fieldname = $match[1];
			    	$displayvalue = str_ireplace($match[0],$data->$fieldname->$target_fieldname,$displayvalue);
		    	}
		    	return $displayvalue;
	    	} else {
		    	$target_fieldname = strval($titlefield['name']);
		    	return $data->$fieldname->$target_fieldname;
	    	}
    	} else {
	    	foreach ($field->children() as $child) {
	    		$value = isset($child['value'])?$child['value']:strval($child);
		    	if ($value == $data->$fieldname) {
		    		if (isset($child['summary'])) return decode(strval($child['summary']));
		    		return decode(strval($child));
		    	}
	    	}
    	}
	    return '';
    }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
    	
		$user = FW4_User::get_user();
		if (isset($field['limit']) && $user['id'] != 0) {
			$dolimit = true;
			
			if (isset($field['limit_condition'])) {
				$dolimit = false;
				$invert = false;
				if (substr($field['limit_condition'],0,1) == '!') {
					$invert = true;
					$field['limit_condition'] = substr($field['limit_condition'],1);
				}
				$limit_fields = explode('.',$field['limit_condition']);
				$limit_field = $user;
				foreach ($limit_fields as $current_field) {
					if (isset($limit_field[$current_field]) && $limit_field[$current_field]) $limit_field = $limit_field[$current_field];
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
				$data[strval($field['name']).'_id'] = $limit_field;
		
				return $data;
			}
		}
		
		$fieldname = strval($field['name']);
		if (isset($field['multiple'])) {
			if (!isset($newdata[strval($field['name'])])) $newdata[strval($field['name'])] = array();
			if (isset($olddata->id)) {
				foreach (self::get_current_multiple_choices($object,$field,$olddata->id) as $row) {
					if (in_array($row->$fieldname, $newdata[$fieldname])) {
						unset($newdata[$fieldname][array_search($row->$fieldname, $newdata[strval($field['name'])])]);
					} else where('id = %d',$row->id)->delete($object['stack'].'/'.$field['name']);
				}
			}
			self::$choice_ids_to_insert = $newdata[strval($field['name'])];
		} else if (isset($newdata[strval($field['name'])])) {
			if (isset($field['source'])) {
				$data[strval($field['name']).'_id'] = $newdata[strval($field['name'])];
				if (isset($field['parent_name'])) {
					$data[strval($field['parent_name']).'_id'] = 0;
					if ($source_rows = self::get_source_rows(strval($field['source']),$field)) {
						if ($row = $source_rows->row_with_id($newdata[strval($field['name'])])) {
							$data[strval($field['parent_name']).'_id'] = $row->parent()->id;
						}
					}
				}
			} else {
				$data[strval($field['name'])] = $newdata[strval($field['name'])];
			}
		}
		return $data;
	}
	
	public function edited($field,$data,$object) {
		if (isset($field['multiple']) && count(self::$choice_ids_to_insert)) {
			foreach (self::$choice_ids_to_insert as $choice_id) {
				insert($object['stack'].'/'.$field['name'],array(
					$object['name'].'_id' => $data->id,
					strval($field['name']) => $choice_id
				));
			}
		}
	}

}


class Choice_abstract extends FW4_Type {

	protected static $cache = array();
    
    public function get_source_rows($name,$field,$id=false) {
    	if ($id) {
    		return where('id = %d',$id)->get($name);
    	}
    	if (isset($field['where'])) {
    		$query = new Query();
	    	$path = explode('>',FW4_Admin::$parent_structure['path']);
	    	$whereval = strval($field['where']);
	    	if (preg_match_all('/\[(.*?)\]/is', $whereval, $matches, PREG_SET_ORDER)) {
		    	foreach ($matches as $match) {
			    	$parent_item = false;
			    	$current_item = FW4_Admin::$current_item;
			    	foreach (explode('.',$match[1]) as $part) {
				    	if ($part == 'parent') {
					    	array_pop($path);
					    	if ($parent_item === false) $parent_item = FW4_Admin::$parent_item;
					    	else $parent_item = $parent_item->parent();
				    	} else {
					    	if (!$parent_item && !$current_item) return false;
					    	$data = $parent_item ? $parent_item : $current_item;
					    	$structure = FW4_Structure::get_object_structure(implode('>',$path),false);
					    	$other_field = $structure->xpath('*[@name="'.addslashes($part).'"]');
					    	if (is_array($other_field)) $other_field = reset($other_field);
					    	if ($other_field && $other_field->getName() == 'choice') {
						    	if (isset($other_field['multiple'])) {
							    	$ids = $data->$part->ids();
							    	if (!count($ids)) $ids[] = 0;
							    	$whereval = str_replace($match[0], '('.implode(',',$ids).')', $whereval);
							    } else {
								    $fieldname = $part.'_id';
									if (!isset($data->$fieldname)) return false;
								    $whereval = str_replace($match[0], $data->$fieldname, $whereval);
							    }
						    } else {
							    if (!property_exists($data,$part)) return false;
							    $whereval = str_replace($match[0], is_null($data->$part)?'NULL':$data->$part, $whereval);
						    }
				    	}
			    	}
		    	}
	    	}
	    	if (preg_match_all('/(\S+)\s+contains\s+(\S+)/is', $whereval, $matches, PREG_SET_ORDER)) {
		    	foreach ($matches as $match) {
			    	$structure = FW4_Structure::get_object_structure($name,false);
			    	$other_field = $structure->xpath('*[@name="'.addslashes($match[1]).'"]');
			    	if (is_array($other_field)) $other_field = reset($other_field);
			    	if (!isset($other_field['multiple'])) {
				    	throw new Exception('"contains" only possible on choice with multiple attribute');
			    	}
			    	if (!$other_field) return false;
			    	$query->join($structure['path'].'>'.$other_field['name'],$other_field['name'].'.'.$structure['name'].'_id = '.$structure['name'].'.id');
				    $whereval = str_replace($match[0], $other_field['name'].'.'.$other_field['name'].' = '.$match[2], $whereval);
			    }
			}
	    	return $query->where($whereval)->get($name);
    	}
	    if (!isset(self::$cache[$name])) self::$cache[$name] = get($name);
	    return self::$cache[$name];
    }
    
    public function get_current_multiple_choices($object,$field,$id) {
    	return where($object['name'].'_id = %d',$id)->get($object['stack'].'>'.$field['name']);
    }

}


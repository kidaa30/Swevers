<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Link extends FW4_Type {

    public function print_field($field,$data,$object) { 
	    if (isset($data->id)) {
	    	$slugs = self::get_slugs($field,$data,$object);
	    	if (isset($field['format'])) $format = strval($field['format']);
	    	else $format = '[slug]';
		    if (count($slugs)) { ?>
			   <div class="input">			 
					<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> 
					<fieldset>
					<? foreach ($slugs as $language => $slug): 
						if (!is_string($language)) $language = $object_language;
						set_language($language);
						$url = str_replace('[slug]',$slug,$format);
						$url = url(str_replace('[id]',$data->id,$url));
					?>
						<div><a class="button thin" href="<?=$url?>" target="_blank"><?=$url?></a></div>
					<? endforeach; 
					$languages = array_keys(languages());
					set_language(reset($languages));
					?>
					</fieldset>
			   </div>
		    <? }
	    }
    }
    
    public function get_slugs($field,$data,$object) {
	    if (isset($data->id)) {
	    	$slugs = array(); $object_language = language(); $parent_id = 0;
		    foreach ($object->children() as $sibling) {
		    	if ($sibling->getName() == 'slug') {
			    	$source = false;
					foreach ($object->children() as $child) {
						if (strval($child['name']) == strval($sibling['source'])) $source = $child;
					}
					if ($source) {
						$name = isset($sibling['name'])?strval($sibling['name']):'slug';
						if (isset($source['translatable']) && $source['translatable']) {
							foreach (languages() as $code => $lang) {
								$fieldname = $name.'_'.$code;
								$slugs[$code] = $data->$fieldname;
							}
						} else $slugs[] = $data->$name;
					}
		    	} else if ($sibling->getName() == 'language') {
		    		$language = strval($sibling['name']);
		    		$object_language = $data->$language;
		    	} else if ($sibling->getName() == 'recursive' && isset($data->parent_id) && $data->parent_id) {
		    		$parent_id = $data->parent_id;
		    	}
		    }
		    foreach ($slugs as $language => $slug) {
			    if (!is_string($language)) {
				    $slugs[$object_language] = $slug;
				    unset($slugs[$language]);
			    }
		    }
		    if ($parent_id) {
			    $parent_slugs = self::get_slugs($field,where('id = %d',$parent_id)->get_row($object['stack']),$object);
			    foreach ($parent_slugs as $key => $parent_slug) {
				    if (isset($slugs[$key])) $slugs[$key] = $parent_slug.'/'.$slugs[$key];
			    }
		    }
		    return $slugs;
	    } else return array();
    }
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		return $data;
	}
    
    public function get_structure($field,$fields) {
	    return '<structure></structure>';
    }

}
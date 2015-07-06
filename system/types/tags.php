<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Tags_type extends FW4_Type {
	
	public function print_field($field,$data,$object) { 
		$fieldname = strval($field['name']);
		$tags = array();
		$tagtranslations = array();
		foreach (get($object['stack'].'>'.$field['name']) as $tag) {
			$tags[] = array('id'=>$tag->id,'tag'=>$tag->tag);
			if (isset($field['translatable'])) {
				foreach (languages() as $code => $language) {
					$fieldn = 'tag_'.$code;
					if (!isset($tagtranslations[$code])) $tagtranslations[$code] = array();
					$tagtranslations[$code][strtolower($tag->tag)] = $tag->$fieldn;
				}
			}
		}
		
		$currenttags = array();
		if ($data && isset($data->id)) foreach ($data->$fieldname as $tag) $currenttags[] = $tag->tag; ?>
    	
		<div class="<?=FW4_Admin::$in_fieldset?'field':'input'?>" id="tagsinput-<?=$field['name']?>">
			<label for="<?=$field['name']?>" class="for-input"><?=$field['label']?></label>
			<input data-fieldname="<?=$field['name']?>" data-languages='<?=(json_encode(array_slice(array_keys(languages()),1)))?>' data-tags="tags<?=$field['name']?>" class="<?=isset($field['required']) && $field['required']?'required':''?><?=isset($field['translatable']) && $field['translatable']?' translatable':''?> tags wide" type="text" id="input-<?=$field['name']?>" name="<?=$field['name']?>" value="<?=implode(', ',$currenttags)?>" maxlength="<?=isset($field['length'])?$field['length']:150?>"<?=isset($field['visible_condition'])?' data-visible-condition="'.$field['visible_condition'].'"':''?>/>
			<? if (isset($field['translatable'])): ?>
				<? foreach (languages() as $code => $language): ?>
					<? if ($code != language()): ?>
						<input class="tags-hidden" data-language="<?=$code?>" type="text" id="input-<?=$field['name']?>-<?=$code?>" name="<?=$field['name']?>_<?=$code?>" value="<?=implode(', ',$currenttags)?>"/>
					<? endif; ?>
				<? endforeach; ?>
			<? endif; ?>
		</div>
		<script>
			var tags<?=$field['name']?> = <?=json_encode($tags);?>;
			<? if (isset($field['translatable'])): ?>
				var currenttags_<?=$field['name']?> = [];
				var tagtranslations_<?=$field['name']?> = [];
				<? foreach (languages() as $code => $language): ?>
					<? if ($code != language()): ?>
						<? $current = array(); ?>
						<? $fieldn = 'tag_'.$code; ?>
						currenttags_<?=$field['name']?>["<?=$code?>"] = [];
						tagtranslations_<?=$field['name']?>["<?=$code?>"] = <?=json_encode(isset($tagtranslations[$code])?$tagtranslations[$code]:array())?>;
					<? endif; ?>
				<? endforeach; ?>
			<? endif; ?>
		</script>
    <? }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
		return $data;
	}
	
    public function edited($field,$data,$object) {
    	$fieldname = strval($field['name']);
    	$newtags = explode(',',$_POST[$fieldname]);
    	
    	if (isset($field['translatable'])) {
	    	$translated = array();
	    	foreach (languages() as $lang => $langname) {
		    	if (isset($_POST[$fieldname.'_'.$lang])) {
			    	$translated[$lang] = explode(',',$_POST[$fieldname.'_'.$lang]);
		    	}
	    	}
    	}
    	
    	if (isset($data->id)) {
	    	foreach ($data->$fieldname as $tag) {
	    		$index = array_search($tag->tag,$newtags);
		    	if ($index !== false) {
			    	if (isset($field['translatable'])) {
				    	$to_update = array();
				    	foreach (languages() as $lang => $langname) {
					    	if ($lang != language()) {
						    	$fieldn = 'tag_'.$lang;
						    	if ($tag->$fieldn != trim($translated[$lang][$index])) {
							    	$to_update[$fieldn] = trim($translated[$lang][$index]);
									$to_update['slug_'.$lang] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', str_replace('-',' ',remove_accents($to_update[$fieldn]))) )));
						    	}
						    	unset($translated[$lang][$index]);
					    	}
				    	}
				    	if (count($to_update)) where('id = %d',$tag->id)->update($object['stack'].'>'.$field['name'],$to_update);
				    }
			    	unset($newtags[$index]);
			    } else {
			    	where(strval($object['name']).' = %d AND '.strval($field['name']).'_id = %d',$data->id,$tag->id)->delete($object['stack'].'>'.$field['name'].'>relation');
			    	if (where(strval($field['name']).'_id = %d',$tag->id)->get($object['stack'].'>'.$field['name'].'>relation')->count() == 0) {
				    	where('id = %d',$tag->id)->delete($object['stack'].'>'.$field['name']);
			    	}
		    	}
	    	}
	    }
    	
	    foreach ($newtags as $index => $tag) {
	    	$existing = where('tag = %s',$tag)->get_row($object['stack'].'>'.$fieldname);
	    	if ($existing) {
		    	$tag_id = $existing->id;
		    	if (isset($field['translatable'])) {
			    	$to_update = array();
			    	foreach (languages() as $lang => $langname) {
				    	if ($lang != language()) {
					    	$fieldn = 'tag_'.$lang;
					    	if ($existing->$fieldn != trim($translated[$lang][$index])) {
						    	$to_update[$fieldn] = trim($translated[$lang][$index]);
								$to_update['slug_'.$lang] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', str_replace('-',' ',remove_accents($to_update[$fieldn]))) )));
					    	}
					    	unset($translated[$lang][$index]);
				    	}
			    	}
			    	if (count($to_update)) where('id = %d',$existing->id)->update($object['stack'].'>'.$field['name'],$to_update);
			    }
	    	} else {
		    	$to_insert = array(
			    	'tag' => $tag,
			    	'slug' => strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', str_replace('-',' ',remove_accents($tag))) )))
			    );
			    if (isset($field['translatable'])) {
			    	foreach (languages() as $lang => $langname) {
				    	if ($lang != language()) {
					    	$to_insert['tag_'.$lang] = trim($translated[$lang][$index]);
					    	$to_insert['slug_'.$lang] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', str_replace('-',' ',remove_accents($to_insert['tag_'.$lang]))) )));
				    	}
			    	}
			    }
		    	$tag_id = insert($object['stack'].'>'.$fieldname,$to_insert);
	    	}
		    insert($object['stack'].'>'.$fieldname.'>relation',array(
		    	$fieldname.'_id' => $tag_id,
		    	strval($object['name']) => $data->id
		    ));
	    }
    }
    
    public function deleted($field,$data) {
    	$fieldname = $field['name'];
    	$fieldname_id = $field['name'].'_id';
		foreach ($data as $row) {
	    	foreach (where(strval($field['parent_name']).' = %d',$row->id)->get($field['stack'].'>relation') as $relation) {
		    	where('id = %d',$relation->id)->delete($field['stack'].'>relation');
		    	if (where($fieldname.'_id = %d',$relation->$fieldname_id)->get($field['stack'].'>relation')->count() == 0) {
			    	where('id = %d',$relation->$fieldname_id)->delete($field['stack']);
		    	}
	    	}
	    }
    }
    
    function get_structure($field,$fields) {
    	return '<structure>
    		<object name="'.$field['name'].'" order="id" child="false">
    			<string name="tag" length="100" index="index"'.(isset($field['translatable'])?' translatable="'.$field['translatable'].'"':'').'/>
    			<string name="slug" length="100" index="index"'.(isset($field['translatable'])?' translatable="'.$field['translatable'].'"':'').'/>
    			<dbrelation name="'.$fields['name'].'" source="'.$fields['stack'].'" link_table="'.$fields['stack'].'>'.$field['name'].'>relation" local_key="id" link_local_key="'.$field['name'].'_id" link_foreign_key="'.$fields['name'].'" foreign_key="id"/>
	    		<object name="relation">
	    			<number name="'.$fields['name'].'" length="10" index="index"/>
	    		</object>
    		</object>
    		<dbrelation name="'.$field['name'].'" source="'.$fields['stack'].'>'.$field['name'].'" order="id" link_table="'.$fields['stack'].'>'.$field['name'].'>relation" local_key="id" link_local_key="'.$fields['name'].'" link_foreign_key="'.$field['name'].'_id" foreign_key="id"'.(isset($field['searchable'])?' searchable="'.$field['searchable'].'"':'').'/>
    	</structure>';
    }
    
    public function get_search_index($field,$data,$object,$language) {
	    
	    $fieldname = strval($field['name']);
	    
	    $tagname = 'tag';
	    if (isset($field['translatable'])) $tagname = 'tag_'.$language;
	    
	    $currenttags = array();
	    if ($data && isset($data->id)) {
		    unset($data->$fieldname);
		    foreach ($data->$fieldname as $tag) {
			    $currenttags[] = $tag->$tagname;
			}
		}
	    
	    return implode(', ',$currenttags);
    }
    
    public function get_scripts() {
	    return <<<SCRIPTS
	    <script src="/system/admin/js/jquery.tagsinput.js"></script>
		<script>
			$('input.tags').each(function(){
				var tagsInput = $(this);
				tagsInput.tagsInput({
				   'defaultText':'',
				   'height':'17px',
				   'placeholderColor':'#ccc',
				   'autocomplete_url':function(request, response) {
				   		var search = request.term;
					   	var results = $.map(window[tagsInput.data('tags')], function(item) {
							if (item.tag.indexOf(search) == 0) return item.tag;
							else return null;
						});
				        response(results.slice(0, 5));
				    },
				   'autocomplete':{autoFocus:false,delay:0,appendTo:"#tagsinput-"+$(this).prop('name')}
				});
			});
		</script>
SCRIPTS;
    }

}
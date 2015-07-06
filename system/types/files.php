<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Files_type extends FW4_Type {

    public function print_field($field,$data,$object) {
	    $fieldname = strval($field['name']);
    	if (isset($field['is_viewing_version']) && $field['is_viewing_version']) {
    		return false;
    	} ?>
    	<div class="input">
	    	<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    	<fieldset>
	    		<? if (isset($data->id) && $data->$fieldname->count() > 0): ?>
	    			<table width="100%" cellpadding="0" cellspacing="0" data-objectname="<?=$object['stack'].'/'.$field['name']?>" class="<?=(isset($field['sortable']) && $field['sortable'] && !isset($object['editing_disabled']))?' sortable':''?>">
			    		<? foreach ($data->$fieldname as $file): ?>
			    			<tr>
				    			<? if (isset($field['sortable']) && $field['sortable']):?>
			    					<td><img class="sort-handle" alt="Sorteren" title="Sorteren" src="/images/admin/sort.gif"><input type="hidden" name="sort-<?=$file['id']?>" value="<?=$image['sort_order']?>" /></td>
			    				<? endif; ?>
								<? if (isset($field['selectable']) && $field['selectable']):?>
									<td><input type="radio" name="<?=$fieldname?>-select" value="<?=$file['id']?>"<?=$file['selected']==1?' checked="checked"':''?> /></td>
								<? endif; ?>
								<? if (isset($field['checkable']) && $field['checkable']):?>
									<td><input type="checkbox" name="<?=$fieldname?>-check[]" value="<?=$file['id']?>"<?=$file['checked']==1?' checked="checked"':''?> /></td>
								<? endif; ?>
					    		<td width="100%">&nbsp;<a href="<?=$fieldname?>/download/<?=$file->id?>"><?=$file->orig_filename?></a></td>
					    		<td width="0"><a href="<?=$fieldname?>/delete/<?=$file->id?>" onclick="return confirm('Bent u zeker dat u deze afbeelding wilt verwijderen?');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"/></a></td>
			    			</tr>
			    		<? endforeach; ?>
	    			</table>
	    			<hr/>
	    		<? endif; ?>
	    		<label for="<?=$fieldname?>">Toevoegen</label><input type="file" multiple="multiple" name="<?=$fieldname?>[]"/>
	    	</fieldset>
    	</div><?
    }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		return $data;
	}
    
    public function edited($field,$data,$object) {
    
	    if (isset($_FILES[strval($field['name'])]) && is_array($_FILES[strval($field['name'])]['name'])) {
    		$files=array();
    		foreach ($_FILES[strval($field['name'])]['name'] as $index => $file_name) {
    			$newfile = array();
    			foreach (array_keys($_FILES[strval($field['name'])]) as $key) {
    				$newfile[$key] = $_FILES[strval($field['name'])][$key][$index];
    			}
    			$files[] = $newfile;
    		}
    		
    		foreach ($files as $f) {
    			if ($f['size']) {
    				$toinsert = array();
    				
    				$extension = substr($f['name'], strrpos($f['name'], '.')+1);
    				do {
    					$name = md5(rand(0,99999).rand(0,99999));
    				} while (file_exists(FILESPATH.$name.".".$extension));
    				
    				move_uploaded_file($f['tmp_name'], FILESPATH.$name.".".$extension);
    				$toinsert['orig_filename'] = decode($f['name']);
    				$toinsert['filename'] = $name.'.'.$extension;
    				$toinsert['upload_date'] = time();
    				
    				$toinsert[$object['name']."_id"] = $data->id;
    				insert($object['stack'].'>'.$field['name'],$toinsert);
    			}
    		}
    	}
    	
    }
    
    public function deleted($field,$data) {
	    foreach ($data as $row) @unlink(FILESPATH.$row->filename);
    }
    
    function get_structure($field,$fields) {
    	$sortable = isset($field['sortable'])&&$field['sortable']?true:false;
    	$xml = '<structure>
	    	<object name="'.$field['name'].'"'.($sortable?' sortable="true"':'').'>
	    		<string name="filename" length="200"/>
	    		<string name="orig_filename" length="200"/>
	    		<date name="upload_date"/>';
	    foreach ($field->children() as $child) $xml .= $child->asXML();
	    return $xml.'
	    	</object>
	    </structure>';
    }
    
    public function function_delete($field,$object,$data,$id) {
    	if ($row = where($object['name'].'_id = %d && id = %d',$data->id,$id)->get_row($object['stack'].'>'.$field['name'])) {
	    	where('id = %d',$row->id)->delete($object['stack'].'>'.$field['name']);
    		redirect($_SERVER['HTTP_REFERER']);
    	} else error(404);
    }
    
    public function function_download($field,$object,$data,$id) {
    	if ($row = where($object['name'].'_id = %d && id = %d',$data->id,$id)->get_row($object['stack'].'>'.$field['name'])) {
	    	$file = where('id = %d',$row->id)->get_row($object['stack'].'>'.$field['name']);
    		force_download(FILESPATH.$file->filename,$file->orig_filename);
    	} else error(404);
    }

}
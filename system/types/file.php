<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class File extends FW4_Type {

    public function print_field($field,$data,$object) {
	    $fieldname = strval($field['name']);
    	if (isset($field['is_viewing_version']) && $field['is_viewing_version']) {
    		return false;
    	}?>
    	<div class="input">
	    	<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    	<fieldset>
	    		<? if (isset($data->id) && $data->$fieldname->count()): ?>
		    		<? foreach ($data->$fieldname as $file): ?>
		    			<table width="100%" cellpadding="0" cellspacing="0">
		    				<tr><td width="100%">&nbsp;<a href="<?=$fieldname?>/download/<?=$file->id?>"><?=$file->orig_filename?></a></td>
		    				<? if (!isset($field['required'])): ?><td width="0"><a class="delete" href="<?=$fieldname?>/delete/<?=$file->id?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u dit bestand wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer ce fichier?','en'=>'Are you sure you want to delete this file?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"/></a></td><? endif; ?></tr>
		    			</table>
		    		<? endforeach; ?>
		    		<hr/>
	    		<? endif; ?>
	    		<input class="<? if (isset($field['required']) && !count($files)): ?>required<? endif; ?>" type="file" name="<?=$field['name']?>"/>
	    	</fieldset>
    	</div>
    	
    	<?
    }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		return $data;
	}
    
    public function edited($field,$data,$object) {
	    if (isset($_FILES[strval($field['name'])]) && $_FILES[strval($field['name'])]['size']) {
			$toinsert = array();
			
			$extension = substr($_FILES[strval($field['name'])]['name'], strrpos($_FILES[strval($field['name'])]['name'], '.')+1);
			do {
				$name = md5(rand(0,99999).rand(0,99999));
			} while (file_exists(FILESPATH.$name.".".$extension));
			
			move_uploaded_file($_FILES[strval($field['name'])]['tmp_name'], FILESPATH.$name.".".$extension);
			$toinsert['orig_filename'] = decode($_FILES[strval($field['name'])]['name']);
			$toinsert['filename'] = $name.'.'.$extension;
			$toinsert['upload_date'] = time();
			
			$toinsert[$object['name']."_id"] = $data->id;
			
			where($object['name']."_id = %d",$data->id)->delete($object['stack'].'>'.$field['name']);
			
			insert($object['stack'].'>'.$field['name'],$toinsert);
			
			if (isset($field['searchable'])) {
				$filecontent = '';
				if ($extension == 'pdf') {
					use_library('pdf');
					$filecontent = pdf_to_text(FILESPATH.$name.".".$extension);
				}
				where('id',intval($data['id']))->update($object['stack'],array(
					$field['name'].'_content' => $filecontent
				));
				where('object_id',intval($data['id']))->where('object',$object['stack'])->update('_search_index',array(
					strval($field['searchable']) => $filecontent
				));
			}
    	}
    	
    }
    
    public function deleted($field,$data) {
	    foreach ($data as $row) @unlink(FILESPATH.$row->filename);
    }
        
    function get_structure($field,$fields) {
    	$sortable = isset($field['sortable'])&&$field['sortable']?true:false;
	    return '<structure>
	    	<object name="'.$field['name'].'"'.($sortable?' sortable="true"':'').'>
	    		<string name="filename" length="200"/>
	    		<string name="orig_filename" length="200"/>
	    		<date name="upload_date"/>
	    	</object>
	    	'.(isset($field['searchable'])?'<text name="'.$field['name'].'_content" searchable="'.$field['searchable'].'"/>':'').'
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
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Images extends FW4_Type {

    public function print_field($field,$data,$object) {
	    $editable = (!isset($object['editing_disabled']) || (isset($field['editing_disabled']) && $field['editing_disabled'] == 'false')) && !isset($field['readonly']);
    	$fieldname = strval($field['name']);
    	if (isset($field['is_viewing_version']) && $field['is_viewing_version']) {
    		return false;
    	}
    	if (isset($data->id)) {
	    	$images = $data->$fieldname->to_array();
		} else $images = array(); ?>
    	<div class="input">
	    	<? if (isset($field['label'])):?><label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=$field['label']?></label><? endif;?>
	    	<table data-objectname="<?=$object['stack'].'>'.$field['name']?>" class="imagelist list<?=(isset($field['sortable']) && $field['sortable'] && $editable)?' sortable':''?>">
	    		<? if (count($images)): ?>
		    		<? foreach ($images as $image): ?>
		    			<tbody data-id="<?=$image->id?>">
			    			<tr>
			    				<? if (isset($field['sortable']) && $field['sortable'] && $editable):?>
			    					<td width="0"><img class="sort-handle" alt="<?=l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort'))?>" title="<?=l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort'))?>" src="<?=url(ADMINRESOURCES.'images/sort.png')?>" width="10" height="11"/><input type="hidden" name="sort-<?=$image->id?>" value="<?=$image->_sort_order?>" /></td>
			    				<? endif; ?>
			    				<td width="0" valign="top">
				    				<div style="background:url('<?=$image->cover(50,50)?>')" class="image-thumbnail"></div>
			    				</td>
			    				<td width="100%">
			    					<div><?=$image->orig_filename?></div>
			    					<? if (!isset($field['readonly']) && $editable): ?>
				    					<input type="text" class="required" name="<?=$field['name']?>-alt[]" placeholder="Beschrijving van de afbeelding" value="<?=e($image->alt)?>" required="required"/>
				    				<? endif; ?>
			    				</td>
			    				<? if ($editable): ?>
				    				<td width="0" valign="top">
				    					<a class="delete" href="<?=$field['name']?>/delete/<?=$image->id?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u deze afbeelding wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette image?','en'=>'Are you sure you want to delete this image?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"/></a>
				    				</td>
				    			<? endif; ?>
			    			</tr>
		    			</tbody>
		    		<? endforeach; ?>
		    	<? else: ?>
		    		<tr class="note"><td><?=l(array('nl'=>'Nog geen afbeeldingen.','fr'=>'Pas d&lsquo;images.','en'=>'No images.'))?></td></tr>
	    		<? endif; ?>
	    	</table>
	    	<? if ($editable): ?>
		    	<div class="imagestypeuploader" data-fieldname="<?=$field['name']?>"></div>
		    <? endif; ?>
    	</div><?
    }
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		return $data;
	}
    
    public function edited($field,$data,$object) {
	    
	    $editable = (!isset($object['editing_disabled']) || (isset($field['editing_disabled']) && $field['editing_disabled'] == 'false')) && !isset($field['readonly']);
	    
	    if (!$editable) return;
    
    	$fieldname = strval($field['name']);
    	
    	if (isset($data->id)) {
	    	foreach ($data->$fieldname as $image) {
		    	$new_alt = array_shift($_POST[$fieldname.'-alt']);
		    	if ($new_alt != $image->alt) {
			    	$image->clear_thumbnails();
			    	where('id = %d',$image->id)->update($object['stack'].'>'.$field['name'],array(
			    		'alt' => $new_alt
			    	));
			    }
	    	}
		}
    	
    	if (isset($_POST[$fieldname]) && is_array($_POST[$fieldname]) && count($_POST[$fieldname])) {
	    	foreach ($_POST[$fieldname] as $filename) {
	    		
	    		if (!$filename || !file_exists(FILESPATH.'uploaded-images/'.$filename)) continue;
	    		
		    	$toinsert = array();
				
				$extension = substr($filename, strrpos($filename, '.')+1);
				do {
					$name = md5(rand(0,99999).rand(0,99999));
				} while (file_exists(FILESPATH.$name.".".$extension));
				
				rename(FILESPATH.'uploaded-images/'.$filename,FILESPATH.$name.".".$extension);
				
				$toinsert['orig_filename'] = array_shift($_POST[$fieldname.'-name']);
				$toinsert['alt'] = array_shift($_POST[$fieldname.'-alt']);
				$toinsert['filename'] = $name.'.'.$extension;
				$toinsert['upload_date'] = time();
				
				$toinsert[$object['name']."_id"] = intval($data->id);
		
				insert($object['stack'].'>'.$field['name'],$toinsert);
	    	}
    	}
    	
    }
    
    public function function_qq_upload($field,$object,$data) {
	    $seconds_old = 3600*2;
        $directory = FILESPATH.'uploaded-images';
	    
	    if (!file_exists($directory)) mkdir($directory);
		else if( $dirhandle = @opendir($directory) ) {
	        while( false !== ($filename = readdir($dirhandle)) ) {
                if( $filename != "." && $filename != ".." ) {
                    $filename = $directory. "/". $filename;
                    if( @filemtime($filename) < (time()-$seconds_old)) @unlink($filename);
                }
	        }
	    }
	    
	    use_library('upload');
	    
	    $allowedExtensions = array('jpg','jpeg','png','gif');
	    $sizeLimit = 10 * 1024 * 1024;
	    
	    $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
	    
	    $result = $uploader->handleUpload($directory.'/');
	    if (isset($result['filename'])) $result['thumbnail'] = '/'.UPLOADSDIR.'/uploaded-images/'.$result['filename'];
	    
	    echo json_encode($result);
    }
    
    public function deleted($field,$data) {
	    $fieldname = strval($field['name']);
	    foreach ($data as $possible_image) {
		    if (is_a($possible_image,'Image')) {
		        if ($possible_image->filename) {
		    	    $possible_image->clear_thumbnails();
		    		@unlink(FILESPATH.$possible_image->filename);
		    	}
		    } else {
			    foreach ($possible_image->$fieldname as $image) {
				    if ($image->filename) {
			    	    $image->clear_thumbnails();
			    		@unlink(FILESPATH.$image->filename);
			    	}
			    }
		    }
	    }
    }
    
    public function get_structure($field,$fields) {
    	$sortable = isset($field['sortable'])&&$field['sortable']?true:false;
    	$xml = '<structure>
	    	<object name="'.$field['name'].'"'.($sortable?' sortable="true" order="_sort_order asc, id asc"':' order="id asc"').' model="image">
	    		<string name="filename" length="200"/>
	    		<string name="orig_filename" length="200"/>
	    		<string name="alt" length="200"'.(isset($field['translatable']) ? ' translatable="true"' : '').'/>
	    		<slug name="slug" source="alt"/>
	    		<date name="upload_date"/>
				<string name="thumbnails" length="256"/>
				<number name="width"/>
				<number name="height"/>';
	    foreach ($field->children() as $child) $xml .= $child->asXML();
	    return $xml.'
	    	</object>
	    </structure>';
    }
    
    public function function_delete($field,$object,$data,$id) {
    	where('id = %d',intval($id))->delete($object['stack'].'>'.$field['name']);
    	redirect($_SERVER['HTTP_REFERER']);
    }
    
    public function function_comments($field,$object,$data,$id) {
    	if ($row = where('id = %d',intval($id))->get_row($object['stack'].'/'.$field['name'])) {
    	
    		$commentsfield = reset($field->xpath('object'));
    		if (!$commentsfield) error(404);
    		
			$commentsfield['stack'] = $object['stack'].'/'.$field['name'].'/'.$commentsfield['name'];
	    	$commentsquery = where($field['name'].'_id',intval($id));
			$commentsamount = $commentsquery->count_rows($commentsfield['stack']);
	    	$commentsdata = $commentsquery->get($commentsfield['stack']);
    		
    		$segments = array_slice(func_get_args(),4);
    		if (count($segments)) {
	    		if (!FW4_Admin::handle_item($segments,$data)) error(404);
    		} else {
	    			    	
		    	echo view("head",array(
					"pages" => FW4_Admin::get_pages(),
					"title" => isset($commentsfield['title'])?$commentsfield['title']:'Comments',
					"user" => FW4_User::get_user(),
					"site" => current_site()
				));
				
				echo '<h2>'.(isset($commentsfield['title'])?$commentsfield['title']:'Comments').'</h2>';
				
				echo '<div class="input"><fieldset>';
				echo '<img src="'.$row->scale(100,100).'" class="thumbnail" style="display:inline-block;vertical-align:middle;margin-right:10px;"/><div style="display:inline-block;vertical-align:middle;">'.$row['orig_filename'].'</div>';
				echo '</fieldset></div>';
		    	
		    	unset($commentsfield['title']);
		    	
		    	FW4_Admin::print_object_list($commentsfield,$commentsdata,$commentsamount,intval($id));
		    	
		    	echo '<div class="controls">';
		    	echo '<a class="button save" href="'.preg_replace('/[^\/]+\/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI']).'">'.l(array('nl'=>'Terug','fr'=>'Retour','en'=>'Back')).'</a>';
		    	echo '</div>';
				
				echo view("foot",array(
					'scripts' => array()
				));
				
			}
	    	
    	} else error(404);
    }
    
    public function get_scripts() {
	    return <<<SCRIPTS
	    <script src="/system/admin/js/fileuploader.js"></script>
		<script>
			$('.imagestypeuploader').each(function(){
				var container = $(this);
				var uploader = new qq.FileUploader({
					element: this,
				    action: $(this).data('fieldname')+'/qq_upload/',
				    inputName: 'imagesuploader-'+$(this).data('fieldname'),
				    allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
					multiple: true,
					uploadButtonText: 'Afbeeldingen toevoegen',
					cancelButtonText: 'Annuleer',        
					failUploadText: 'Upload mislukt',
					template: '<div class="qq-uploader">' + 
							'<ul class="qq-upload-list"></ul>' + 
			                '<div class="qq-upload-drop-area"><span>{dragText}</span></div>' +
			                '<div class="tablecontrols">' +
			                '<a class="button qq-upload-button">{uploadButtonText}</a>' +
			                '</div>' +
			             '</div>',
			        fileTemplate: '<li>' +
			        		'<span class="qq-upload-thumbnail"></span>' +
			        		'<span class="qq-upload-file"></span>' +
			                '<span class="qq-upload-size"></span>' +
			                '<span class="qq-upload-spinner"></span>' +
			                '<a class="qq-upload-cancel" href="#">{cancelButtonText}</a>' +
			                '<a class="delete" href="#"><img alt="" title="" src="/system/admin/images/del.png" width="22" height="23"/></a>' +
			                '<span class="qq-progress-bar-container"><span class="qq-progress-bar"></span></span>' +
			                '<span class="qq-upload-failed-text">{failUploadtext}</span>' +
			                '<input type="text" class="required" name="'+$(this).data('fieldname')+'-alt[]" placeholder="Beschrijving van de afbeelding" required="required"/>' +
			                '<input class="qq-filename" type="hidden" name="'+$(this).data('fieldname')+'[]"/>' +
			                '<input class="qq-orig-filename" type="hidden" name="'+$(this).data('fieldname')+'-name[]"/>' +
			            '</li>',
					messages: {
					    typeError: "{file} heeft een ongeldig formaat. Alleen {extensions} is toegelaten.",
					    sizeError: "{file} is te groot.",
					    minSizeError: "{file} is te klein.",
					    emptyError: "{file} is leeg.",
					    onLeave: "Er word een bestand geupload."            
					},
					onComplete: function(id, fileName, responseJSON){
						if (!responseJSON.error) {
							var item = uploader._getItemByFileId(id);
							$(item).find('.qq-upload-thumbnail').css({
								background: 'url('+responseJSON.thumbnail+') center no-repeat'
							});
							$(item).find('input.qq-filename').val(responseJSON.filename);
							$(item).find('input.qq-orig-filename').val(responseJSON.orig_filename);
							$(item).find('a.delete').click(function(){
								$(this).parents('li').remove();
							});
						}
					},
					onSubmit: function(id,fileName) {
						container.hide();
						container.get(0).style.cssText += ';-webkit-transform:rotateZ(0deg)';
						container.get(0).offsetHeight;
						container.get(0).style.cssText += ';-webkit-transform:none';
						container.show();
					}
				});
			});
		</script>
SCRIPTS;
		
	}

}
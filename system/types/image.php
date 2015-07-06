<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Image_type extends FW4_Type {

    public function print_field($field,$data,$object) { 
    	$fieldname = strval($field['name']);
    	$orig_filename = $fieldname.'_orig_filename';
    	$alt = $fieldname.'_alt';
    	if (isset($field['is_viewing_version']) && $field['is_viewing_version']) {
    		return false;
    	}?>
    	<div class="input">
	    	<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
    		<? if (isset($data->$fieldname) && $data->$fieldname): ?>
    			<table class="list imagelist" width="100%" cellpadding="0" cellspacing="0">
    				<td width="0" valign="top">
    					<div style="background:url('<?=$data->$fieldname->cover(50,50)?>')" class="image-thumbnail"></div>
    				</td>
    				<td width="100%">
    					<div><?=$data->$orig_filename?></div>
    					<input type="text" class="required" name="<?=$field['name']?>-alt[]" placeholder="Beschrijving van de afbeelding" value="<?=e($data->$alt)?>" required="required"/>
    				</td>
    				<? if (!isset($field['required'])):?><td width="0"><a class="delete" href="<?=$field['name']?>/delete/<?=$data->id?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u deze afbeelding wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette image?','en'=>'Are you sure you want to delete this image?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"/></a></td><? endif; ?>
    			</table>
    		<? endif; ?>
    		<div class="imagetypeuploader" data-fieldname="<?=$field['name']?>"></div>
    	</div><?
    }
    
    public function on_fetch($field,$data) {
	    if (!isset($field['fieldname'])) return;
	    $fieldname = strval($field['fieldname']);
	    $fieldname_filename = strval($field['fieldname']).'_filename';
	    $fieldname_slug = strval($field['fieldname']).'_slug';
	    $fieldname_alt = strval($field['fieldname']).'_alt';
	    $fieldname_thumbnails = strval($field['fieldname']).'_thumbnails';
	    $path = explode('>',isset($field['path']) ? $field['path'] : $field['imagestack']);
	    array_pop($path);
	    $path = implode('>',$path);
	    if ($data->$fieldname_filename) {
		    $data->$fieldname = Image::image_with_data($data->id,$data->$fieldname_filename,$data->$fieldname_slug,$data->$fieldname_alt,$field['imagestack'],$path,$data->$fieldname_thumbnails,$fieldname_thumbnails);
	    } else {
		    $data->$fieldname = false;
		}
    }
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		$fieldname = strval($field['name']);
    	$field_filename = $fieldname.'_filename';
    	$orig_filename = $fieldname.'_orig_filename';
    	$upload_date = $fieldname.'_upload_date';
    	$alt = $fieldname.'_alt';
    	
    	if (isset($olddata->$field_filename) && $olddata->$field_filename) {
	    	$data[$alt] = array_shift($_POST[$fieldname.'-alt']); 
		}
		
		if (isset($_POST[$fieldname]) && is_array($_POST[$fieldname]) && count($_POST[$fieldname])) {
	    	foreach ($_POST[$fieldname] as $filename) {
	    		
	    		if (!$filename || !file_exists(FILESPATH.'uploaded-images/'.$filename)) continue;
	    		
	    		if (isset($olddata->$field_filename) && $olddata->$field_filename) {
	    			$this->deleted($field,array($olddata));
	    		}
	    		
	    		$extension = substr($filename, strrpos($filename, '.')+1);
				do {
					$name = md5(rand(0,99999).rand(0,99999));
				} while (file_exists(FILESPATH.$name.".".$extension));
				
				rename(FILESPATH.'uploaded-images/'.$filename,FILESPATH.$name.".".$extension);
				
				$data[$orig_filename] = array_shift($_POST[$fieldname.'-name']);
				$data[$alt] = array_shift($_POST[$fieldname.'-alt']);
				$data[$field_filename] = $name.'.'.$extension;
				$data[$upload_date] = time();
	    	}
    	}
    	
		return $data;
	}
    
    public function deleted($field,$data) {
	    $fieldname = strval($field['name']);
    	$filename = $fieldname.'_filename';
	    foreach ($data as $row) {
		    if ($row->$filename) {
			    $row->$fieldname->clear_thumbnails();
				@unlink(FILESPATH.$row->$filename);
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
	    if (isset($result['filename'])) {
		    if ($result['extension'] == 'png' && class_exists('Imagick')) {
			    $imagick = new Imagick(FILESPATH.'uploaded-images/'.$result['filename']);
			    $alpha = $imagick->getImageAlphaChannel();
			    $mean = $imagick->getImageChannelMean(imagick::CHANNEL_ALPHA);
				if ($alpha == imagick::ALPHACHANNEL_UNDEFINED || $mean['standardDeviation'] == 0 || is_nan($mean['standardDeviation'])) {
					$imagick->setImageFormat('jpg');
					$imagick->writeImage(FILESPATH.'uploaded-images/'.$result['name'].'.jpg');
					@unlink(FILESPATH.'uploaded-images/'.$result['filename']);
					$result['filename'] = $result['name'].'.jpg';
				}
		    }
		    $result['thumbnail'] = '/'.UPLOADSDIR.'/uploaded-images/'.$result['filename'];
		}
	    
	    echo json_encode($result);
    }
    
    function get_structure($field,$fields) {
	    return '<structure>
    		<string name="'.$field['name'].'_filename" fieldname="'.$field['name'].'" imagestack="'.$fields['stack'].'>'.$field['name'].'" length="200" type_name="image"/>
    		<string name="'.$field['name'].'_orig_filename" length="200"/>
    		<string name="'.$field['name'].'_alt" length="200"/>
    		<slug name="'.$field['name'].'_slug" source="'.$field['name'].'_alt"/>
    		<date name="'.$field['name'].'_upload_date"/>
    		<string name="'.$field['name'].'_thumbnails" length="256"/>
    		<number name="'.$field['name'].'_width"/>
    		<number name="'.$field['name'].'_height"/>
	    </structure>';
    }
    
    public function function_delete($field,$object,$data,$id) {
    	$fieldname = strval($field['name']);
    	$field_filename = strval($field['name']).'_filename';
    	if (is_object($data)) {
	    	if ($data->$fieldname) {
		    	$data->$fieldname->clear_thumbnails();
	    	}
	    	@unlink(FILESPATH.$data->$field_filename);
	    	where('id = %d',$data->id)->update($object['stack'],array(
	    		$fieldname.'_filename' => '',
	    		$fieldname.'_orig_filename' => '',
	    		$fieldname.'_upload_date' => ''
	    	));
    		redirect($_SERVER['HTTP_REFERER']);
    	} else error(404);
    }
    
    public function summary($field,$data,$object) {
    	$fieldname = strval($field['name']);
    	if ($data->$fieldname) return '<img style="border:1px solid #777;border-radius:2px;box-shadow:0 1px 1px #666;margin-bottom:-2px" src="'.$data->$fieldname->contain(40,24).'" width="'.$data->$fieldname->width().'" height="'.$data->$fieldname->height().'"/>';
        return '';
    }
    
    public function get_scripts() {
	    return <<<SCRIPTS
	    <script src="/system/admin/js/fileuploader.js"></script>
		<script>
			$('.imagetypeuploader').each(function(){
				var container = $(this);
				var uploadButtonText = 'Afbeelding toevoegen';
				if (container.prev('table.list').length > 0) uploadButtonText = 'Afbeelding wijzigen';
				var uploader = new qq.FileUploader({
					element: this,
				    action: $(this).data('fieldname')+'/qq_upload/',
				    inputName: 'imagesuploader-'+$(this).data('fieldname'),
				    allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
					multiple: false,
					uploadButtonText: uploadButtonText,
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
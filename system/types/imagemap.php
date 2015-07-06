<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Imagemap extends Choice_abstract {

    public function print_field($field,$data,$object) {
		
		$source_rows = self::get_source_rows(strval($field['source']),$field);
		if ($source_rows === false) return false;
		
		$source_structure = FW4_Structure::get_object_structure($field['source'],false);
		$source_title = isset($source_structure['title']) ? $source_structure['title'] : 'aandachtspunten';
		
		$fieldname = strval($field['name']);
		$imagefieldname = $fieldname.'_image';
    	$orig_filename = $imagefieldname.'_orig_filename';
    	$alt = $imagefieldname.'_alt';
    	if (isset($field['is_viewing_version']) && $field['is_viewing_version']) {
    		return false;
    	}
    	
    	$options = '';
    	if (isset($field['source'])) {
	    	if (!$structure = FW4_Structure::get_object_structure(strval($field['source']),false)) return false;
			$titlefields = $structure->xpath('string');
			if (!($titlefield = reset($titlefields))) {
				$titlefields = $structure->xpath('number');
				if (!($titlefield = reset($titlefields))) return false;
			}
			$titlefield = strval($titlefield['name']);
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
				usort($source_rows,function ($a,$b) use ($parenttitlefield) {
					if ($a->parent()->$parenttitlefield == $b->parent()->$parenttitlefield) {
				        return 0;
				    }
				    return ($a->parent()->$parenttitlefield < $b->parent()->$parenttitlefield) ? -1 : 1;
				});
		    }
		    foreach ($source_rows as $child) {
		    	$value = $child->id;
		    	if (isset($field['group_by']) && $group_field = strval($field['group_by']) && $child->$group_field != $group_value) {
		    		if ($group_value) $options .= '</optgroup>';
		    		$group_value = $actual_group_value = $child[strval($field['group_by'])];
		    		if ($group_is_choice) $actual_group_value = $group_values[$group_value];
		    		$options .= '<optgroup label="'.$actual_group_value.'">';
		    	} else if (isset($field['optgroup']) && $child->parent()->$parenttitlefield != $group_value) {
		    		if ($group_value) $options .= '</optgroup>';
		    		$group_value = $child->parent()->$parenttitlefield;
		    		$options .= '<optgroup label="'.$group_value.'">';
		    	}
		    	$options .= '<option value="'.$value.'">';
		    	if (isset($field['format'])) {
			    	$displayvalue = $field['format'];
				    preg_match_all('/\[([a-z0-9\_]+)\]/is',$field['format'],$matches,PREG_SET_ORDER);
				    foreach ($matches as $match) {
					    $match_name = $match[1];
					    $displayvalue = str_ireplace($match[0],$child->$match_name,$displayvalue);
				    }
				    $options .= $displayvalue;
			    } else {
				    $options .= htmlentities($child->$titlefield);
				}
		    	$options .= '</option>';
	    	}
	    	if ($group_value) $options .= '</optgroup>';
	    } else {
		    foreach ($field->children() as $child) {
		    	$value = isset($child['value'])?$child['value']:strval($child);
		    	$options .= '<option value="'.$value.'">'.strval($child).'</option>';
	    	}
	    }
	    
	    $existing_areas = array();
	    if (isset($data->id) && $data->id) {
			$areas_fieldname = $field['name'].'_area';
			$filename_fieldname = $field['name'].'_image_filename';
			if ($data->$filename_fieldname) {
			    $width_field = $field['name'].'_image_width';
				$height_field = $field['name'].'_image_height';
				$canvas_width = $canvas_height = 800;
				if ($data->$width_field > $data->$height_field) {
					$canvas_height = ($data->$height_field/$data->$width_field) * $canvas_width;
				} else {
					$canvas_width = ($data->$width_field/$data->$height_field) * $canvas_height;
				}
			    foreach ($data->$areas_fieldname as $existing_area) {
				    $existing_areas[] = array(
					    'left' => ($existing_area->topx)*$canvas_width/100,
					    'top' => ($existing_area->topy)*$canvas_height/100,
					    'width' => ($existing_area->bottomx)*$canvas_width/100 - ($existing_area->topx)*$canvas_width/100,
					    'height' => ($existing_area->bottomy)*$canvas_height/100 - ($existing_area->topy)*$canvas_height/100,
					    'value' => intval($existing_area->value_id)
				    );
				}
			}
		}
    	?>
    	<div class="input" data-options="<?=str_replace('"', "'", $options)?>">
	    	<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
    		<? if (isset($data->id) && isset($data->$imagefieldname) && $data->$imagefieldname): ?>
    			<table class="list imagelist" width="100%" cellpadding="0" cellspacing="0">
    				<td width="0" valign="top">
    					<div style="background:url('<?=$data->$imagefieldname->cover(50,50)?>')" class="image-thumbnail"></div>
    				</td>
    				<td width="100%">
    					<div><?=$data->$orig_filename?></div>
    					<div><a class="button areas" href="#" data-image="<?=$data->$imagefieldname->contain(800,800)?>">Bepaal <?=strtolower($source_title)?></a></div>
    					<input class="areas-input" type="hidden" name="<?=$field['name']?>-areas" value='<?=json_encode($existing_areas)?>'/>
    				</td>
    				<? if (!isset($field['required'])):?><td width="0"><a class="delete" href="<?=$field['name']?>/delete/<?=$data->id?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u deze afbeelding wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette image?','en'=>'Are you sure you want to delete this image?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"/></a></td><? endif; ?>
    			</table>
    		<? endif; ?>
    		<div class="imagemaptypeuploader" data-fieldname="<?=$field['name']?>" data-title="<?=$source_title?>"></div>
    	</div><?
    }
    
    public function on_fetch($field,$data) {
	    $fieldname = substr(strval($field['name']), 0, strrpos(strval($field['name']), '_') );
	    if (strval($field['name']) != 'map_image' || isset($data->$fieldname)) return;
	    $area_fieldname = $fieldname.'_area';
	    $fieldname_image = $fieldname.'_image';
	    $fieldname_filename = $fieldname_image.'_filename';
	    $fieldname_slug = $fieldname_image.'_slug';
	    $fieldname_alt = $fieldname_image.'_alt';
	    $fieldname_thumbnails = $fieldname_image.'_thumbnails';
	    $path = explode('>',$field['path']);
	    array_pop($path);
	    $path = implode('>',$path);
	    if ($data->$fieldname_filename) {
		    $obj = new stdClass();
		    $obj->image = Image::image_with_data($data->id,$data->$fieldname_filename,$data->$fieldname_slug,$data->$fieldname_alt,$path.'>'.$fieldname_image,$path,$data->$fieldname_thumbnails,$fieldname_thumbnails);
		    $obj->areas = $data->$area_fieldname;
		    foreach ($obj->areas as $area) {
			    $area->topx = floatval($area->topx);
			    $area->topy = floatval($area->topy);
			    $area->bottomx = floatval($area->bottomx);
			    $area->bottomy = floatval($area->bottomy);
		    }
		    $data->$fieldname = $obj;
	    } else {
		    $data->$fieldname = false;
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
			<image name="'.$field['name'].'_image"/>
    		<object name="'.$field['name'].'_area">
				<double name="topx"/>
				<double name="topy"/>
				<double name="bottomx"/>
				<double name="bottomy"/>
				<choice name="value" source="'.$field['source'].'"></choice>
    		</object>
		</structure>';
    }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
		
		$fieldname = $field['name'].'_image';
    	$field_filename = $fieldname.'_filename';
    	$orig_filename = $fieldname.'_orig_filename';
    	$upload_date = $fieldname.'_upload_date';
    	$alt = $fieldname.'_alt';
    	
    	$titlefields = $object->xpath('string');
    	if ($titlefield = reset($titlefields)) $titlefield = strval($titlefield['name']);
    	
    	if ($titlefield && isset($data[$titlefield]) && $data[$titlefield]) {
			
			if (!isset($olddata->$alt) || (isset($olddata->$alt) && $olddata->$alt != $data[$titlefield])) {
    		
	    		$i = 1;
				$slug_field = $fieldname.'_slug';
	    		
	    		if (isset($olddata->$alt) && $olddata->$alt) {
	    			if (preg_match('/.*\-([0-9]+)/is',$olddata->$slug_field,$match)) {
		    			$i = intval($match[1]);
		    			$i++;
	    			}
	    		}
	    		
				$data[$alt] = $data[$titlefield];
				$data[$slug_field] = slug_format($data[$alt]).'-'.$i;
				
				if ($olddata->$fieldname) $olddata->$fieldname->clear_thumbnails();
			}
		}
		
		if (isset($_POST[strval($field['name'])]) && is_array($_POST[strval($field['name'])]) && count($_POST[strval($field['name'])])) {
	    	foreach ($_POST[strval($field['name'])] as $filename) {
	    		
	    		if (!$filename || !file_exists(FILESPATH.'uploaded-images/'.$filename)) continue;
	    		
		    	$width_field = $fieldname.'_width';
		    	$height_field = $fieldname.'_height';
    	
	    		$folder = str_replace('>','/',$object['path']);
	    		if (!file_exists(FILESPATH.$folder)) mkdir(FILESPATH.$folder);
	    		$folder .= '/';
	    		
	    		$image_size = @getimagesize(FILESPATH.'uploaded-images/'.$filename);
	    		if ($image_size === false) continue;
	    		
	    		$extension = substr($filename, strrpos($filename, '.')+1);
				do {
					$name = md5(rand(0,99999).rand(0,99999));
				} while (file_exists(FILESPATH.$folder.$name.".".$extension));
	    		
	    		if (isset($olddata->$field_filename) && $olddata->$field_filename) {
	    			$this->deleted($field,array($olddata));
	    		}
				
				rename(FILESPATH.'uploaded-images/'.$filename,FILESPATH.$folder.$name.".".$extension);
				
				$data[$orig_filename] = array_shift($_POST[strval($field['name']).'-name']);
				$data[$field_filename] = $folder.$name.'.'.$extension;
				$data[$width_field] = $image_size[0];
				$data[$height_field] = $image_size[1];
				
				$slug_field = $fieldname.'_slug';
				if ($titlefield && isset($data[$titlefield]) && $data[$titlefield] && isset($olddata->$alt) && $olddata->$alt == $data[$titlefield]) {
					$i = 1;
		    		
	    			if (preg_match('/.*\-([0-9]+)/is',$olddata->$slug_field,$match)) {
		    			$i = intval($match[1]);
		    			$i++;
	    			}
		    		
					$data[$slug_field] = slug_format($data[$titlefield]).'-'.$i;
				}
	    	}
    	}
    	
		return $data;
	}
	
	public function edited($field,$data,$object) {
		if (isset($_POST[$field['name'].'-areas'])) {
			$areas = json_decode($_POST[$field['name'].'-areas']);
			$areas_fieldname = $field['name'].'_area';
			$area_stack = $object['path'].'>'.$areas_fieldname;
			$width_field = $field['name'].'_image_width';
			$height_field = $field['name'].'_image_height';
			$canvas_width = $canvas_height = 800;
			if ($data->$width_field > $data->$height_field) {
				$canvas_height = ($data->$height_field/$data->$width_field) * $canvas_width;
			} else {
				$canvas_width = ($data->$width/$data->$height_field) * $canvas_height;
			}
			foreach ($data->$areas_fieldname as $existing_area) {
				foreach ($areas as $key => $area) {
					$top_perc = $area->top / $canvas_height * 100;
					$left_perc = $area->left / $canvas_width * 100;
					$bottom_perc = ($area->top + $area->height) / $canvas_height * 100;
					$right_perc = ($area->left + $area->width) / $canvas_width * 100;
					if ($left_perc == $existing_area->topx && $top_perc == $existing_area->topy && $right_perc == $existing_area->bottomx && $bottom_perc == $existing_area->bottomy) {
						if ($area->value != $existing_area->value_id) {
							where('id = %d',$existing_area->id)->update($area_stack,array(
								'value_id' => $area->value
							));
						}
						unset($areas[$key]);
						continue 2;
					}
				}
				where('id = %d',$existing_area->id)->delete($area_stack);
			}
			foreach ($areas as $key => $area) {
				$top_perc = $area->top / $canvas_height * 100;
				$left_perc = $area->left / $canvas_width * 100;
				$bottom_perc = ($area->top + $area->height) / $canvas_height * 100;
				$right_perc = ($area->left + $area->width) / $canvas_width * 100;
				insert($area_stack,array(
					$object['name'].'_id' => $data->id,
					'topx' => $left_perc,
					'topy' => $top_perc,
					'bottomx' => $right_perc,
					'bottomy' => $bottom_perc,
					'value_id' => $area->value
				));
			}
		}
	}
    
    public function function_delete($field,$object,$data,$id) {
    	$fieldname = strval($field['name']);
    	$field_image = strval($field['name']).'_image';
    	$field_filename = $field_image.'_filename';
    	if (is_object($data)) {
	    	if ($data->$fieldname && $data->$fieldname->image) {
		    	$data->$fieldname->image->clear_thumbnails();
	    	}
	    	@unlink(FILESPATH.$data->$field_filename);
	    	where($object['name'].'_id = %d',$data->id)->delete($object['stack'].'>'.$fieldname.'_area');
	    	where('id = %d',$data->id)->update($object['stack'],array(
	    		$field_image.'_filename' => '',
	    		$field_image.'_orig_filename' => '',
	    		$field_image.'_upload_date' => ''
	    	));
    		redirect($_SERVER['HTTP_REFERER']);
    	} else error(404);
    }
    
    public function deleted($field,$data) {
	    $fieldname = strval($field['name']);
    	$filename = $fieldname.'_image_filename';
	    foreach ($data as $row) {
		    if (isset($row->$filename) && $row->$filename && isset($row->$fieldname->image)) {
			    $row->$fieldname->image->clear_thumbnails();
				@unlink(FILESPATH.$row->$filename);
			}
	    }
    }
	
    public function get_scripts() {
	    return <<<SCRIPTS
	    <script src="/system/admin/js/fileuploader.js"></script>
		<script>
			var imagemapSaved = true;
			var imagemapIsDragging = false;
			var imagemapIsMoving = false;
			var imagemapIsResizing = false;
			var imagemapStartX = 0;
			var imagemapStartY = 0;
			var imagemapWidth = 0;
			var imagemapHeight = 0;
			var imagemapCurrentArea;
			var imagemapCurrentInput;
			$(document).mousemove(function(e){
				if (imagemapIsDragging || imagemapIsResizing) {
					var offset = $('#imagemapcanvas').offset();
				    imagemapWidth = Math.max( Math.min( e.pageX - offset.left - imagemapStartX, $('#imagemapcanvas').width() - imagemapStartX - 2), - imagemapStartX);
				    imagemapHeight = Math.max( Math.min( e.pageY - offset.top - imagemapStartY, $('#imagemapcanvas').height() - imagemapStartY - 2), - imagemapStartY);
				    if (imagemapWidth < 0) {
					    imagemapWidth = -imagemapWidth;
					    imagemapCurrentArea.css({
							left: imagemapStartX - imagemapWidth
						});
					}
				    if (imagemapHeight < 0) {
					    imagemapHeight = -imagemapHeight;
					    imagemapCurrentArea.css({
							top: imagemapStartY - imagemapHeight
						});
					}
				    imagemapCurrentArea.css({
						width: imagemapWidth,
						height: imagemapHeight
					});
				} else if (imagemapIsMoving) {
					var offset = $('#imagemapcanvas').offset();
				    imagemapStartX = Math.min( Math.max(0, e.pageX - offset.left), $('#imagemapcanvas').width() - imagemapCurrentArea.width() - 2);
				    imagemapStartY = Math.min( Math.max(0, e.pageY - offset.top), $('#imagemapcanvas').height() - imagemapCurrentArea.height() - 2);
				    imagemapCurrentArea.css({
						left: imagemapStartX,
						top: imagemapStartY
					});
				}
			});
			$(document).mouseup(function(e){
				if (imagemapIsDragging || imagemapIsResizing) {
					if (imagemapWidth < 15 || imagemapHeight < 15) {
						imagemapCurrentArea.remove();
					} else if (imagemapIsDragging) {
						setupImagemapArea(imagemapCurrentArea);
						showImagemapDropdown();
						imagemapSaved = false;
					}
					imagemapIsDragging = false;
					imagemapIsResizing = false;
					e.stopPropagation();
					e.stopImmediatePropagation();
					return false;
				}
				if (imagemapIsMoving) {
					imagemapSaved = false;
					imagemapIsMoving = false;
					e.stopPropagation();
					e.stopImmediatePropagation();
					return false;
				}
			});
			function setupImagemapButton(button) {
				button.click(function(){
					imagemapCurrentInput = $(this).parents('div').siblings('.areas-input');
					var options = button.parents('.input').data('options');
					var content = '<div id="imagemappopover"><div id="imagemapimage"><div id="imagemapcanvas"><img src="'+button.data('image')+'"/></div><div id="imagemapselector"><select>'+options+'</select><a class="button save" href="#">ok</a></div></div><div class="controls"><a class="button save" href="#">Klaar</a><div class="help">Klik en sleep om een punt toe te voegen</div></div></div>';
					$.fancybox({
						padding		: 0,
						type		: 'html',
						fitToView	: false,
						autoSize	: true,
						closeClick	: false,
						openEffect	: 'fade',
						closeEffect	: 'fade',
						content		: content,
						helpers		: {
							overlay : {
								closeClick : false,
					            css : {
						            'background' : 'rgba(30,30,30,0.8)'
					            }
					        },
							media : {}
						},
						beforeClose: function() {
							if (!imagemapSaved)	{
								var result = confirm('Bent u zeker dat u de wijzigingen wilt annuleren?');
								if (result) imagemapSaved = true;
								return result;
							}
						},
						beforeShow: function() {
							var areas = JSON.parse(imagemapCurrentInput.val());
							$.each(areas,function(){
								var newArea = $('<div class="imagemaparea" data-value="'+this.value+'"><div class="mover"></div><div class="sizer"></div><div class="del"></div></div>');
								newArea.appendTo($('#imagemapcanvas'));
								newArea.css({
									left: this.left,
									top: this.top,
									width: this.width,
									height: this.height
								});
								setupImagemapArea(newArea);
							});
							$('#imagemappopover .controls .save').click(function(){
								var areas = [];
								$('#imagemapcanvas .imagemaparea').each(function(){
									var position = $(this).position();
									areas.push({
										left: position.left,
										top: position.top,
										width: $(this).width(),
										height: $(this).height(),
										value: parseInt($(this).data('value'),10)
									});
								});
								imagemapCurrentInput.val(JSON.stringify(areas));
								imagemapSaved = true;
								$.fancybox.close();
								return false;
							});
							$('#imagemappopover select').uniform({selectAutoWidth:false});
							$('#imagemapselector select').change(function(){
								imagemapCurrentArea.data('value',$('#imagemapselector select').val());
								imagemapSaved = false;
							});
							$('#imagemapcanvas').mousedown(function(e){
								hideImagemapDropdown();
								if (e.which == 1) {
									var offset = $('#imagemapcanvas').offset();
								    imagemapStartX = e.pageX - offset.left;
								    imagemapStartY = e.pageY - offset.top;
								    imagemapWidth = 0;
								    imagemapHeight = 0;
									imagemapCurrentArea = $('<div class="imagemaparea" data-value="'+$('#imagemapselector select option:first').attr('value')+'"><div class="mover"></div><div class="sizer"></div><div class="del"></div></div>');
									imagemapCurrentArea.appendTo($('#imagemapcanvas'));
									imagemapCurrentArea.css({
										left: imagemapStartX,
										top: imagemapStartY
									});
									imagemapIsDragging = true;
								}
								return false;
							});
							$('#imagemapselector .button').click(function(){
								hideImagemapDropdown();
								return false;
							});
						}
					});
					return false;
				});				
			}
			function setupImagemapArea(area) {
				area.mousedown(function(e){
					e.stopPropagation();
				});
				area.click(function(){
					imagemapCurrentArea = area;
					showImagemapDropdown();
				});
				area.find('.mover').mousedown(function(e){
					hideImagemapDropdown();
					if (e.which == 1) {
						e.stopPropagation();
						e.stopImmediatePropagation();
						imagemapIsMoving = true;
						imagemapCurrentArea = area;
					}
					return false;
				});
				area.find('.mover,.sizer').click(function(e){
					e.stopPropagation();
					e.stopImmediatePropagation();
				});
				area.find('.del').click(function(e){
					hideImagemapDropdown();
					imagemapCurrentArea = false;
					area.remove();
					e.stopPropagation();
					e.stopImmediatePropagation();
				});
				area.find('.sizer').mousedown(function(e){
					hideImagemapDropdown();
					if (e.which == 1) {
						e.stopPropagation();
						e.stopImmediatePropagation();
						var position = area.position();
						imagemapStartX = position.left;
					    imagemapStartY = position.top;
						imagemapIsResizing = true;
						imagemapCurrentArea = area;
					}
					return false;
				});
			}
			function showImagemapDropdown() {
				$('#imagemapcanvas .imagemaparea.active').removeClass('active');
				if (imagemapCurrentArea.data('value')) $('#imagemapselector select').val(imagemapCurrentArea.data('value'));
				else $('#imagemapselector select').val($('#imagemapselector select option:first').attr('value'));
				$('#imagemapselector select').uniform('update');
				var offset = imagemapCurrentArea.position();
				var canvasOffset = $('#imagemapcanvas').position();
				var dropdownX = offset.left + canvasOffset.left + imagemapCurrentArea.width()/2 - $('#imagemapselector').outerWidth(true)/2;
				$('#imagemapselector').removeClass('right left');
				if (dropdownX < canvasOffset.left + 10) {
					$('#imagemapselector').addClass('left').css({
						left: offset.left + canvasOffset.left + imagemapCurrentArea.width(),
						top: offset.top + canvasOffset.top + imagemapCurrentArea.height()/2 - $('#imagemapselector').outerHeight(true)/2
					});
				} else if (dropdownX + $('#imagemapselector').outerWidth(true) > canvasOffset.left + $('#imagemapcanvas').outerWidth() - 10) {
					$('#imagemapselector').addClass('right').css({
						left: offset.left + canvasOffset.left - $('#imagemapselector').outerWidth(true),
						top: offset.top + canvasOffset.top + imagemapCurrentArea.height()/2 - $('#imagemapselector').outerHeight(true)/2
					});
				} else {
					$('#imagemapselector').css({
						left: dropdownX,
						top: offset.top + canvasOffset.top + imagemapCurrentArea.height()
					});
				}
				imagemapCurrentArea.addClass('active');
				$('#imagemapselector').fadeIn(200);
			}
			function hideImagemapDropdown() {
				if (imagemapCurrentArea) imagemapCurrentArea.removeClass('active');
				$('#imagemapselector').fadeOut(100);
			}
			setupImagemapButton($('.imagelist a.button.areas'));
			$('.imagemaptypeuploader').each(function(){
				var container = $(this);
				var fieldname = $(this).data('fieldname');
				var uploadButtonText = 'Afbeelding toevoegen';
				if (container.prev('table.list').length > 0) uploadButtonText = 'Afbeelding wijzigen';
				var uploader = new qq.FileUploader({
					element: this,
				    action: $(this).data('fieldname')+'/qq_upload/',
				    inputName: 'imagesuploader-'+fieldname,
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
			                '<div><a class="button areas" href="#">Bepaal '+$(this).data('title')+'</a></div>' +
			                '<input class="areas-input" type="hidden" name="'+fieldname+'-areas" value="[]"/>' +
			                '<input class="qq-filename" type="hidden" name="'+fieldname+'[]"/>' +
			                '<input class="qq-orig-filename" type="hidden" name="'+fieldname+'-name[]"/>' +
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
							$(item).find('a.button.areas').attr('data-image',responseJSON.thumbnail);
							setupImagemapButton($(item).find('a.button.areas'));
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
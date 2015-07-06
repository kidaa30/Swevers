<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Virtualvisit_type extends FW4_Type {

    public function print_field($field,$data,$object) {
	    $fieldname = strval($field['name']);
	    if (isset($data->id)) {
	    	$source = strval($field['source']);
	    	$photos = array();
	    	foreach ($data->$source as $row) {
		    	$photos[] = array(
			    	'id' => $row->id,
			    	'thumbnail' => $row->cover(75,75)
		    	);
		    } ?>
	    	<div class="virtualvisit" data-photos="<?=e(json_encode($photos))?>">
		    	<div class="toprow">
			    	<table class="floors"><tbody>
				    	<? foreach ($data->$fieldname as $floor):
					    	$spots = array();
					    	foreach ($floor->spot as $spot) $spots[] = array(
						    	'positionx' => floatval($spot->positionx),
						    	'positiony' => floatval($spot->positiony),
						    	'targetx' => floatval($spot->targetx),
						    	'targety' => floatval($spot->targety),
						    	'image_id' => intval($spot->image_id),
						    	'angle' => floatval($spot->angle),
						    	'type' => intval($spot->type)
					    	); ?>
				    		<tr>
					    		<td width="1" valign="middle"><img class="sort-handle" src="/system/admin/images/sort.png" width="10" height="11"/><input type="hidden" name="floor_sorting[]" value="<?=$floor->_sort_order?>"/></td>
								<td width="1"><div class="thumbnail" style="background-image: url(<?=$floor->image->contain(70,70)?>);"></div></td>
								<td><input type="hidden" name="floor_ids[]" value="<?=$floor->id?>"/><input class="floorspots" type="hidden" name="floor_spots[]" value="<?=e(json_encode($spots))?>"/><input type="hidden" name="floor_images[]" value="<?=$floor->image_filename?>" /><input class="floornames required" name="floor_names[]" type="text" placeholder="Naam verdieping" value="<?=$floor->name?>" required="required"/><br/><a href="#" class="editlink" onclick="virtualVisitActiveRow=$(this).parents('tr');editVirtualVisitFloor($(this).parents('td').find('input.floornames').val(),'<?=$floor->image->contain(900,900)?>',$(this).parents('td').find('input.floorspots').val(),$(this).parents('.virtualvisit').data('photos'));return false;"><?=$floor->spot->count()?> foto's op deze verdieping</a> &nbsp; &nbsp; &nbsp; <input type="hidden" name="floor_main[]" value="<?=$floor->main?>"/><label><input type="checkbox"<?=$floor->main?' checked="checked"':''?> onchange="setVirtualVisitMainFloor($(this).parents('tr'));"/> Hoofdverdieping</label><img src="<?=$floor->image->contain(900,900)?>" class="bigphoto"/></td>
								<td align="right"><div style="white-space:nowrap;"><a class="delete" href="#" onclick="event.stopPropagation();if (confirm('Bent u zeker dat u deze verdieping wilt verwijderen?')) deleteVirtualVisitFloor($(this).parents('tr')); return false;"><img alt="Verwijderen" title="Verwijderen" src="/system/admin/images/del.png" width="22" height="23"></a></div></td>
							</tr>
				    	<? endforeach; ?>
			    	</tbody></table>
			    	<div class="addvirtualvisitfloor" data-fieldname="<?=$field['name']?>"></div>
		    	</div>
		    	<div class="bottomrow">
			    	
		    	</div>
	    	</div>
    	<? }
    }
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		return $data;
	}
    
    public function edited($field,$data,$object) {
    
    	use_library('files');
		
    	$fieldname = strval($field['name']);
		$stack = $object['stack'].'>'.$fieldname;
		
    	if (!isset($_POST['floor_ids'])) $_POST['floor_ids'] = array();
    	foreach ($data->$fieldname as $existing) {
	    	$index = array_search($existing->id,$_POST['floor_ids']);
	    	if ($index === false) {
		    	where('id = %d',$existing->id)->delete($stack);
	    	} else {
		    	where('id = %d',$existing->id)->update($stack,array(
			    	'name' => $_POST['floor_names'][$index],
			    	'image_alt' => $_POST['floor_names'][$index],
			    	'main' => intval($_POST['floor_main'][$index]),
			    	'_sort_order' => $_POST['floor_sorting'][$index]
		    	));
		    	$spots = json_decode($_POST['floor_spots'][$index]);
		    	foreach ($existing->spot as $spot) {
			    	$new_spot = array_shift($spots);
			    	if ($new_spot) {
				    	where('id = %d',$spot->id)->update($stack.'>spot',array(
					    	'image_id' => $new_spot->image_id,
					    	'angle' => $new_spot->angle,
					    	'positionx' => $new_spot->positionx,
					    	'positiony' => $new_spot->positiony,
					    	'targetx' => $new_spot->targetx,
					    	'targety' => $new_spot->targety,
					    	'type' => $new_spot->type
				    	));
			    	} else {
				    	where('id = %d',$spot->id)->delete($stack.'>spot');
			    	}
		    	}
		    	foreach ($spots as $spot) {
			    	insert($stack.'>spot',array(
				    	$fieldname.'_id' => $existing->id,
				    	'image_id' => $spot->image_id,
				    	'angle' => $spot->angle,
				    	'positionx' => $spot->positionx,
				    	'positiony' => $spot->positiony,
				    	'targetx' => $spot->targetx,
				    	'targety' => $spot->targety,
				    	'type' => $spot->type
			    	));
		    	}
		    	unset($_POST['floor_ids'][$index]);
		    	unset($_POST['floor_names'][$index]);
		    	unset($_POST['floor_images'][$index]);
		    	unset($_POST['floor_sorting'][$index]);
		    	unset($_POST['floor_spots'][$index]);
		    	unset($_POST['floor_main'][$index]);
	    	}
    	}
    	
    	foreach ($_POST['floor_names'] as $index => $floor_name) {
	    	
	    	$filename = $_POST['floor_images'][$index];
	    	if (!$filename || !file_exists(FILESPATH.'uploaded-images/'.$filename)) continue;
    		
			$extension = substr($filename, strrpos($filename, '.')+1);
			do {
				$name = md5(rand(0,99999).rand(0,99999));
			} while (file_exists(FILESPATH.$name.".".$extension));
			
			rename(FILESPATH.'uploaded-images/'.$filename,FILESPATH.$name.".".$extension);
			
	    	$floor_id = insert($stack,array(
		    	$object['name'].'_id' => $data->id,
		    	'name' => $floor_name,
		    	'image_alt' => $floor_name,
		    	'image_filename' => $name.".".$extension,
		    	'main' => intval($_POST['floor_main'][$index]),
		    	'_sort_order' => $_POST['floor_sorting'][$index]
	    	));
	    	
	    	foreach (json_decode($_POST['floor_spots'][$index]) as $spot) {
		    	insert($stack.'>spot',array(
			    	$fieldname.'_id' => $floor_id,
			    	'image_id' => $spot->image_id,
			    	'angle' => $spot->angle,
			    	'positionx' => $spot->positionx,
			    	'positiony' => $spot->positiony,
			    	'targetx' => $spot->targetx,
			    	'targety' => $spot->targety,
			    	'type' => $spot->type
		    	));
	    	}
    	}
    	
    }
    
    public function deleted($field,$data) {
	    foreach ($data as $row) {
	    	$row->image->clear_thumbnails();
	    	@unlink(FILESPATH.$row->image->filename);
	    }
    }
    
    public function get_structure($field,$fields) {
	    $source = strval($field['source']);
	    return '<structure>
	    	<object name="'.$field['name'].'" sortable="sortable">
	    		<string name="name" length="200"/>
	    		<image name="image"/>
				<bool name="main"/>
	    		<object name="spot">
	    			<choice name="image" source="'.$fields['stack'].'>'.$source.'"/>
	    			<double name="angle"/>
					<double name="positionx"/>
					<double name="positiony"/>
					<double name="targetx"/>
					<double name="targety"/>
					<choice name="type">
						<option value="1">Foto</option>
						<option value="2">Render</option>
					</choice>
	    		</object>
	    	</object>
	    </structure>';
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
    
    public function get_scripts() {
	    return <<<SCRIPTS
	    <script src="/system/admin/js/fileuploader.js"></script>
		<script>
			$('.virtualvisit tbody').sortable({
		        handle: ".sort-handle",
		        tolerance: "pointer",
		        update: function(event, ui) {
		        	var i = 0;
		        	var parent = ui.item.parents('table');
		        	parent.find('input[name="floor_sorting[]"]').each(function(index,el){
		        		$(el).val(++i);
		        	});
		        }
		    });
			$('.addvirtualvisitfloor').each(function(){
				var container = $(this);
				var uploader = new qq.FileUploader({
					element: this,
				    action: $(this).data('fieldname')+'/qq_upload/',
				    inputName: 'imagesuploader-'+$(this).data('fieldname'),
				    allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
					multiple: true,
					uploadButtonText: 'Verdieping toevoegen',
					cancelButtonText: 'Annuleer',        
					failUploadText: 'Upload mislukt',
					template: '<div class="qq-uploader">' + 
							'<ul class="qq-upload-list"></ul>' + 
			                '<div class="qq-upload-drop-area"><span>{dragText}</span></div>' +
			                '<a class="button qq-upload-button">{uploadButtonText}</a>' +
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
							var table = $(item).parents('.toprow').find('table.floors');
							table.find('tbody').append('<tr><td width="1" valign="middle"><img class="sort-handle" src="/system/admin/images/sort.png" width="10" height="11"/><input type="hidden" name="floor_sorting[]" value="" /></td>' +
								'<td width="1"><div class="thumbnail" style="background-image: url('+responseJSON.thumbnail+');"></div></td>' +
								'<td><input type="hidden" name="floor_ids[]" value=""/><input class="floorspots" type="hidden" name="floor_spots[]" value="[]"/><input type="hidden" name="floor_images[]" value="'+responseJSON.filename+'" /><input class="floornames required" name="floor_names[]" type="text" placeholder="Naam verdieping" required="required"/><br/><a href="#" class="editlink" onclick="virtualVisitActiveRow=$(this).parents(\'tr\');editVirtualVisitFloor($(this).parents(\'td\').find(\'input.floornames\').val(),\''+responseJSON.thumbnail+'\',$(this).parents(\'td\').find(\'input.floorspots\').val(),$(this).parents(\'.virtualvisit\').data(\'photos\'));return false;">0 foto\'s op deze verdieping</a> &nbsp; &nbsp; &nbsp; <input type="hidden" name="floor_main[]" value="0"/><label><input type="checkbox" onchange="setVirtualVisitMainFloor($(this).parents(\'tr\'));"/> Hoofdverdieping</label></td>'+
								'<td align="right"><div style="white-space:nowrap;"><a class="delete" href="#" onclick="event.stopPropagation();if (confirm(\'Bent u zeker dat u deze verdieping wilt verwijderen?\')) deleteVirtualVisitFloor($(this).parents(\'tr\')); return false;"><img alt="Verwijderen" title="Verwijderen" src="/system/admin/images/del.png" width="22" height="23"></a></div></td></tr>');
							$(item).remove();
							table.find('tbody').sortable('refresh');
							var i = 0;
				        	table.find('input[name="floor_sorting[]"]').each(function(index,el){
				        		$(el).val(++i);
				        	});
							table.find('tr:last-child input[name="floor_names[]"]').focus();
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
			var virtualVisitActiveOrigin = false;
			var virtualVisitActiveTarget = false;
			var virtualVisitActiveElement = false;
			var virtualVisitSaved = true;
			var virtualVisitActiveRow;
			function deleteVirtualVisitFloor(row) {
				row.remove();
				var i = 0;
	        	var parent = row.parents('table');
	        	parent.find('input[name="floor_sorting[]"]').each(function(index,el){
	        		$(el).val(++i);
	        	});
			}
			function setupVirtualVisitMarker(marker) {
				marker.find('.origin').bind('mousedown',function(e){
					if (e.which == 1) {
						$('.virtualvisitflooredit p.note').hide();
						$('.virtualvisitflooredit div.controls').show();
						marker.siblings('.marker.active').removeClass('active');
						marker.addClass('active');
						virtualVisitActiveOrigin = marker.find('.origin');
						virtualVisitActiveTarget = marker.find('.target');
						virtualVisitActiveElement = virtualVisitActiveOrigin;
						$('.virtualvisitflooredit div.photos a.active').removeClass('active');
						if (marker.data('photo')) $('.virtualvisitflooredit div.photos a[data-id="'+marker.data('photo')+'"]').addClass('active');
						$('.virtualvisitflooredit select[name="type"]').val(marker.data('type'));
					}
				});
				
				marker.find('.target').bind('mousedown',function(e){
					if (e.which == 1 && marker.hasClass('active')) {
						virtualVisitActiveOrigin = marker.find('.origin');
						virtualVisitActiveTarget = marker.find('.target');
						virtualVisitActiveElement = virtualVisitActiveTarget;
					}
				});
			}
			function setVirtualVisitMainFloor(row) {
				var checkbox = row.find('input[type="checkbox"]');
				if (checkbox.is(':checked')) {
					row.siblings('tr').find('input[type="checkbox"]').prop('checked',false);
					row.siblings('tr').find('input[name="floor_main[]"]').val(0);
					row.find('input[name="floor_main[]"]').val(1);
				} else {
					row.find('input[name="floor_main[]"]').val(0);
				}
			}
			function editVirtualVisitFloor(title,image,spots,photos) {
				var content = '<div class="virtualvisitflooredit"><div class="leftcol"><div class="content"><img src="'+image+'"/></div></div><div class="rightcol"><p class="note">Klik en sleep om een foto toe te voegen.</p>'+
						'<div class="controls"><div class="topcontrols"><label>Soort afbeelding</label><select name="type"><option value="1">Foto</option><option value="2">Digitaal voorbeeld</option></select><a class="delete red button" href="#">Punt verwijderen</a></div><div class="photos">';
				$.each(photos, function( index, value ) {
					content += '<a href="#" data-id="'+value.id+'"><img src="'+value.thumbnail+'"/></a>';
				});
				content += '</div></div><div class="bottomcontrols"><a class="button save" href="#">Opslaan</a></div></div>';
				if (typeof spots == 'string') spots = JSON.parse(spots);
				$.fancybox({
					type		: 'html',
					fitToView	: true,
					autoSize	: true,
					closeClick	: false,
					width	: 600,
					height	: 400,
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
						if (!virtualVisitSaved)	{
							var result = confirm('Bent u zeker dat u de wijzigingen wilt annuleren?');
							if (result) virtualVisitSaved = true;
							return result;
						}
					},
					beforeShow: function() {
						$.each(spots,function(){
							var marker = $('<div class="marker"><div class="origin"></div><div class="target"></div><div class="arrow"></div><div class="gradient"></div></div>');
							marker.find('.origin,.arrow,.gradient').css({
								left: ($('.virtualvisitflooredit .content').width() / 100 * this.positionx) + 'px',
								top: ($('.virtualvisitflooredit .content').height() / 100 * this.positiony) + 'px'
							});
							marker.find('.target').css({
								left: ($('.virtualvisitflooredit .content').width() / 100 * this.targetx) + 'px',
								top: ($('.virtualvisitflooredit .content').height() / 100 * this.targety) + 'px'
							});
							$('.virtualvisitflooredit .content').append(marker);
							
							setupVirtualVisitMarker(marker);
							
							marker.data('type',this.type);
							marker.data('photo',this.image_id);
							
							marker.toggleClass('render',this.type==2);
							
							var centerx = $('.virtualvisitflooredit .content').width() / 100 * this.positionx;
							var centery = $('.virtualvisitflooredit .content').height() / 100 * this.positiony;
							
							var targetx = $('.virtualvisitflooredit .content').width() / 100 * this.targetx;
							var targety = $('.virtualvisitflooredit .content').height() / 100 * this.targety;
							
							var radians = Math.atan2(targetx - centerx, targety - centery);
							var degrees = (radians * (180 / Math.PI) * -1) + 45;
							
							var distance = Math.sqrt(Math.abs((targetx - centerx)*(targetx - centerx) + (targety - centery)*(targety - centery)));
							
							marker.find('.arrow').css('height',distance+'px');
							
							marker.find('.gradient').css('-moz-transform', 'rotate('+degrees+'deg)');
							marker.find('.gradient').css('-webkit-transform', 'rotate('+degrees+'deg)');
							marker.find('.gradient').css('-o-transform', 'rotate('+degrees+'deg)');
							marker.find('.gradient').css('-ms-transform', 'rotate('+degrees+'deg)');
							marker.find('.gradient').css('transform', 'rotate('+degrees+'deg)');
							
							marker.find('.arrow').css('-moz-transform', 'rotate('+(degrees-45)+'deg)');
							marker.find('.arrow').css('-webkit-transform', 'rotate('+(degrees-45)+'deg)');
							marker.find('.arrow').css('-o-transform', 'rotate('+(degrees-45)+'deg)');
							marker.find('.arrow').css('-ms-transform', 'rotate('+(degrees-45)+'deg)');
							marker.find('.arrow').css('transform', 'rotate('+(degrees-45)+'deg)');
						});
						$('.virtualvisitflooredit select[name="type"]').change(function(){
							if (virtualVisitActiveElement) {
								virtualVisitActiveElement.parents('.marker').data('type',$(this).val());
								virtualVisitActiveElement.parents('.marker').toggleClass('render',$(this).val()==2);
							}
							virtualVisitSaved = false;
						});
						$('.virtualvisitflooredit a.delete').click(function(){
							if (virtualVisitActiveElement) {
								virtualVisitSaved = false;
								virtualVisitActiveElement.parents('.marker').remove();
								$('.virtualvisitflooredit p.note').show();
								$('.virtualvisitflooredit div.controls').hide();
							}
							return false;
						});
						$('.virtualvisitflooredit a.save').click(function(){
							var spots = [];
							virtualVisitSaved = true;
							$('.virtualvisitflooredit .content .marker').each(function(){
								if (!$(this).hasClass('active')) $(this).find('.target').show();
								
								var centerx = Math.floor($(this).find('.origin').offset().left);
								var centery = Math.floor($(this).find('.origin').offset().top);
								
								var targetx = Math.floor($(this).find('.target').offset().left)-2;
								var targety = Math.floor($(this).find('.target').offset().top)-2;
								
								var radians = Math.atan2(targetx - centerx, targety - centery);
								var degrees = (radians * (180 / Math.PI) * -1);
								
								spots.push({
									positionx: ($(this).find('.origin').position().left/$('.virtualvisitflooredit .content').width()) * 100,
									positiony: ($(this).find('.origin').position().top/$('.virtualvisitflooredit .content').height()) * 100,
									targetx: ($(this).find('.target').position().left/$('.virtualvisitflooredit .content').width()) * 100,
									targety: ($(this).find('.target').position().top/$('.virtualvisitflooredit .content').height()) * 100,
									image_id: $(this).data('photo'),
									type: $(this).data('type'),
									angle: degrees
								});
								if (!$(this).hasClass('active')) $(this).find('.target').css({display:''});
							});
							virtualVisitActiveRow.find('input[name="floor_spots[]"]').val(JSON.stringify(spots));
							virtualVisitActiveRow.find('.editlink').text(spots.length==1?"1 foto op deze verdieping":spots.length+" foto's op deze verdieping");
							$.fancybox.close();
							return false;
						});
						$('.virtualvisitflooredit .content img').bind('mousedown',function(e){
							if (e.which == 1) {
								virtualVisitSaved = false;
								$('.virtualvisitflooredit .content .marker.active').removeClass('active');
								
								var offset = $('.virtualvisitflooredit .content img').offset();
								var left = (e.pageX - offset.left);
								var top = (e.pageY - offset.top);
								var marker = $('<div class="marker active"><div class="origin"></div><div class="target"></div><div class="arrow"></div><div class="gradient"></div></div>');
								marker.find('.origin,.arrow,.gradient,.target').css({
									left: left + 'px',
									top: top + 'px'
								});
								$('.virtualvisitflooredit .content').append(marker);
								virtualVisitActiveOrigin = marker.find('.origin');
								virtualVisitActiveTarget = marker.find('.target');
								virtualVisitActiveElement = virtualVisitActiveTarget;
								
								marker.data('type',1);
								
								$('.virtualvisitflooredit p.note').hide();
								$('.virtualvisitflooredit div.controls').show();
								
								$('.virtualvisitflooredit div.photos a.active').removeClass('active');
								$('.virtualvisitflooredit select[name="type"]').val(1);
								
								setupVirtualVisitMarker(marker);
							}
						});
						$('.virtualvisitflooredit .content').bind('mousemove',function(e){
							if (e.which == 1 && virtualVisitActiveTarget) {
								virtualVisitSaved = false;
								
								if (virtualVisitActiveElement.hasClass('target')) {
									var offset = $('.virtualvisitflooredit .content').offset();
									var left = e.pageX - offset.left;
									var top = e.pageY - offset.top;
									if (left < 0 || top < 0 || left > $('.virtualvisitflooredit .content').width() || top > $('.virtualvisitflooredit .content').height()) return;
									virtualVisitActiveTarget.css({
										left: left + 'px',
										top: top + 'px'
									});
								} else {
									var offset = $('.virtualvisitflooredit .content').offset();
									var left = e.pageX - offset.left;
									var top = e.pageY - offset.top;
									if (left < 0 || top < 0 || left > $('.virtualvisitflooredit .content').width() || top > $('.virtualvisitflooredit .content').height()) return;
									virtualVisitActiveOrigin.css({
										left: left + 'px',
										top: top + 'px'
									});
									virtualVisitActiveOrigin.siblings('.gradient,.arrow').css({
										left: left + 'px',
										top: top + 'px'
									});
								}
								
								var centerx = Math.floor(virtualVisitActiveOrigin.offset().left);
								var centery = Math.floor(virtualVisitActiveOrigin.offset().top);
								
								var targetx = Math.floor(virtualVisitActiveTarget.offset().left)-2;
								var targety = Math.floor(virtualVisitActiveTarget.offset().top)-2;
								
								var radians = Math.atan2(targetx - centerx, targety - centery);
								var degrees = (radians * (180 / Math.PI) * -1) + 45;
								
								var distance = Math.sqrt(Math.abs((targetx - centerx)*(targetx - centerx) + (targety - centery)*(targety - centery)));
								
								virtualVisitActiveOrigin.siblings('.arrow').css('height',distance+'px');
								
								virtualVisitActiveOrigin.siblings('.gradient').css('-moz-transform', 'rotate('+degrees+'deg)');
								virtualVisitActiveOrigin.siblings('.gradient').css('-webkit-transform', 'rotate('+degrees+'deg)');
								virtualVisitActiveOrigin.siblings('.gradient').css('-o-transform', 'rotate('+degrees+'deg)');
								virtualVisitActiveOrigin.siblings('.gradient').css('-ms-transform', 'rotate('+degrees+'deg)');
								virtualVisitActiveOrigin.siblings('.gradient').css('transform', 'rotate('+degrees+'deg)');
								
								virtualVisitActiveOrigin.siblings('.arrow').css('-moz-transform', 'rotate('+(degrees-45)+'deg)');
								virtualVisitActiveOrigin.siblings('.arrow').css('-webkit-transform', 'rotate('+(degrees-45)+'deg)');
								virtualVisitActiveOrigin.siblings('.arrow').css('-o-transform', 'rotate('+(degrees-45)+'deg)');
								virtualVisitActiveOrigin.siblings('.arrow').css('-ms-transform', 'rotate('+(degrees-45)+'deg)');
								virtualVisitActiveOrigin.siblings('.arrow').css('transform', 'rotate('+(degrees-45)+'deg)');
							}
						});
						$('.virtualvisitflooredit .photos a').click(function(){
							virtualVisitSaved = false;
							$(this).siblings('.active').removeClass('active');
							$(this).addClass('active');
							if (virtualVisitActiveElement) virtualVisitActiveElement.parents('.marker').data('photo',$(this).data('id'));
							return false;
						});
					}
				});
			}
		</script>
SCRIPTS;
		
	}

}
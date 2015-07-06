<?
	$site = current_site();
	
	if (segment(1) == 'upload') {
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
	    
	    $result = $uploader->handleUpload(FILESPATH);
	    if (isset($result['filename'])) {
		    $orig_filename = substr($result['orig_filename'], 0, strrpos($result['orig_filename'],'.'));
		    $newdata = array(
	    		'site_id' => intval($site->id),
	    		'upload_date' => time(),
	    		'filename' => $result['filename'],
	    		'orig_filename' => $result['orig_filename'],
	    		'slug' => strtolower($orig_filename)
	    	);
	    	$id = insert('site/images',$newdata);
	    	$image = where('id = %d',$id)->get_row('site/images');
	    	$result['thumbnail'] = $image->cover(85,85);
	    	$result['small'] = $image->contain(100,100).','.$image->width().','.$image->height().',small';
	    	$result['normal'] = $image->contain(250,300).','.$image->width().','.$image->height().',normal';
	    	$result['large'] = $image->contain(800,800).','.$image->width().','.$image->height().',large';
	    	$result['xlarge'] = $image->contain(1000,2500).','.$image->width().','.$image->height().',xlarge';
	    	$result['id'] = $id;
	    }
	    
	    echo json_encode($result);
		exit();
	}
	
	if (segment(1) == 'savealt') {
		$image = where('id = %d',intval($_POST['id']))->get_row('site/images');
		$newdata = array(
			'alt' => $_POST['value']
		);
		where('id = %d',intval($_POST['id']))->update('site>images',$newdata);
		exit();
	}
	
	if (segment(1) == 'delete') {
		$image = where('id = %d',intval(segment(2)))->get_row('site>images');
    	$image->clear_thumbnails();
    	@unlink(FILESPATH.$image->filename);
		where('id = %d',intval(segment(2)))->delete('site>images');
		redirect('/admin/_imagepopup');
	}
	
	$site = where('id = %d',$site->id)->get_row('site');
?>

<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title><?=l(array('nl'=>'Afbeeldingen','en'=>'Images','fr'=>'Images'))?></title>
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/normalize.min.css')?>">
    <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/popup.css')?>">

    <script src="<?=url(ADMINRESOURCES.'js/modernizr-2.6.1.min.js')?>"></script>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="<?=url(ADMINRESOURCES.'js/jquery-1.8.1.min.js')?>"><\/script>')</script>
    
    <script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/tiny_mce_popup.js',false)?>"></script>
    <script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/utils/mctabs.js',false)?>"></script>
    <script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/utils/form_utils.js',false)?>"></script>
    <script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/utils/validate.js',false)?>"></script>
    <script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/utils/editable_selects.js',false)?>"></script>
		<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/jquery.uniform.min.js',false)?>"></script>
    <script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/popup.js',false)?>"></script>

</head>

<body class="thin">
	<h2><?=l(array('nl'=>'Afbeeldingen','en'=>'Images','fr'=>'Images'))?></h2>
	<? if ($site->images->count()): ?>
		<table class="list imagelist">
			<? foreach ($site->images as $image): ?>
				<tr>
					<td width="0" valign="top">
						<div style="background:url('<?=$image->cover(85,85)?>')" class="image-thumbnail"></div>
					</td>
					<td width="100%" valign="top">
						<input type="hidden" name="id" value="<?=$image->id?>"/>
						<?=$image->orig_filename?><br/>
						<input type="text" class="required" name="images-alt[]" onBlur="saveAlt(this)" placeholder="Beschrijving van de afbeelding" value="<?=$image->alt?>" required="required"/><br/>
						<span class="imagecontrols">
							<select name="position" class="inline">
								<option value="left"><?=l(array('nl'=>'Links','en'=>'Left','fr'=>'Gauche'))?></option>
								<option value="center"><?=l(array('nl'=>'Midden','en'=>'Center','fr'=>'Centre'))?></option>
								<option selected="selected" value="right"><?=l(array('nl'=>'Rechts','en'=>'Right','fr'=>'Droit'))?></option>
							</select> <select name="size" class="inline">
								<option value="<?=$image->contain(100,100)?>,<?=$image->width()?>,<?=$image->height()?>,small"><?=l(array('nl'=>'Klein','en'=>'Small','fr'=>'Petit'))?></option>
								<option selected="selected" value="<?=$image->contain(250,300)?>,<?=$image->width()?>,<?=$image->height()?>,normal"><?=l(array('nl'=>'Normaal','en'=>'Normal','fr'=>'Normal'))?></option>
								<option value="<?=$image->contain(800,800)?>,<?=$image->width()?>,<?=$image->height()?>,large"><?=l(array('nl'=>'Groot','en'=>'Large','fr'=>'Grand'))?></option>
								<option value="<?=$image->contain(1000,2500)?>,<?=$image->width()?>,<?=$image->height()?>,xlarge"><?=l(array('nl'=>'Maximum','en'=>'Maximum','fr'=>'Maximum'))?></option>
							</select> <a class="button insert" href="#" onclick="return insertImage(this);"><?=l(array('nl'=>'Invoegen','en'=>'Insert','fr'=>'Ins&eacute;rer'))?></a>
						</span>
					<td align="right" width="0">
						<div style="white-space:nowrap;">
							<a class="delete" href="/admin/_imagepopup/delete/<?=$image->id?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u deze afbeelding wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette image?','en'=>'Are you sure you want to delete this image?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"></a>
						</div>
					</td>
				</tr>
			<? endforeach; ?>
		</table>
	<? endif; ?>
	<form method="post" enctype="multipart/form-data" action="">
		<div class="imagestypeuploader"></div>
	</form>
	<script src="/system/admin/js/fileuploader.js"></script>
	<script>
		function insertImage(button) {
			var ed = tinyMCEPopup.editor, f = document.forms[0], nl = f.elements, v, args = {}, el;
	
			tinyMCEPopup.restoreSelection();
	
			if (tinymce.isWebKit) ed.getWin().focus();
			
			var size = $(button).parent().find('select[name="size"]').val();
			var data = size.split(',');
	
			tinymce.extend(args, {
				src : data[0],
				width : data[1],
				height : data[2],
				'alt' : $(button).parents('td').find('input[type="text"]').val(),
				'class' : $(button).parent().find('select[name="position"]').val()+' '+data[3]
			});
			
			ed.execCommand('mceInsertContent', false, '<img id="__mce_tmp" />', {skip_undo : 1});
			ed.dom.setAttribs('__mce_tmp', args);
			ed.dom.setAttrib('__mce_tmp', 'id', '');
			ed.undoManager.add();
	
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.editor.focus();
			tinyMCEPopup.close();
			
			return false;
		}
		$('.imagestypeuploader').each(function(){
			var container = $(this);
			var uploader = new qq.FileUploader({
				element: this,
			    action: '/admin/_imagepopup/upload/',
			    inputName: 'imagesuploader',
			    allowedExtensions: ['jpg', 'jpeg', 'png', 'gif'],
				multiple: true,
				uploadButtonText: 'Afbeeldingen toevoegen',
				cancelButtonText: 'Annuleer',        
				failUploadText: 'Upload mislukt',
				template: '<div class="qq-uploader">' + 
		                '<div class="qq-upload-drop-area"><span>{dragText}</span></div>' +
						'<table class="qq-upload-list list imagelist"></table>' + 
		                '<div class="tablecontrols">' +
		                '<a class="button qq-upload-button">{uploadButtonText}</a>' +
		                '</div>' +
		             '</div>',
		        fileTemplate: '<tr>' +
		        		'<td width="0" valign="top">' +
							'<div class="image-thumbnail qq-upload-thumbnail"></div>' +
						'</td>' +
						'<td width="100%" valign="top">' +
							'<input type="hidden" name="id"/>' +
							'<span class="qq-upload-file"></span>' +
							'<span class="qq-upload-size"></span>' +
							'<span class="qq-upload-spinner"></span>' +
							'<a class="qq-upload-cancel" href="#">{cancelButtonText}</a>' +
							'<span class="qq-progress-bar-container"><span class="qq-progress-bar"></span></span>' +
							'<span class="qq-upload-failed-text">{failUploadtext}</span>' +
							'<input type="text" class="required" name="images-alt[]" onBlur="saveAlt(this)" placeholder="Beschrijving van de afbeelding" required="required"/>' +
							'<span class="imagecontrols">' +
								'<select name="position" class="inline">' +
									'<option value="left"><?=l(array('nl'=>'Links','en'=>'Left','fr'=>'Gauche'))?></option>' +
									'<option value="center"><?=l(array('nl'=>'Midden','en'=>'Center','fr'=>'Centre'))?></option>' +
									'<option selected="selected" value="right"><?=l(array('nl'=>'Rechts','en'=>'Right','fr'=>'Droit'))?></option>' +
								'</select> <select name="size" class="inline">' +
									'<option><?=l(array('nl'=>'Klein','en'=>'Small','fr'=>'Petit'))?></option>' +
									'<option selected="selected"><?=l(array('nl'=>'Normaal','en'=>'Normal','fr'=>'Normal'))?></option>' +
									'<option><?=l(array('nl'=>'Groot','en'=>'Large','fr'=>'Grand'))?></option>' +
									'<option><?=l(array('nl'=>'Maximum','en'=>'Maximum','fr'=>'Maximum'))?></option>' +
								'</select> <a class="button insert" href="#" onclick="return insertImage(this);"><?=l(array('nl'=>'Invoegen','en'=>'Insert','fr'=>'Ins&eacute;rer'))?></a>' +
							'</span>' +
						'</td>' +
						'<td align="right" width="0">' +
							'<div style="white-space:nowrap;">' +
								'<a class="delete" href="/admin/_imagepopup/delete/" onclick="return confirm(\'<?=l(array('nl'=>'Bent u zeker dat u deze afbeelding wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette image?','en'=>'Are you sure you want to delete this image?'))?>\');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"></a>' +
							'</div>' +
						'</td>' +
		            '</tr>',
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
						$(item).find('input[name="id"]').val(responseJSON.id);
						$(item).find('a.delete').attr('href','/admin/_imagepopup/delete/'+responseJSON.id);
						$(item).find('select[name="size"] option').eq(0).attr('value',responseJSON.small);
						$(item).find('select[name="size"] option').eq(1).attr('value',responseJSON.normal);
						$(item).find('select[name="size"] option').eq(2).attr('value',responseJSON.large);
						$(item).find('select[name="size"] option').eq(3).attr('value',responseJSON.xlarge);
					}
				},
				onAddToList: function(id, fileName, item){
					$(item).find('select').uniform({selectAutoWidth:false});
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
		function saveAlt(element) {
        	$.ajax({
        		type: "POST",
        		url: "/admin/_imagepopup/savealt/",
        		data: {
	        		id: $(element).siblings('input[name="id"]').val(),
	        		value: $(element).val()
        		}
        	});
		}
	</script>
</body>
</html>
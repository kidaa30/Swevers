<?
	
	$site = current_site();
	
	if (segment(1) == 'delete') {
		$file = where('id = %d',intval(segment(2)))->get_row('site>downloads');
    	@unlink(FILESPATH.$file->filename);
		where('id = %d',intval(segment(2)))->delete('site>downloads');
		redirect('/admin/_downloadspopup');
	}
	
	if (isset($_FILES['addFile']) && is_array($_FILES['addFile']['name'])) {
		$files=array();
		foreach ($_FILES['addFile']['name'] as $index => $file_name) {
			$newfile = array();
			foreach (array_keys($_FILES['addFile']) as $key) {
				$newfile[$key] = $_FILES['addFile'][$key][$index];
			}
			$files[] = $newfile;
		};
		
		foreach ($files as $f) {
			if ($f['size']) {
				$toinsert = array();
				
				$extension = substr($f['name'], strrpos($f['name'], '.')+1);
				do {
					$name = md5(rand(0,99999).rand(0,99999));
				} while (file_exists(FILESPATH.$name.".".$extension));
				
				move_uploaded_file($f['tmp_name'], FILESPATH.$name.".".$extension);
				$toinsert['orig_filename'] = $f['name'];
				$toinsert['filename'] = $name.'.'.$extension;
				$toinsert['upload_date'] = time();
				$toinsert["site_id"] = intval($site->id);
				insert('site>downloads',$toinsert);
			}
		}
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
	
	<title><?=l(array('nl'=>'Downloads','en'=>'Downloads','fr'=>'Downloads'))?></title>
	
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
	<h2><?=l(array('nl'=>'Downloads','en'=>'Downloads','fr'=>'Downloads'))?></h2>
	
	<? if ($site->downloads->count()): ?>
		<table class="list">
			<? foreach ($site->downloads as $file): ?>
				<tr>
					<td width="0">
						<a class="button insert" href="#" onclick="return insertFile('<?=url('_download/'.$file->id,false)?>','<?=$file->orig_filename?>');"><?=l(array('nl'=>'Invoegen','en'=>'Insert','fr'=>'Ins&eacute;rer'))?></a>
					</td>
					<td width="100%">
						<?=$file->orig_filename?>
					</td>
					<td width="0">
						<a class="delete" href="/admin/_downloadspopup/delete/<?=$file->id?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u dit bestand wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer ce fichier?','en'=>'Are you sure you want to delete this file?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"></a>
					</td>
				</tr>
			<? endforeach; ?>
		</table>
	<? endif; ?>
	
	<form method="post" enctype="multipart/form-data" action="">
		<fieldset>
			<input type="file" multiple="multiple" name="addFile[]"/>
			<div style="text-align:center;"><a class="button" href="#" onclick="$(this).hide();setTimeout(function(){$('form').submit();},50);return false;"><?=l(array('nl'=>'Toevoegen','fr'=>'Ajouter','en'=>'Add'))?></a></div>
		</fieldset>
	</form>
	
	<script>
		function insertFile(url,filename) {
			var ed = tinyMCEPopup.editor, f = document.forms[0], nl = f.elements, v, el;
	
			tinyMCEPopup.restoreSelection();
	
			if (tinymce.isWebKit) ed.getWin().focus();
			
			ed.execCommand('mceInsertContent', false, '<a href="'+url+'">'+filename+'</a>', {skip_undo : 1});
			ed.undoManager.add();
	
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.editor.focus();
			tinyMCEPopup.close();
			
			return false;
		}
	</script>
</body>
</html>
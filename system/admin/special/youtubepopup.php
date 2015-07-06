<?
	$videotype = FW4_Type_Manager::get_instance()->get_type('video');
	
	$site = current_site();
	
	if (segment(1) == 'delete') {
		$video = where('id = %d',intval(segment(2)))->get_row('site/videos');
		$video->video->image->clear_thumbnails();
    	@unlink(FILESPATH.$video->image->filename);
		where('id = %d',intval(segment(2)))->delete('site/videos');
		redirect('/admin/_youtubepopup');
	}
	
	if (count($_POST)) {
		$data = $videotype->edit(array(),array('name'=>'video'),$_POST,new stdClass(),NULL);
		$data['site_id'] = $site->id;
		if ($data['video_url']) insert('site/videos',$data);
		redirect('/admin/_youtubepopup');
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
	
	<title><?=l(array('nl'=>'Video\'s','en'=>'Videos','fr'=>'Vid&eacute;os'))?></title>
	
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
	<h2><?=l(array('nl'=>'Video\'s','en'=>'Videos','fr'=>'Vid&eacute;os'))?></h2>
	<? if ($site->videos->count()): ?>
		<table class="list">
			<? foreach ($site->videos as $video): ?>
				<tr>
					<td width="0">
						<img src="<?=$video->video->image->contain(70,50)?>"/> 
					</td>
					<td width="100%">
						<div><?=$video->video->title?></div>
						<select name="position" class="inline">
							<option selected="selected" value="left"><?=l(array('nl'=>'Links','en'=>'Left','fr'=>'Gauche'))?></option>
							<option value="center"><?=l(array('nl'=>'Midden','en'=>'Center','fr'=>'Centre'))?></option>
							<option value="right"><?=l(array('nl'=>'Rechts','en'=>'Right','fr'=>'Droit'))?></option>
						</select> <select name="size" class="inline">
							<option value="<?=$video->video->image->contain(100,100)?>,<?=$video->video->image->width()?>,<?=$video->video->image->height()?>,<?=e($video->video->title)?>"><?=l(array('nl'=>'Klein','en'=>'Small','fr'=>'Petit'))?></option>
							<option selected="selected" value="<?=$video->video->image->contain(300,300)?>,<?=$video->video->image->width()?>,<?=$video->video->image->height()?>,<?=e($video->video->title)?>"><?=l(array('nl'=>'Normaal','en'=>'Normal','fr'=>'Normal'))?></option>
							<option value="<?=$video->video->image->contain(900,900)?>,<?=$video->video->image->width()?>,<?=$video->video->image->height()?>,<?=e($video->video->title)?>"><?=l(array('nl'=>'Groot','en'=>'Large','fr'=>'Grand'))?></option>
						</select> 
						<input type="hidden" name="url" value="<?=$video->video->url?>"/>
						<a class="button insert" href="#" onclick="return insertImage(this);"><?=l(array('nl'=>'Invoegen','en'=>'Insert','fr'=>'Ins&eacute;rer'))?></a>
					</td>
					<td width="0">
						<a class="delete" href="/admin/_youtubepopup/delete/<?=$video->id?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u deze video wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette vid&eacute;o?','en'=>'Are you sure you want to delete this video?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"></a>
					</td>
				</tr>
			<? endforeach; ?>
		</table>
	<? endif; ?>
	<form method="post" enctype="multipart/form-data" action="">
		<fieldset>
			<? $videotype->print_field(array('name'=>'video','label'=>l(array('nl'=>'Video (YouTube URL)','en'=>'Video (YouTube URL)','fr'=>'Vid&eacute;o (URL YouTube)'))),array(),array()); ?>
			<div style="text-align:center;"><a href="#" class="button" onclick="$('form').submit();$(this).hide();return false;"><?=l(array('nl'=>'Toevoegen','en'=>'Add','fr'=>'Ajouter'))?></a></div>
		</fieldset>
	</form>
	<script>
		function insertImage(button) {
			var ed = tinyMCEPopup.editor, f = document.forms[0], nl = f.elements, v, args = {}, el;
	
			tinyMCEPopup.restoreSelection();
	
			if (tinymce.isWebKit) ed.getWin().focus();
			
			var size = $(button).parents('td').find('select[name="size"]').val();
			var data = size.split(',');
	
			tinymce.extend(args, {
				src : data[0],
				width : data[1],
				height : data[2],
				alt : data[3]
			});
			
			var large = (data[1] > 300);
			
			ed.execCommand('mceInsertContent', false, '<img class="youtube '+$(button).parents('td').find('select[name="position"]').val()+' '+(large?'large':'')+'" data-href="'+$(button).siblings('input[name="url"]').val()+'" id="__mce_tmp"/>', {skip_undo : 1});
			ed.dom.setAttribs('__mce_tmp', args);
			ed.dom.setAttrib('__mce_tmp', 'id', '');
			ed.undoManager.add();
	
			tinyMCEPopup.editor.execCommand('mceRepaint');
			tinyMCEPopup.editor.focus();
			tinyMCEPopup.close();
			
			return false;
		}
	</script>
	<?=$videotype->get_scripts();?>
</body>
</html>
<!doctype html>
<!--[if lt IE 7 ]> <html class="no-js ie6" lang="en"> <![endif]-->
<!--[if IE 7 ]>    <html class="no-js ie7" lang="en"> <![endif]-->
<!--[if IE 8 ]>    <html class="no-js ie8" lang="en"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	
	<title>Link</title>
	
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/normalize.min.css')?>">
    <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/popup.css')?>">

    <script src="<?=url(ADMINRESOURCES.'js/modernizr-2.6.1.min.js')?>"></script>
	
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    <script>window.jQuery || document.write('<script src="<?=url(ADMINRESOURCES.'js/jquery-1.8.1.min.js')?>"><\/script>')</script>
    
	<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/jquery.uniform.min.js')?>"></script>
    
	<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/tiny_mce_popup.js')?>"></script>
	<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/utils/mctabs.js')?>"></script>
	<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/utils/form_utils.js')?>"></script>
	<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/utils/validate.js')?>"></script>
	<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/themes/advanced/js/link.js')?>"></script>

</head>

<body class="thin" id="link" style="display: none">
	<h2>{#advanced_dlg.link_title}</h2>
	<form onsubmit="LinkDialog.update();return false;" action="#">
		
		<fieldset>
			<div class="input">
				<label for="href">{#advanced_dlg.link_url}</label>
				<input id="href" name="href" type="text" class="mceFocus" value="" onchange="LinkDialog.checkPrefix(this);" />
				<div id="hrefbrowsercontainer"></div>
			</div>
			<div class="input">
				<label id="targetlistlabel" for="targetlist">{#advanced_dlg.link_target}</label>
				<span id="targetlistcontainer"><select id="target_list" name="target_list"></select></span>
			</div>
			<div class="input">
				<label for="linktitle">{#advanced_dlg.link_titlefield}</label>
				<input id="linktitle" name="linktitle" type="text" value="" />
			</div>
			<div class="input">
				<label for="stylelist">Stijl</label>
				<select id="stylelist" name="stylelist">
					<option value="">- Standaard -</option>
					<? if (count(Config::buttoncolors())): ?>
						<? foreach (Config::buttoncolors() as $colorname => $colorhex): ?>
							<option value="button <?=strtolower($colorname)?>" data-hex="<?=$colorhex?>">Knop (<?=$colorname?>)</option>
						<? endforeach; ?>
					<? else: ?>
						<option value="button">Knop</option>
					<? endif; ?>
				</select>
			</div>
		</fieldset>
			
		<div class="mceActionPanel">
			<input type="submit" id="insert" name="insert" value="{#insert}" />
		</div>
	</form>
</body>
</html>
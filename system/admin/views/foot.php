			</section>
		</div>
		
		<div id="drag-elements"></div>
		
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?=url(ADMINRESOURCES.'js/jquery-1.8.1.min.js')?>"><\/script>')</script>

        <script src="<?=url(ADMINRESOURCES.'js/fancybox/jquery.fancybox.pack.js')?>"></script>
		<script src="<?=url(ADMINRESOURCES.'js/fancybox/helpers/jquery.fancybox-media.js')?>"></script>
		<script src="<?=url(ADMINRESOURCES.'js/fancybox/helpers/jquery.fancybox-buttons.js')?>"></script>
        
        <script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/tiny_mce/jquery.tinymce_src.js',false)?>"></script>
		<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/jquery-ui-1.10.2.min.js',false)?>"></script>
		<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/jquery-ui-timepicker.js',false)?>"></script>
		<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/jquery.ui.datepicker-'.language().'.js',false)?>"></script>
		<script type="text/javascript" src="<?=url(ADMINRESOURCES.'js/jquery.uniform.js',false)?>"></script>
		
		<script>
			var admin = "<?=url(ADMINDIR,false)?>";
			var adminurl = "<?=url(ADMINRESOURCES,false)?>";
			var language = "<?=language()?>";
			var textcolors = "<?=Config::textcolors()?>";
			<? $colors = explode(',',Config::textcolors()); ?>
			var defaulttextcolor = "<?=Config::textcolors()?reset($colors):''?>";
			var euroSign = "€";
			var tinyMCEStyles = '<? foreach (Config::buttoncolors() as $colorname => $hex): 
					$r = hexdec($hex[0].$hex[1]);
					$g = hexdec($hex[2].$hex[3]);
					$b = hexdec($hex[4].$hex[5]);
					$luma = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b; ?>a.button.<?=strtolower($colorname)?> { border:0; color:#<?=($luma>100?'000':'fff')?>; text-shadow:none; box-shadow:inset 0 0 1px 1px rgba(0,0,0,0.5); background:#<?=$hex?>;} <? endforeach; ?>';
		</script>

        <script src="<?=url(ADMINRESOURCES.'js/main.js')?>"></script>
        
        <?=isset($scripts)&&is_string($scripts)?html_entity_decode($scripts,ENT_QUOTES,'UTF-8'):''?>
        
    </body>
</html>
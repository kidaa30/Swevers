        </div>
        <footer>
            <div class="container">
                <div class="left">marcswevers.be vastgoedmakelaar  |  <?= STREET ?>, <?= POSTAL.' '.CITY ?>  |  T: <a href="tel:<?= str_replace(' ', '', PHONE); ?>" id="phone"><?= PHONE ?></a>  |  E: <a href="<?= email_encode('mailto:'.EMAIL); ?>"><?= email_encode(EMAIL); ?></a>
                </div>
                <div class="right">
                    <a href="https://www.facebook.com/sweversvastgoed?sk=wall" rel="nofollow" target="_blank" class="icon left facebook">Facebook</a>
                    <a href="https://twitter.com/SweversVastgoed" rel="nofollow" target="_blank" class="icon left twitter">Twitter</a>
                    <a href="https://www.linkedin.com/profile/view?id=83651622&trk=tab_pro" rel="nofollow" target="_blank" class="icon left linkedin">Linkedin</a>
                    <a href="https://www.youtube.com/user/sweversvastgoed" rel="nofollow" target="_blank" class="icon left youtube">Youtube</a>
                    <a href="https://www.pinterest.com/sweversvastgoed/" rel="nofollow" target="_blank" class="icon left pinterest">Pinterest</a>
                </div>
            </div>
        </footer>
    
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="/js/vendor/jquery-1.8.1.min.js"><\/script>')</script>
		
        <script src="/js/min/main.js"></script>
        
        <? if (isset($js)): ?>
        	<? if (is_array($js)): ?>
        		<? foreach ($js as $script): ?>
        			<script src="/js/min/<?=$script?>.js"></script>	
        		<? endforeach; ?>
        	<? else: ?>
        		<script src="/js/min/<?=$js?>.js"></script>
        	<? endif; ?>
        <? endif; ?>
        
        <!-- Page generated in <?=stop_benchmark('global')?> -->
        
    </body>
</html>
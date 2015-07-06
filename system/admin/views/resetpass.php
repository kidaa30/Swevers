<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?=$site->name?> &mdash; <?=l(array('nl'=>'Beheerderspagina','fr'=>'Page d\'administration','en'=>'Administration panel'))?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/normalize.min.css')?>">
        <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/single.css')?>">

        <script src="<?=url(ADMINRESOURCES.'js/modernizr-2.6.1.min.js')?>"></script>
    </head>
    <body>
        
        <section id="content">
			<h1><?=$site->name?></h1>
			
			<form method="post">
				
				<h2>Wachtwoord opnieuw instellen</h2>
				
				<? if ($error):?>
					<p class="error"><?=$error?></p>
				<? endif; ?>
				
				<? if ($success): ?>
					<p><?=$success?></p>
				<? else: ?>
				
					<label for="password"><?=l(array('nl'=>'Nieuw wachtwoord'))?></label>
					<input type="password" name="password" required="required"/>
					
					<label for="confirm-password"><?=l(array('nl'=>'Bevestig wachtwoord'))?></label>
					<input type="password" name="confirm-password" required="required"/>
					
					<input type="submit" name="submit" value="<?=l(array('nl'=>'Instellen'))?>" />
				<? endif; ?>
			</form>
		</section>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="<?=url(ADMINRESOURCES.'js/jquery-1.8.1.min.js')?>"><\/script>')</script>

        <script src="js/main.js"></script>
    </body>
</html>
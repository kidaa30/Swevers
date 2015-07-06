<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?=isset($title)?$title.' &mdash; ':''?><?=$site->name?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width">

        <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/normalize.min.css')?>">
        <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'css/main.css')?>">
        
        <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'js/fancybox/jquery.fancybox.css')?>"/>
        <link rel="stylesheet" href="<?=url(ADMINRESOURCES.'js/fancybox/helpers/jquery.fancybox-buttons.css')?>"/>

        <script src="<?=url(ADMINRESOURCES.'js/modernizr-2.6.1.min.js')?>"></script>
    </head>
    <body>
    
    	<header>
	    	<h1><?=$site->name?></h1>
	    	<div class="usermenu">
		    	<a href="<?=url(ADMINDIR.'/logout/',false)?>"><?=isset($user->firstname)&&isset($user->lastname)?$user->firstname.' '.$user->lastname:$user->name?></a>
	    	</div>
    	</header>
        
        <div id="wrapper">
        	<nav><ul>
				<? $last_section = strval($pages[0]['section']);
				foreach ($pages as $page): 
					if (strval($page['section']) != $last_section) echo '<hr/>'; 
					$last_section = strval($page['section']); ?>
					<li<?=$page['name']==segment(0)?' class="active"':''?>><a href="<?=url(ADMINDIR.'/'.$page['name'],false)?>"><?=$page['label']?></a></li>
				<? endforeach; ?>
			</ul></nav>
        	<section id="content">
	        	<? if (!is_writable(FILESPATH)): ?>
				<div class="warning">De upload map is niet schrijfbaar op de server. Mogelijk zal sommige functionaliteit niet werken.</div>
				<? endif; ?>
				<? if (isset($_SESSION['successmessage'])): ?>
					<div class="usernote success"><?=$_SESSION['successmessage']?></div>
					<? unset($_SESSION['successmessage']); ?>
				<? endif; ?>
				
				<?=view('navstack',array('title'=>isset($navstacktitle)?$navstacktitle:false)); ?>
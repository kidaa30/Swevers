<?=view('head',array('title'=>l(array('nl'=>'Interne fout','fr'=>'Erreur interne','en'=>'Internal error'))))?>

<h2><?=l(array('nl'=>'Er is een fout opgetreden tijdens het laden van deze pagina.','fr'=>'Une erreur s\'est produite lors du chargement de ce contenu.','en'=>'An error occurred while loading the page.'))?></h2>
<p>
	<?=l(array(
		'nl' => 'We konden de door u gevraagde pagina niet laden. Er is langs onze kant een fout opgetreden. Onze ontwikkelaars werden hiervan verwittigd. We verontschuldigen ons voor het ongemak.',
		'fr' => 'Nous ne pouvions pas charger la page que vous avez demand&eacute;. Il est de notre c&ocirc;t&eacute; une erreur. Nos d&eacute;veloppeurs ont &eacute;t&eacute; notifi&eacute;s. Nous nous excusons pour la g&ecirc;ne occasionn&eacute;e.',
		'en' => 'We could not load the requested page. An error occurred on our end. Our developers have been notified. We apologise for the inconvenience.'
	))?>
</p>
<p><a href="<?=url('')?>"><?=l(array('nl'=>'Ga naar de startpagina','fr'=>'Aller &agrave; la page d\'accueil','en'=>'Go to the homepage'))?></a></p>

<!--<?=$debug_info?>-->

<?=view('foot')?>
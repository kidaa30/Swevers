<?=view('head',array('title'=>l(array('nl'=>'Pagina niet gevonden','fr'=>'Page introuvable','en'=>'Page not found')), 'class' => 'content'))?>
<div id="content-wrapper">
	<div class="container">
<h1><?=l(array('nl'=>'De opgevraagde pagina kan niet worden gevonden.','fr'=>'La page que vous avez demand&eacute;e est introuvable.','en'=>'The page you requested could not be found.'))?></h1>
<p>
	<?=l(array(
		'nl' => 'Je hebt op een link geklikt die niet meer geldig is of een verkeerd adres getypt. Sommige internetadressen zijn hoofdlettergevoelig.',
		'fr' => 'Vous avez cliqu&eacute; sur un lien qui n\'est plus valide ou tap&eacute; une mauvaise adresse. Quelques adresses web sont sensibles &agrave; la casse.',
		'en' => 'You have clicked on a link that is no longer valid or typed the wrong address. Some web addresses are case sensitive.'
	))?>
</p>
<p><a href="<?=url('')?>"><?=l(array('nl'=>'Ga naar de startpagina','fr'=>'Aller &agrave; la page d\'accueil','en'=>'Go to the homepage'))?></a></p>
</div></div>
<?=view('foot')?>
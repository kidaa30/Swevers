<? if((segment(2) == 'te-koop' || segment(2) == 'te-huur') && segment(1) == 'index'){
	$background = true;
	$background_image = get_random_row('home/images');
} else{ $background = false; }?>

<?
	$contact = get_row('contact');
	define('EMAIL', $contact->email);
	define('PHONE', $contact->phone);
	define('STREET', $contact->address_address);
	define('POSTAL', $contact->address_postal_code);
	define('CITY', $contact->address_city);
 ?>

<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title><?= isset($title) && $title != '' ?$title.' &mdash; ' :''?>Swevers Vastgoed</title>
		
		<meta name="description" content="<?= isset($description) && $description != '' ? addslashes($description):''?>">
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">

		<meta property="og:title" content="<?= isset($title) &&  $title != '' ?' &mdash; '.$title.' ':''?>Swevers Vastgoed"/>
		<meta property="og:site_name" content="http://www.swevers.be/"/>
		<meta property="og:description" content="<?= isset($description) && $description != '' ? addslashes($description):''?>"> 
		<? if (isset($image) && $image): ?><meta property="og:image" content="<?=$image?>">
		<? else: ?><meta property="og:image" content="<?=url('images/fblogo.jpg')?>"><? endif; ?>

		<link rel="stylesheet" href="<?=url('css/main.css')?>">
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
		<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
		<link rel="apple-touch-icon" sizes="57x57" href="/apple-touch-icon-57x57.png" />
		<link rel="apple-touch-icon" sizes="72x72" href="/apple-touch-icon-72x72.png" />
		<link rel="apple-touch-icon" sizes="114x114" href="/apple-touch-icon-114x114.png" />
		<link rel="apple-touch-icon" sizes="144x144" href="/apple-touch-icon-144x144.png" />
		
		<? if (isset($css)):?><link rel="stylesheet" href="<?=url('css/'.$css.'.css')?>"><? endif; ?>

		<script src="<?=url('js/vendor/modernizr-2.6.1.min.js')?>"></script>
	</head>
	<body class="<?=language()?><? if (isset($class)):?> <?=$class?><? endif; ?> <?= $background ? 'background' : '' ;?>" <?= $background && $background_image ? 'style="background-image: url('.$background_image->cover(2000, 1500).')"' : ''; ?>>
		<header role="banner" class="<?= segment(0) != 'home' ? 'other-page' : '' ?>" >
			<div class="container">
				<h1 aria-label="Swevers Vastgoed" id="logo">
					<a href="<?= url('') ?>"><span class="vertical"></span><span class="horizontal"></span>Swevers Vastgoed</a>
				</h1>
				<a href="tel:<?= str_replace(' ', '', PHONE); ?>" id="phone"><?= PHONE ?></a>
				<nav role="navigation">
					<ul>
						<li class="<?= segment(0) == 'offer' && in_array('te-koop', segments()) ? 'selected' : '' ?>"><a href="<?= url('te-koop') ?>">te koop</a></li>
						<li class="<?= segment(0) == 'offer' && in_array('te-huur', segments()) ? 'selected' : '' ?>"><a href="<?= url('te-huur') ?>">te huur</a></li>
						<li class="<?= segment(0) == 'projects' ? 'selected' : '' ?>"><a href="<?= url('nieuwbouw') ?>">nieuwbouw</a></li>
						<li class="<?= segment(0) == 'services' ? 'selected' : '' ?>"><a href="<?= url('diensten') ?>">diensten</a></li>
						<li class="<?= segment(0) == 'news' ? 'selected' : '' ?>"><a href="<?= url('nieuws') ?>">nieuws</a></li>
						<li class="<?= segment(0) == 'testimonials' ? 'selected' : '' ?>"><a href="<?= url('getuigenissen') ?>">getuigenissen</a></li>
						<li class="<?= segment(0) == 'contact' ? 'selected' : '' ?>"><a href="<?= url('contact') ?>">contact</a></li>
					</ul>
				</nav>
			</div>
		</header>
		<div id="wrapper">
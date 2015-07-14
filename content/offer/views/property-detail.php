<div id="content-wrapper">
	<? if($property->photo->count()): ?>
		<div id="slideshow">
			<? foreach($property->photo as $photo): ?>
				<div class="slide" style="background-image:url('<?= $photo->cover(2000, 1500) ?>')"></div>		
			<? endforeach; ?>
			<div id="navigation">
				<? foreach($property->photo as $photo): ?>
					<button></button>
				<? endforeach; ?>
			</div>
		</div>
	<? endif; ?>
	<header role="property">
		<div class="container">
			<h1>
				<span class="title"><?= $property->flash1_title; ?></span>
				<? if($property->price && $property->show_price && $property->price != 0): ?><span class="price right">&euro; <?= number_format($property->price, 0, ',', '.') ?><?=$property->purpose == 2 ? ' p/m' : ''; ?></span><? endif; ?>
			</h1>
			<h2>
				<?= $property->city; ?>
				<ul class="property-detail right">
					<? if(intval($property->cadastrall_income)): ?><li><span>KI </span>&euro; <?= number_format($property->cadastrall_income, 0, ',', '.') ?></li><? endif; ?>
					<? if($property->epc): ?><li><span>EPC </span><?= $property->epc; ?></li><? endif; ?>
					<? if($property->has_bedrooms && $property->bedrooms): ?><li class="icon-offer rooms"><?= $property->bedrooms; ?></li><? endif; ?>
					<? if(intval($property->surface_livable)): ?><li class="icon-offer surface"><?= intval($property->surface_livable); ?> m&sup2;</li><? endif; ?>
				</ul>
			</h2>
		</div>
	</header>
	<a href="#" id="more-info" class="button yellow"><span>meer info</span></a>
	<div id="detail-wrapper">
	<section id="detail">
		<div class="container">
			<div class="grid">
				<div class="col-2-3">
					<? if($property->flash1_content): ?>
					<div id="description"><?= $property->flash1_content; ?></div>
					<? endif; ?>
					<div class="grid">
						<div class="col-1-2">
							<? if(isset($property->details['algemeen']) && count($property->details['algemeen'])): ?>
								<div class="detail">
									<h3>algemeen</h3>
									<dl>
										<? foreach ($property->details['algemeen'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								</div>
							<? endif; ?>

							<? if(isset($property->details['financieel']) && count($property->details['financieel'])): ?>
								<div class="detail">
									<h3>financiëel</h3>
									<dl>
										<? foreach ($property->details['financieel'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								</div>
							<? endif; ?>

							<? if(isset($property->details['energie']) && count($property->details['energie'])): ?>
								<div class="detail">
									<h3>energie</h3>
									<?if(isset($property->details['energie'][l(array('nl' => 'EPC', 'fr' => 'PEB', 'en' => 'EPC', 'de' => 'EPC'))])):?>
									<?
										$epc = $property->details['energie'][l(array('nl' => 'EPC', 'fr' => 'PEB', 'en' => 'EPC', 'de' => 'EPC'))];
										$max = 700;
										$percentage = 0.753;
									?>
									<div class="epc-slider print-hider">
										<div class="epc-slider-nonblur" style="width:<?=round(100 - (min($max,$epc)/$max)*100)?>%"></div>
										<div class="epc-number" style="left:<?=round((min($max,$epc)/$max)*100)?>%"><?=substr($property->details['energie'][l(array('nl' => 'EPC', 'fr' => 'PEB', 'en' => 'EPC', 'de' => 'EPC'))], 0, strpos($property->details['energie'][l(array('nl' => 'EPC', 'fr' => 'PEB', 'en' => 'EPC', 'de' => 'EPC'))], ' ')) ?></div>
									</div>
								   <?endif;?>
									<dl>
										<? foreach ($property->details['energie'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								</div>
							<? endif; ?>

							<? if(isset($property->details['stedenbouw']) && count($property->details['stedenbouw'])): ?>
								<div class="detail">
									<h3>stedenbouwkundige info</h3>
									<dl>
										<? foreach ($property->details['stedenbouw'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								</div>
							<? endif; ?>
						</div>
						<div class="col-1-2">
							<div class="detail">
								<h3>geografische ligging</h3>
								<? if (isset($property->details['ligging']) && count($property->details['ligging'])): ?>
									<dl>
										<? foreach ($property->details['ligging'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								<? endif; ?>
							</div>
							<? if(isset($property->details['indeling']) && count($property->details['indeling'])): ?>
								<div class="detail">
									<h3>indeling</h3>
									<dl>
										<? foreach ($property->details['indeling'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								</div>
							<? endif; ?>

							<? if(isset($property->details['grond']) && count($property->details['grond'])): ?>
								<div class="detail">
									<h3>grond</h3>
									<dl>
										<? foreach ($property->details['grond'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								</div>
							<? endif; ?>

							<? if(isset($property->details['comfort']) && count($property->details['comfort'])): ?>
								<div class="detail">
									<h3>comfort</h3>
									<dl>
										<? foreach ($property->details['comfort'] as $name => $detail) :?>
											<div><dt><?= $name ?></dt><dd><?= $detail ?></dd></div>
										<? endforeach; ?>
									</dl>
								</div>
							<? endif; ?>
						</div>
					</div>
				</div>
				<div class="col-1-3">
					<? if($property->youtube_code): ?>
						<a class="button yellow video youtube" href="https://www.youtube.com/watch?v=<?= $property->youtube_code ?>">bekijk video</a>
					<? endif; ?>

					<? if($property->photo->count()): ?>
						<? if($property->photo->count() > 6): ?><div class="arrow top disabled"></div><? endif; ?>
						<div id="thumbnails" class="<?= $property->photo->count() > 6 ? 'with-pages' : '' ?>">
							<div class="thumbnails-scrollwindow">
								<div class="thumbnails-wrapper">
									<div class="page">
										<div class="row">
											<?foreach($property->photo as $i => $photo):?>
											<?if($i%6 == 0 && $i > 0):?></div></div><div class="page"><div class="row">
											<?elseif ($i%2 == 0 && $i > 0): ?></div><div class="row"><?endif;?>
											<div class="photo"><a href="<?=$photo->contain(1600, 900)?>" rel="gallery" class="fancybox"><img src="<?=$photo->cover(165, 110)?>" alt="" /></a></div>
										<?endforeach?>
										</div>
									</div>
								</div>
							</div>
						</div>
						<? if($property->photo->count() > 6): ?><div class="arrow bottom"></div><? endif; ?>
					<? endif; ?>

					<? if($property->coord_lat && $property->coord_lon && $property->show_address): ?>
						<div id="map-canvas"></div>
					<? endif; ?>
					<div id="social">
						<a href="" class="button yellow small">vraag meer info</a>
						<a href="" class="button blue small right">afdrukken</a>
						<?if($property->virtual_active && $property->virtual->count() > 0):?><a href="#virtual" class="button small long yellow virtual"><span>virtueel bezoek</span></a><?endif?>
					</div>
				</div>
			</div>
		</div>
	</section>
	</div>
</div>

<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
<? if ($property->coord_lat && $property->coord_lon): ?>
	<script>
		var coord_lat = <?=number_format($property->coord_lat,10,'.','')?>;
		var coord_lon = <?=number_format($property->coord_lon,10,'.','')?>;
		var showMarker = <?=$property->show_address?'true':'false'?>;
		<? if ($property->show_address): ?>
			var markerContent = '<? if ($property->address || $property->number || $property->box): ?><div><?=ucfirst(addslashes($property->address))?></div><? endif; ?><div><?=$property->postal?> <?=addslashes($property->city)?></div>';
		<? endif; ?>	
	</script>
<? endif; ?>
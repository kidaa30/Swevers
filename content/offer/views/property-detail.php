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
					<? if($property->epc): ?><li><span>EPC </span><?= $property->epc; ?></li><? endif; ?>
					<? if($property->has_bedrooms && $property->bedrooms): ?><li class="icon-offer rooms"><?= $property->bedrooms; ?></li><? endif; ?>
					<? if(intval($property->surface_livable)): ?><li class="icon-offer surface"><?= intval($property->surface_livable); ?> m&sup2;</li><? endif; ?>
				</ul>
			</h2>
		</div>
	</header>
	<a href="#" id="more-info"><span>Meer info</span></a>
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
				<div class="col-1-3"></div>
			</div>
		</div>
	</section>
	</div>
</div>
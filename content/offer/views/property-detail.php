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
	<a href="#" id="more-info">Meer info</a>
	<section id="detail">
		<div class="container">
			<div class="grid">
				<div class="col-2-3">
					<? if($property->flash1_content): ?>
					<div id="description"><?= $property->flash1_content; ?></div>
					<? endif; ?>
					<div class="grid">
						<div class="col-1-2">
							<? if(isset($property->details['algemeen'])): ?>
								<div class="detail">
									<h3>algemeen</h3>
								</div>
							<? endif; ?>

							<? if(isset($property->details['financieel'])): ?>
								<div class="detail">
									<h3>financiëel</h3>
								</div>
							<? endif; ?>

							<? if(isset($property->details['energie'])): ?>
								<div class="detail">
									<h3>energie</h3>
								</div>
							<? endif; ?>

							<? if(isset($property->details['algemeen'])): ?>
								<div class="detail">
									<h3>algemeen</h3>
								</div>
							<? endif; ?>

							<? if(isset($property->details['stedenbouw'])): ?>
								<div class="detail">
									<h3>stedenbouwkundige info</h3>
								</div>
							<? endif; ?>
						</div>
						<div class="col-1-2">
							<div class="detail">
								<h3>geografische ligging</h3>
							</div>
							<? if(isset($property->details['indeling'])): ?>
								<div class="detail">
									<h3>indeling</h3>
								</div>
							<? endif; ?>

							<? if(isset($property->details['grond'])): ?>
								<div class="detail">
									<h3>grond</h3>
								</div>
							<? endif; ?>

							<? if(isset($property->details['comfort'])): ?>
								<div class="detail">
									<h3>comfort</h3>
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
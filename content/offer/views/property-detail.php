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
			
		</div>
	</section>
</div>
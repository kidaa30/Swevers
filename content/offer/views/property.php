<div class="property-wrapper">
	<div class="property" id="pand-<?= $property->id ?>">
		<div class="property-image">
			<a href="<?= url($purpose_slugs[$property->purpose].'/'.$property->slug.'/'.$property->id) ?>">
				<? if($property->photo->count()): ?>
					<img src="<?= $property->photo->first()->cover(360,240) ?>" srcset="<?= $property->photo->first()->cover(360,240) ?> 1x, <?= $property->photo->first()->cover(720,480) ?> 2x" alt="<?= $property->flash1_title ?>" />
				<? else: ?>
					<img src="/images/property-placeholder.jpg" srcset="/images/property-placeholder.jpg 1x, /images/property-placeholder@2x.jpg 2x" alt="<?= $property->flash1_title ?>" />
				<? endif; ?>
			</a>
			<? if($property->youtube_code): ?>
				<a class="video youtube" href="https://www.youtube.com/watch?v=<?= $property->youtube_code ?>">bekijk video</a>
			<? endif; ?>

			<? if($property->purpose_status > 4): ?><span class="property-sticker option">in optie</span>
			<? elseif($property->create_date > strtotime('-1 month')): ?><span class="property-sticker new">nieuw</span><? endif; ?>
		</div>
		<div class="property-details">
			<? if($property->flash1_title != ''): ?>
				<a href="<?= url($purpose_slugs[$property->purpose].'/'.$property->slug.'/'.$property->id) ?>">
					<div class="property-title"><?= $property->flash1_title ?></div>
				</a>
			<? endif; ?>
			<div class="property-location">
				<span class="city left"><?= $property->city ?></span>

				<? if($property->price && $property->show_price && $property->price != 0): ?><span class="price right">&euro; <?= number_format($property->price, 0, ',', '.') ?><?=$property->purpose == 2 ? ' p/m' : ''; ?></span><? endif; ?>
	
				<ul class="property-detail right">
					<? if($property->epc): ?><li><span>EPC </span><?= $property->epc; ?></li><? endif; ?>
					<? if($property->has_bedrooms && $property->bedrooms): ?><li class="icon-offer rooms"><?= $property->bedrooms; ?></li><? endif; ?>
					<? if(intval($property->surface_livable)): ?><li class="icon-offer surface"><?= intval($property->surface_livable); ?> m&sup2;</li><? endif; ?>
				</ul>
			</div>
			<? if($property->flash1_content): ?>
				<div class="property-content">
					<?= excerpt($property->flash1_content, 650); ?>
				</div>
			<? endif; ?>
		</div>
	</div>
</div>
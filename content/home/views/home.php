<? if($home->images->count()): ?>
	<div id="slideshow">
		<? foreach($home->images as $image): ?>
			<div class="slide" style="background-image:url('<?= $image->cover(2000, 1500) ?>')"></div>		
		<? endforeach; ?>
		<div id="navigation">
			<? foreach($home->images as $image): ?>
				<button></button>
			<? endforeach; ?>
		</div>
	</div>
<? endif; ?>

<div id="content">
	<a href="" id="top-button"></a>
	<div class="container">
		<div class="grid">
			<div class="row">
				<div class="<?= $home->video ? 'col-2-3' : 'col-3-3' ?>">
					<h1><?= $home->title ?></h1>
					<div class="usercontent"><?= $home->content ?></div>
				</div>
				<? if($home->video): ?>
					<div class="col-1-3">
						<a id="video" href="https://www.youtube.com/watch?v=<?= $home->video->code ?>" class="youtube">
							<img src="http://img.youtube.com/vi/<?= $home->video->code ?>/0.jpg" alt="" />
							<span class="duration"><?= floor($home->video->duration/60).':'.str_pad($home->video->duration%60, 2, 0, STR_PAD_LEFT); ?></span>	
						</a>
					</div>
				<? endif; ?>
			</div>

			<div id="properties" class="row">
				<? if($sale->count()): ?>
					<div class="col-1-3">
						<ul class="properties-wrapper clear">
							<? foreach($sale as $property): ?>
								<li class="property">
									<a href="<?= url('te-huur/'.$property->slug.'/'.$property->id) ?>">
									<? if($property->photo->count()): ?>
										<img src="<?= $property->photo->first()->cover(360,240) ?>" srcset="<?= $property->photo->first()->cover(360,240) ?> 1x, <?= $property->photo->first()->cover(720,480) ?> 2x" alt="Te koop <?= $property->flash1_title ?>" />
									<? else: ?>
										<img src="/images/property-placeholder.jpg" srcset="/images/property-placeholder.jpg 1x, /images/property-placeholder@2x.jpg 2x" alt="Te hoop <?= $property->flash1_title ?>" />
									<? endif; ?>
									</a>
									<div class="title clear">
										<span class="purpose">Te koop </span><div class="subtitle"><?= $property->flash1_title; ?></div>
									</div>
									<div class="details clear">
										<span class="city"><?= $property->city ?></span>
										<? if($property->price && $property->show_price && $property->price != 0): ?><span class="price">&euro; <?= number_format($property->price, 0, ',', '.') ?></span><? endif; ?>
									</div>
								</li>
							<? endforeach; ?>
						</ul>
					</div>
				<? endif; ?>
				<? if($rent->count()): ?>
					<div class="col-1-3">
						<ul class="properties-wrapper clear">
							<? foreach($rent as $property): ?>
								<li class="property">
									<a href="<?= url('te-huur/'.$property->slug.'/'.$property->id) ?>">
									<? if($property->photo->count()): ?>
										<img src="<?= $property->photo->first()->cover(360,240) ?>" srcset="<?= $property->photo->first()->cover(360,240) ?> 1x, <?= $property->photo->first()->cover(720,480) ?> 2x" alt="Te huur <?= $property->flash1_title ?>" />
									<? else: ?>
										<img src="/images/property-placeholder.jpg" srcset="/images/property-placeholder.jpg 1x, /images/property-placeholder@2x.jpg 2x" alt="Te huur <?= $property->flash1_title ?>" />
									<? endif; ?>
									</a>
									<div class="title clear">
										<span class="purpose">Te huur </span><div class="subtitle"><?= $property->flash1_title; ?></div>
									</div>
									<div class="details clear">
										<span class="city"><?= $property->city ?></span>
										<? if($property->price && $property->show_price && $property->price != 0): ?><span class="price">&euro; <?= number_format($property->price, 0, ',', '.') ?><span class="small"> p/m</span></span><? endif; ?>
									</div>
								</li>
							<? endforeach; ?>
						</ul>
					</div>
				<? endif; ?>
				<? if($projects->count()): ?>
					<div class="col-1-3">Te koop</div>
				<? endif; ?>

			</div>
		</div>
	</div>
</div>
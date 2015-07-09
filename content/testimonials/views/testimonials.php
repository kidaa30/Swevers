<div id="content-wrapper">
	<div class="container">
		<h1>Getuigenissen</h1>
		<? if($testimonials->testimonial->count()): ?>
			<div class="grid">
				<? foreach($testimonials->testimonial as $i => $testimonial): ?>
					<div class="col-3-3">
						<div class="testimonial">
							<? if($testimonial->image): ?>
								<div class="testimonial-image">
									<img src="<?= $testimonial->image->cover(240, 160) ?>" srcset="<?= $testimonial->image->cover(360, 240) ?> 1x, <?= $testimonial->image->cover(720, 480) ?> 2x" alt="" />
								</div>
							<? endif; ?>
							<div class="testimonial-content <?= $testimonial->image ? 'with-image' : ''?>">
								<h2><?= $testimonial->name ?></h2>
								<?= $testimonial->content ?>
								<? if($testimonial->city): ?><div class="city"><?= $testimonial->city; ?></div><? endif; ?>
							</div>
						</div>
					</div>
				<? endforeach; ?>
			</div>
		<? endif; ?>
	</div>
</div>
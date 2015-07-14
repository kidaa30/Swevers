<div id="content-wrapper">
	<div class="container">
		<div class="clear">
			<h1 class="left"><?= $purpose_titles[segment(2)] ?></h1>
			<ul id="display" class="right">
				<li><a href="#" data-display="grid" class="display left grid <?= $display == 'grid' ? 'selected' : ''; ?>"></a></li>
				<li><a href="#" data-display="list" class="display left list <?= $display == 'list' ? 'selected' : ''; ?>"></a></li>
			</ul>
		</div>

		<form id="search-form" method="post">
			<div id="input-fields" class="">
				<div id="select-types" class="input select left">
					<label>type</label>
					<select name="types[]" id="types" multiple="multiple">
						<? foreach ($possible_categories as $category_id): ?>
							<option value="<?= $category_id ?>" <?= in_array($category_id,$category_ids) ? 'selected="selected"' : '' ?>><?= $categories[$category_id] ?></option>
						<? endforeach; ?>
					</select>
				</div>
				<div id="select-rooms"  class="input select left">
					<label>slaapkamers</label>
					<select name="rooms" id="rooms">
						<option value="0">--</option>
						<optgroup label="maximum">
							<option value="1" <?= $rooms == 1 ? 'selected="selected"' : '' ?>>1</option>
							<option value="2" <?= $rooms == 2 ? 'selected="selected"' : '' ?>>2</option>
							<option value="3" <?= $rooms == 3 ? 'selected="selected"' : '' ?>>3</option>
							<option value="4" <?= $rooms == 4 ? 'selected="selected"' : '' ?>>4</option>
						</optgroup>
						<optgroup label="minimum">
							<option value="5" <?= $rooms == 5 ? 'selected="selected"' : '' ?>>5</option>
						</optgroup>
					</select>
				</div>
				<div id="select-price"  class="input select left">
					<label>prijs</label>
					<select name="price" id="price">
						<option value="0">--</option>
						<optgroup label="maximum">
						<? $counter = 1; ?>
						<? foreach ($prices as $price): ?>
							<? if($counter == count($prices)): ?></optgroup><optgroup label="minimum"><? endif; ?>
							<option value="<?= $price ?>" <?= $price == $minprice || $price == $maxprice ? 'selected="selected"' : '' ?>>&euro; <?= number_format($price, 0, ',', '.') ?></option>
							<? $counter++ ?>
						<? endforeach; ?>
						</optgroup>
					</select>
				</div>
				<div id="select-cities" class="input select left">
					<label>gemeente</label>
					<select name="cities[]" id="cities" multiple="multiple">
						<? foreach ($possible_cities as $city): ?>
							<? foreach($city as $postal => $city_name): ?>
								<option value="<?= $postal ?>" <?= in_array($postal,$postalcodes) ? 'selected="selected"' : '' ?>><?= $city_name ?></option>
							<? endforeach; ?>
						<? endforeach; ?>
					</select>
				</div>
			</div>
			<div id="input-submit">
				<input type="submit" name="submit" value="zoeken" />
			</div>
		</form>
		<section id="properties" class="grid display-<?= $display ?>">
			<? if($properties->count()): ?>
				<? foreach($properties as $property): ?>
					<?= view('property', array(
						'property' => $property,
						'purpose_slugs' => $purpose_slugs
						));?>
				<? endforeach; ?>
			<? else: ?>
				<div class="col-3-3">
					<h2>Geen resultaten gevonden!</h2>
				</div>
			<? endif; ?>
		</section>
		<?if($amount > 12):?>
		<? $main_url = $purpose.(count($segments)?'/':'').implode('/',$segments)?>
		<ul id="pagination" class="right">
			<?if($page > 1):?><li class="pagination-arrow"><a href="<?= url($main_url.'/pagina-'.($page-1).'') ?>" class="arrow"><?= l(array('nl' => 'vorige','fr' => 'précédent', 'en' => 'früher')) ?></a></li><?endif;?>
			<?if($page >= 4):?><li><a href="<?= url($main_url.'/pagina-1') ?>">1</a></li><?endif?>
			<?if($page >= 5):?><li class="dots">...</li><?endif?>
			<?for($i= max(1, $page - 2); $i <= min($page + 2, $pages); $i++):?>
				<? if ($i == $page): ?>
					<li class="selected"><?=$i?></li>
				<?else:?>
					<li><a href="<?= url($main_url.'/pagina-'.($i).'') ?>"><?=$i?></a></li>
				<?endif?>
			<?endfor?>
			<?if($page <= $pages - 4):?><li class="dots">...</li><?endif?>
			<?if($page <= $pages - 3):?><li><a href="<?= url($main_url.'/pagina-'.($pages).'') ?>"><?=$pages?></a></li><?endif;?>
			<?if($page < $pages): ?><li class="pagination-arrow"><a href="<?= url($main_url.'/pagina-'.($page+1).'') ?>" class="arrow"><?= l(array('nl' => 'volgende','fr' => 'suivant', 'en' => 'nächster')) ?></a></li><? endif; ?>
		</ul>
	<?endif?>
	</div>
</div>
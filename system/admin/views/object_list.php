<div class="object-list">
<? if ($amount > 10 && $controls): ?>
	<div class="tablecontrols top">
		<? if (!isset($field['allow_edit']) && !isset($field['creation_disabled'])): ?>
			<a class="button" href="<?=$field['name']?>/add"><?=isset($field['label'])?l(array('nl'=>ucfirst($field['label']).' toevoegen','en'=>'Add '.strtolower($field['label']),'fr'=>'Ajouter '.strtolower($field['label']))):l(array('nl'=>'Toevoegen','fr'=>'Ajouter','en'=>'Add'))?></a>
		<? endif; ?>
		<? if (isset($field['exportable']) && $field['exportable']): ?>
			<a class="button" href="<?=$field['name']?>/export"><?=l(array('nl'=>'Exporteren','fr'=>'Exporter','en'=>'Export'))?></a>	
		<? endif; ?>
		<span class="amount"><?=l(array('nl'=>'Totaal','fr'=>'Totale','en'=>'Total'))?>: <strong><?=$amount?></strong></span>
	</div>
<? endif;

if ($searchable || count($filters)): ?>
<div class="object-filter">
	<div class="object-filters<? if ($searchable): ?> with-search<? endif; ?>">
		<? if ($searchable): ?>
			<div class="search-placeholder"></div>
		<? else: ?>
			<div class="reset-placeholder"></div>
		<? endif;?>
		<? foreach ($filters as $filtername => $filter): ?>
			<select name="filter_<?=$filtername?>" class="notfixed" data-label="<?=$filter['label']?>">
				<option value="">-</option>
				<? foreach ($filter['values'] as $filtervalue => $filteroption): 
					if ($filtervalue === false) continue; ?>
					<option value="<?=$filtervalue?>"<? if (isset($current_filter['filter_'.$filtername]) && $current_filter['filter_'.$filtername] === strval($filtervalue)):?> selected="selected"<? endif;?>><?=$filteroption?></option>
				<? endforeach; ?>
			</select>
		<? endforeach; ?>
	</div>
	<div class="filter-right">
		<div class="reset"><a href="#">Reset</a></div>
		<? if ($searchable): ?>
			<div class="search">
				<input type="search" name="search"<? if (isset($current_filter['search']) && $current_filter['search']):?> value="<?=$current_filter['search']?>"<? endif;?> placeholder="<?=l(array('nl'=>'Zoeken','fr'=>'Chercher','en'=>'Search'))?>"/> <a class="button" href="#"><span><?=l(array('nl'=>'Zoeken','fr'=>'Chercher','en'=>'Search'))?></span></a>
			</div>
		<? endif; ?>
	</div>
	<input type="hidden" name="parentname" value="<?=substr($field['stack'],0, strrpos($field['stack'],'>'))?>"/>
	<input type="hidden" name="parentid" value="<?=$parent_id?>"/>
</div>
<? endif;

if ($amount > 50): ?>
<div class="object-pagination top" data-pages="<?=ceil($amount/50)?>">
	<a class="prev" href="#">&larr;</a>
	<a class="next" href="#">&rarr;</a>
	<div class="pages">
		<a<? if ($page == 1):?> class="active"<? endif; ?> href="#" data-page="1">1</a>
		<span class="space before">&hellip;</span>
		<? for ($i=2;$i<ceil($amount/50);$i++): ?>
			<a<? if ($page == $i):?> class="active"<? endif; ?> href="#" data-page="<?=$i?>"><?=$i?></a>
		<? endfor; ?>
		<span class="space after">&hellip;</span>
		<a<? if ($page == ceil($amount/50)):?> class="active"<? endif; ?> href="#" data-page="<?=ceil($amount/50)?>"><?=ceil($amount/50)?></a>
	</div>
</div>
<? endif;

if (isset($field['title'])) echo '<label>'.$field['title'].'</label>';

if ($amount): ?>
	<table data-objectname="<?=$field['stack']?>" data-parentname="<?=substr($field['stack'],0, strrpos($field['stack'],'>'))?>" data-parentid="<?=$parent_id?>" data-page="1" class="list selectable<?=(isset($field['sortable']) && $field['sortable'])?' sortable':''?><?=(!$controls || (isset($field['creation_disabled']) && !isset($field['exportable'])))?' nocontrols':''?>">
		<thead>
			<tr>
				<? if (isset($field['sortable']) && $field['sortable']):?>
					<th width="1"></th>
				<? endif; ?>
				<? foreach ($headers as $i => $header):
					echo '<th'.($i==count($headers)-1?' colspan="2"':'').'>'.$header.'</th>';
				endforeach; ?>
			</tr>
		</thead>
		<?=view('object_rows',array(
			'data' => $data,
			'field' => $field,
			'object' => $object,
			'shownfields' => $shownfields,
			'allow_edit' => $allow_edit,
			'delete_limits' => $delete_limits,
			'recursive_name' => $recursive_name
		))?>
	</table>
<? else: ?>
	<table class="list<?=(!$controls || (isset($field['creation_disabled']) && !isset($field['exportable'])))?' nocontrols':''?>"><tr class="note"><td><?=l(array('nl'=>'Nog geen items.','fr'=>'Sans contenu.','en'=>'No items.'))?></td></tr></table>
<? endif; ?>
<? if ($amount > 50): ?>
<div class="object-pagination" data-pages="<?=ceil($amount/50)?>">
	<a class="prev" href="#">&larr;</a>
	<a class="next" href="#">&rarr;</a>
	<div class="pages">
		<a<? if ($page == 1):?> class="active"<? endif; ?> href="#" data-page="1">1</a>
		<span class="space before">&hellip;</span>
		<? for ($i=2;$i<ceil($amount/50);$i++): ?>
			<a<? if ($page == $i):?> class="active"<? endif; ?> href="#" data-page="<?=$i?>"><?=$i?></a>
		<? endfor; ?>
		<span class="space after">&hellip;</span>
		<a<? if ($page == ceil($amount/50)):?> class="active"<? endif; ?> href="#" data-page="<?=ceil($amount/50)?>"><?=ceil($amount/50)?></a>
	</div>
</div>
<? endif; ?>
<? if ($controls && (!isset($field['creation_disabled']) || isset($field['exportable']))): ?>
	<div class="tablecontrols">
		<? if (!isset($field['allow_edit']) && !isset($field['creation_disabled'])): ?>
			<a class="button" href="<?=$field['name']?>/add"><?=isset($field['label'])?l(array('nl'=>ucfirst($field['label']).' toevoegen','en'=>'Add '.strtolower($field['label']),'fr'=>'Ajouter '.strtolower($field['label']))):l(array('nl'=>'Toevoegen','fr'=>'Ajouter','en'=>'Add'))?></a>
		<? endif; ?>
		<? if (isset($field['exportable']) && $field['exportable']): ?>
			<a class="button" href="<?=$field['name']?>/export"><?=l(array('nl'=>'Exporteren','fr'=>'Exporter','en'=>'Export'))?></a>	
		<? endif; ?>
	</div>
<? endif; ?>
</div>
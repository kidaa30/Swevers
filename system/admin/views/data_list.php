<? if (count($data)): ?>
	<? if (count($data) > 10): ?>
		<div class="tablecontrols top">
			<? if ($export): ?>
				<a class="button" href="<?=$datasource?'list_data_'.$datasource:'list_object_'.$object?>/export"><?=l(array('nl'=>'Exporteren','fr'=>'Exporter','en'=>'Export'))?></a>	
			<? endif; ?>
			<span class="amount"><?=l(array('nl'=>'Totaal','fr'=>'Totale','en'=>'Total'))?>: <strong><?=count($data)?></strong></span>
		</div>
	<? endif;?>
	<table class="list<? if (!$export):?> nocontrols<? endif; ?>">
		<thead>
			<tr>
				<? foreach (reset($data) as $key => $value): ?>
					<th><?=$key?></th>
				<? endforeach; ?>
			</tr>
		</thead>
		<tbody>
			<? foreach ($data as $row): ?>
				<tr>
					<? foreach ($row as $key => $value): ?>
						<td><?=$value?></td>
					<? endforeach; ?>
				</tr>
			<? endforeach; ?>
		</tbody>
	</table>
	<? if ($export): ?>
		<div class="tablecontrols bottom">
			<? if ($export): ?>
				<a class="button" href="<?=$datasource?'list_data_'.$datasource:'list_object_'.$object?>/export"><?=l(array('nl'=>'Exporteren','fr'=>'Exporter','en'=>'Export'))?></a>	
			<? endif; ?>
		</div>
	<? endif;?>
<? else: ?>
	<table class="list nocontrols"><tr class="note"><td><?=l(array('nl'=>'Nog geen items.','fr'=>'Sans contenu.','en'=>'No items.'))?></td></tr></table>
<? endif;
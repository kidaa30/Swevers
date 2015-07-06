<? $root_id = 0;
if (!function_exists('print_row')) {
	function print_row($field,$shownfields,$row,$object,$show_edit=true,$level=0,$currentslug='',$parenturl='',$delete_limits=array()) { 
		global $root_id;
		if ($level == 0) echo '<tbody data-id="'.$row->id.'">';
		$fieldname = strval($field['name']);
		$typemanager = FW4_Type_Manager::get_instance(); ?>
		<tr<?=$level>0?' data-root="'.$root_id.'"':''?> data-id="<?=$row->id?>" onclick="window.location='<?=$parenturl.$field['name']?>/<?=$row->id?>/';">
			<? if (isset($field['sortable']) && $field['sortable']):?>
				<td>
				</td>
			<? endif;
			$i = 0;
			foreach ($shownfields as $name => $subfield): ?>
				<td<? if ($subfield->getName() == "price"):?> align="right"<? endif; ?>>
					<?
					if ($i++ == 0 && $level > 0) {
						for ($s=0;$s<$level;$s++) echo '&nbsp;&nbsp;&nbsp;';
						echo '&#9492; ';
					}
					if ($subfield->getName() == "bool") {
						if ($row->$name == 1) : ?>
							<img src="<?=url(ADMINRESOURCES.'images/tick.png')?>" class="bool" width="16" height="16"/>
						<? else: ?>
							<img src="<?=url(ADMINRESOURCES.'images/cross.png')?>" class="bool" width="16" height="16"/> 
						<? endif;
					} else if ($subfield->getName() == "date") {
						if ($row->$name) {
							echo date('j/m/Y',$row->$name);
						}
					} else if ($subfield->getName() == "timedate") {
						if ($row->$name) {
							echo date('j/m/Y H:i',$row->$name);
						}
					} else if ($subfield->getName() == "text") {
						if ($row->$name) {
							echo excerpt($row->$name,50);
						}
					} else if ($subfield->getName() == "slug") {
						foreach ($field->children() as $child):
							$childname = strval($child['name']);
							if (is_numeric($row->$childname) && isset($subfield['format_'.$child['name'].'_'.$row->$childname])):
								if (!$name) $name = 'slug';
								$i = 0;
								foreach (languages() as $key => $lang){
									$langname = $name.'_'.$key;
									if ($i++!=0) echo ' &bull; ';
									$link = url((count(languages()) > 1?$key.'/':'').str_replace('$slug',$row->$langname,$subfield['format_'.$child['name'].'_'.$row->$childname]),false);
									if (count(languages()) > 1) echo '<a href="'.$link.'">'.strtoupper($key).'</a>';
									else echo '<a href="'.$link.'">'.$link.'</a>';
								}
								$currentslug = str_replace('$slug',$row->$name,$subfield['format_'.$child['name'].'_'.$row->$childname]).'/';
							endif;
						endforeach;
					} else if ($type = $typemanager->get_type(strval($subfield->getName()))) {
						echo $type->summary($subfield,$row,$object);
					} else echo $row->$name; ?>
				</td>
			<? endforeach; ?>
			<? $deletable = true; ?>
			<? foreach ($delete_limits as $delete_limit_key => $delete_limit_value) {
				if (!isset($row->$delete_limit_key) || $row->$delete_limit_key != $delete_limit_value) $deletable = false;
			} ?>
			<td align="right">
				<? if ($deletable && !isset($field['delete_disabled'])): ?>
					<div style="white-space:nowrap;">
						<? if ($field['name'] != 'user' || $row->id != $user->id): ?>
							<a class="delete" href="<?=$parenturl.$field['name']?>/<?=$row->id?>/delete/" onclick="event.stopPropagation();return confirm('<?=l(array('nl'=>'Bent u zeker dat u dit item wilt verwijderen?','fr'=>'&Ecirc;tes-vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?','en'=>'Are you sure you want to remove this item?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"></a>
						<? endif; ?>
					</div>
				<? endif; ?>
			</td>
		</tr>
		
		<? if (isset($field['recursive']) && $field['recursive']) {
			if ($level == 0) $root_id = $row->id;
			foreach ($row->$fieldname as $subrow) {
				print_row($field,$shownfields,$subrow,$object,$show_edit,$level+1,$currentslug,$parenturl.$field['name'].'/'.$row->id.'/');
			}
		}
		if ($level == 0) echo '</tbody>';
	}
}

if (count($data)) foreach ($data as $row) print_row($field,$shownfields,$row,$object,$allow_edit,0,'','',$delete_limits);
else {
	$colcount = count($shownfields);
	if (isset($field['sortable']) && $field['sortable']) $colcount++;
	echo '<tr><td colspan="'.$colcount.'" class="nodata"><p>'.l(array('nl'=>'Geen resultaten','fr'=>'Pas de r&eacute;sultats','en'=>'No results')).'</p></td></tr>';
}
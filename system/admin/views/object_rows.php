<?
$GLOBALS['root_id'] = 0;
$GLOBALS['recursive_name'] = $recursive_name;
if (!function_exists('print_row')) {
	function print_row($field,$shownfields,$row,$object,$show_edit=true,$level=0,$currentslug='',$parenturl='',$delete_limits=array()) {
		if ($level == 0) echo '<tbody data-id="'.$row->id.'">';
		$user = FW4_User::get_user();
		$typemanager = FW4_Type_Manager::get_instance(); ?>
		<tr<?=$level>0?' data-root="'.$GLOBALS['root_id'].'"':''?> data-id="<?=$row->id?>" onclick="window.location='<?=$parenturl.($level>0?$GLOBALS['recursive_name']:$field['name'])?>/<?=$row->id?>/';">
			<? if (isset($field['sortable']) && $field['sortable']):?>
				<td valign="middle">
					<? if ($level == 0):?>
						<img class="sort-handle" src="<?=url(ADMINRESOURCES.'images/sort.png')?>" width="10" height="11"/><input type="hidden" name="sort-<?=$row->id?>" value="<?=$row->_sort_order?>" />
					<? endif; ?>
				</td>
			<? endif;
			$i = 0;
			foreach ($shownfields as $name => $subfield): ?>
				<td<? if ($subfield->getName() == "price"):?> align="right"<? endif; ?>><div class="overflow">
					<?
					if ($i++ == 0 && $level > 0) {
						echo '&nbsp;&nbsp;';
						for ($s=0;$s<$level-1;$s++) echo '&nbsp;&nbsp;&nbsp;&nbsp;';
						echo '<span style="opacity:0.5">&#9492;</span> ';
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
					} else if ($subfield->getName() == "float") {
						if ($row->$name) {
							echo rtrim(rtrim(number_format($row->$name,2,',','.'),'0'),',');
						}
					} else if ($subfield->getName() == "timedate") {
						if ($row->$name) {
							if (date('H:i',$row->$name) == '00:00') echo date('j/m/Y',$row->$name);
							else echo date('j/m/Y H:i',$row->$name);
						}
					} else if ($subfield->getName() == "text") {
						if (isset($row->$name)) {
							if ($subfield['summary'] == 'bool') {
								if (trim($row->$name)) : ?>
									<img src="<?=url(ADMINRESOURCES.'images/tick.png')?>" class="bool" width="16" height="16"/>
								<? else: ?>
									<img src="<?=url(ADMINRESOURCES.'images/cross.png')?>" class="bool" width="16" height="16"/> 
								<? endif;
							} else echo excerpt($row->$name,50);
						}
					} else if ($subfield->getName() == "slug") {
						foreach ($field->children() as $child):
							$childname = strval($child['name']);
							if (is_numeric($row->$childname) && isset($subfield['format_'.$child['name'].'_'.$row->$childname])):
								if (!$name) $name = 'slug';
								$i = 0;
								foreach (languages() as $key => $lang){
									$childlang = $name.'_'.$key;
									if ($i++!=0) echo ' &bull; ';
									$link = url((count(languages()) > 1?$key.'/':'').str_replace('$slug',$row->$childlang,$subfield['format_'.$child['name'].'_'.$row->$childname]),false);
									if (count(languages()) > 1) echo '<a href="'.$link.'">'.strtoupper($key).'</a>';
									else echo '<a href="'.$link.'">'.$link.'</a>';
								}
								$currentslug = str_replace('$slug',$row->$name,$subfield['format_'.$child['name'].'_'.$row->$childname]).'/';
							endif;
						endforeach;
					} else if ($type = $typemanager->get_type(strval($subfield->getName()))) {
						echo $type->summary($subfield,$row,$object);
					} else echo htmlentities_all($row->$name); ?>
				</div></td>
			<? endforeach; ?>
			<? $deletable = true; ?>
			<? foreach ($delete_limits as $delete_limit_key => $delete_limit_value) {
				if (!isset($row->$delete_limit_key) || $row->$delete_limit_key != $delete_limit_value) $deletable = false;
			} ?>
			<td align="right">
				<? if ($deletable && !isset($field['delete_disabled'])): ?>
					<div style="white-space:nowrap;">
						<? if ($field['name'] != 'user' || $row->id != $user->id): ?>
							<a class="delete" href="<?=$parenturl.($level>0?$GLOBALS['recursive_name']:$field['name'])?>/<?=$row->id?>/delete/" onclick="event.stopPropagation();return confirm('<?=l(array('nl'=>'Bent u zeker dat u dit item wilt verwijderen?','fr'=>'&Ecirc;tes-vous s&ucirc;r de vouloir supprimer cet &eacute;l&eacute;ment?','en'=>'Are you sure you want to remove this item?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"></a>
						<? endif; ?>
					</div>
				<? endif; ?>
			</td>
		</tr>
		<? if (isset($GLOBALS['recursive_name']) && $GLOBALS['recursive_name']) {
			if ($level == 0) $GLOBALS['root_id'] = $row->id;
			foreach ($row->$GLOBALS['recursive_name'] as $subrow) {
				print_row($field,$shownfields,$subrow,$object,$show_edit,$level+1,$currentslug,$parenturl.($level==0?$field['name']:$GLOBALS['recursive_name']).'/'.$row->id.'/');
			}
		}
		if ($level == 0) echo '</tbody>';
	}
}

foreach ($data as $row) {
	print_row($field,$shownfields,$row,$object,$allow_edit,0,'','',$delete_limits);
}
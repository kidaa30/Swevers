<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Family extends FW4_Type {

    public function print_field($field,$data,$object) { 
    	if (!isset($data['id'])) return false;
    	if (isset($object['editing_disabled'])) {
			return false;
		}
		$relatives = where($object['name'].'_id',$data['id'])->get($object['stack'].'/'.$field['name']);
		$relative_ids = array(); 
		foreach ($relatives as $relative) $relative_ids[] = $relative['other_id'];
		if (count($relatives)) $relative_objects = where_in('id',$relative_ids)->get($object['stack']);
		$types = array(
			 '' => array(
				1 => 'Vader/moeder',
				2 => 'Broer/zus',
				3 => 'Zoon/dochter',
		    	4 => 'Grootvader/grootmoeder',
		    	5 => 'Kleinzoon/kleindochter',
		    	6 => 'Oom/tante',
		    	7 => 'Neef/nicht',
				8 => 'Neef/nicht',
				9 => 'Echtgenoot/echtgenote'
			), 'm' => array(
				1 => 'Vader',
				2 => 'Broer',
				3 => 'Zoon',
		    	4 => 'Grootvader',
		    	5 => 'Kleinzoon',
		    	6 => 'Oom',
		    	7 => 'Neef',
				8 => 'Neef',
				9 => 'Echtgenoot'
			), 'v' => array(
				1 => 'Moeder',
				2 => 'Zus',
				3 => 'Dochter',
		    	4 => 'Grootmoeder',
		    	5 => 'Kleindochter',
		    	6 => 'Tante',
		    	7 => 'Nicht',
				8 => 'Nicht',
				9 => 'Echtgenote'
		));
		?>
    	<div class="input">
	    	<label><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    	<table data-objectname="<?=$object['stack'].'/'.$field['name']?>" class="list sortable">
	    		<? if (count($relatives)): ?>
		    		<? foreach ($relatives as $relative): ?>
		    			<tr data-other-id="<?=$relative['other_id']?>">
		    				<td width="0"><img class="sort-handle" alt="<?=l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort'))?>" title="<?=l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort'))?>" src="<?=url(ADMINRESOURCES.'images/sort.png')?>" width="10" height="11"/><input type="hidden" name="sort-<?=$relative['id']?>" value="<?=$relative['sort_order']?>" /></td>
		    				<td width="100%">
		    					<div><a href="<?=preg_replace('/[^\/]+\/?$/', $relative['other_id'].'/', $_SERVER['REQUEST_URI'])?>"><?=$relative_objects[$relative['other_id']]['firstname']?> <?=$relative_objects[$relative['other_id']]['lastname']?></a> (<?=$types[$relative_objects[$relative['other_id']]['sex']][$relative['kind']]?>)</div>
		    				</td>
		    				<td width="0">
		    					<a class="delete" href="<?=$field['name']?>/delete/<?=$relative['id']?>" onclick="return confirm('<?=l(array('nl'=>'Bent u zeker dat u deze relatie wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette relation?','en'=>'Are you sure you want to delete this relation?'))?>');"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"/></a>
		    				</td>
		    			</tr>
		    		<? endforeach; ?>
		    	<? else: ?>
		    		<tr class="note"><td><?=l(array('nl'=>'Nog geen gegevens.','fr'=>'Pas d&lsquo;info.','en'=>'No data.'))?></td></tr>
	    		<? endif; ?>
	    	</table>
	    	<div class="tablecontrols family">
		    	<input data-object="<?=$object['stack'].'/'.$field['name']?>" data-current-id="<?=isset($data['id'])?$data['id']:0?>" class="family_picker" type="text" name="<?=strval($field['name'])?>" placeholder="Typ de naam van een lid"/>
		    	<select name="<?=strval($field['name'])?>_type">
		    		<option value="9">Echtgenoot/echtgenote</option>
		    		<option value="1">Vader/moeder</option>
	    			<option value="2">Broer/zus</option>
	    			<option value="3">Zoon/dochter</option>
	    			<option value="4">Grootvader/grootmoeder</option>
	    			<option value="5">Kleinzoon/kleindochter</option>
	    			<option value="6">Oom/tante</option>
	    			<option value="7">Neef/nicht (kind van oom/tante)</option>
	    			<option value="8">Neef/nicht (kind van broer/zus)</option>
		    	</select>
		    	<input type="hidden" name="<?=strval($field['name'])?>_id"/>
		    	<a class="button family_add" style="float:right;margin:0" href="#">Toevoegen</a>
	    	</div>
    	</div><?
    }
    
	public function edit($data,$field,$newdata,$olddata,$object) {
		unset($data[strval($field['name'])]);
		unset($data[strval($field['name']).'_type']);
		unset($data[strval($field['name']).'_id']);
		return $data;
	}
    
    public function edited($field,$data,$object) {

	    if (isset($_FILES[strval($field['name'])]) && is_array($_FILES[strval($field['name'])]['name'])) {
    		$files=array();
    		foreach ($_FILES[strval($field['name'])]['name'] as $index => $file_name) {
    			$newfile = array();
    			foreach (array_keys($_FILES[strval($field['name'])]) as $key) {
    				$newfile[$key] = $_FILES[strval($field['name'])][$key][$index];
    			}
    			$files[] = $newfile;
    		}
    		
    		foreach ($files as $f) {
    			if ($f['size'] && strstr($f['type'], 'image')) {
    				$toinsert = array();
    				
    				$extension = substr($f['name'], strrpos($f['name'], '.')+1);
    				do {
    					$name = md5(rand(0,99999).rand(0,99999));
    				} while (file_exists(FILESPATH.$name.".".$extension));
    				
    				move_uploaded_file($f['tmp_name'], FILESPATH.$name.".".$extension);
    				$toinsert['orig_filename'] = $f['name'];
    				$toinsert['filename'] = $name.'.'.$extension;
    				$toinsert['upload_date'] = time();
    				
    				$toinsert[$object['name']."_id"] = intval($data['id']);
    		
    				insert($object['stack'].'/'.$field['name'],$toinsert);
    			}
    		}
    	}
    	
    }
    
    public function deleted($field,$data) {
	    foreach ($data as $row) {
	    	delete_thumbnails($row);
	    	@unlink(FILESPATH.$row['filename']);
	    }
    }
    
    public function get_structure($field,$fields) {
    	$sortable = isset($field['sortable'])&&$field['sortable']?true:false;
	    return '<structure>
	    	<object name="'.$field['name'].'" sortable="true">
	    		<number name="other_id" index="index"/>
	    		<choice name="kind" index="index">
	    			<option value="9">Echtgenoot/echtgenote</option>
	    			<option value="1">Vader/moeder</option>
	    			<option value="2">Broer/zus</option>
	    			<option value="3">Zoon/dochter</option>
	    			<option value="4">Grootvader/grootmoeder</option>
	    			<option value="5">Kleinzoon/kleindochter</option>
	    			<option value="6">Oom/tante</option>
	    			<option value="7">Neef/nicht (kind van oom/tante)</option>
	    			<option value="8">Neef/nicht (kind van broer/zus)</option>
	    		</choice>
	    	</object>
	    </structure>';
    }
    
    public function function_delete($field,$object,$data,$id) {
    	$objectname = strval($object['name']);
    	if ($row = where('id',intval($id))->get_row($object['stack'].'/'.$field['name'])) {
    		where('other_id',$row[$objectname.'_id'])->where($objectname.'_id',$row['other_id'])->delete($object['stack'].'/'.$field['name']);
	    	where('id',$row['id'])->delete($object['stack'].'/'.$field['name']);
    		redirect($_SERVER['HTTP_REFERER']);
    	} else error(404);
    }
    
    function get_scripts() { 
	    return utf8_encode('<script>
			$(function(){
				$("input.family_picker").autocomplete({
					source: function( request, response ) {
						var otherids = [];
						$(this.element).parents(".input").find("tr[data-other-id]").each(function(){
							otherids.push($(this).data("other-id"));
						});
						$.ajax({
							url: "/admin/family/ajax_autocomplete/",
							dataType: "json",
							type: "post",
							data: {
								search: request.term,
								currentid: $(this.element).data("current-id"),
								otherids: otherids
							},
							success: function( data ) {
								
								response( $.map( data, function( item ) {
									return {
								    	label: item.firstname + " " + item.lastname,
										value: item.firstname + " " + item.lastname,
										id : item.id
									}
								}));
							}
						});
					},
					minLength: 2,
					select: function( event, ui ) {
						if (ui.item) $(this).parents(".tablecontrols").find(\'input[type="hidden"]\').val(ui.item.id);
						else $(this).parents(".tablecontrols").find(\'input[type="hidden"]\').val(0);
					},
					open: function() {
						$( this ).removeClass( "ui-corner-all" ).addClass( "ui-corner-top" );
					},
					close: function() {
						$( this ).removeClass( "ui-corner-top" ).addClass( "ui-corner-all" );
					}
				}).blur(function(){
					if (!$(this).parents(".tablecontrols").find(\'input[type="hidden"]\').val()) $(this).val("").autocomplete( "search", "" );
				});
				$("a.family_add").click(function(){
					var id = $(this).parents(".tablecontrols").find(\'input[type="hidden"]\').val();
					var familytable = $(this).parents(".input");
					if (id) {
						$.ajax({
							url: "/admin/family/ajax_add_relation/"+$(this).parents(".tablecontrols").find(\'input[type="text"]\').data("object")+"/",
							dataType: "html",
							type: "post",
							data: {
								currentid: $(this).parents(".tablecontrols").find(\'input[type="text"]\').data("current-id"),
								targetid: id,
								type: $(this).parents(".tablecontrols").find(\'select\').val(),
								url: window.location.origin + window.location.pathname
							},
							success: function( data ) {
								familytable.find("tbody tr.note").remove();
								familytable.find("tbody").prepend(data);
								$("table.sortable tbody").sortable( "refresh" );
							},
							error: function(data) {
								alert(data);
							}
						});
						$(this).parents(".tablecontrols").find(\'input[type="hidden"]\').val(0);
						$(this).parents(".tablecontrols").find(\'input[type="text"]\').val("").autocomplete( "search", "" );
						
					} else alert("Geen geldig lid gekozen. Selecteer een lid.");
					return false;
				});
			});
		</script>');
    }
    
    public function function_ajax_autocomplete() {
    	$query = limit(4)->where_not('id',intval($_POST['currentid']))->where_str('(CONCAT(firstname," ",lastname) LIKE "'.mysql_real_escape_string(($_POST['search'])).'%" OR CONCAT(lastname," ",firstname) LIKE "'.mysql_real_escape_string(($_POST['search'])).'%")');
    	if (is_array($_POST['otherids'])) $query->where_not_in('id',$_POST['otherids']);
	    echo json_encode(array_values($query->get('leden/lid')));
    }
    
    public function function_ajax_add_relation() {
    	$types = array(
			 '' => array(
				1 => 'Vader/moeder',
				2 => 'Broer/zus',
				3 => 'Zoon/dochter',
		    	4 => 'Grootvader/grootmoeder',
		    	5 => 'Kleinzoon/kleindochter',
		    	6 => 'Oom/tante',
		    	7 => 'Neef/nicht',
				8 => 'Neef/nicht',
				9 => 'Echtgenoot/echtgenote'
			), 'm' => array(
				1 => 'Vader',
				2 => 'Broer',
				3 => 'Zoon',
		    	4 => 'Grootvader',
		    	5 => 'Kleinzoon',
		    	6 => 'Oom',
		    	7 => 'Neef',
				8 => 'Neef',
				9 => 'Echtgenoot'
			), 'v' => array(
				1 => 'Moeder',
				2 => 'Zus',
				3 => 'Dochter',
		    	4 => 'Grootmoeder',
		    	5 => 'Kleindochter',
		    	6 => 'Tante',
		    	7 => 'Nicht',
				8 => 'Nicht',
				9 => 'Echtgenote'
		));
    	$stack = array_slice(func_get_args(),2);
    	if (!count($stack)) return false;
    	$result = $this->add_relation(implode('/',$stack),intval($_POST['currentid']),intval($_POST['targetid']),intval($_POST['type']));
    	$person = where('id',intval($_POST['targetid']))->get_row(implode('/',array_slice($stack,0,-1)));
	    echo '<tr data-other-id="'.$result['other_id'].'"><td width="0"><img class="sort-handle" alt="'.l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort')).'" title="'.l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort')).'" src="'.url(ADMINRESOURCES.'images/sort.png').'" width="10" height="11"/><input type="hidden" name="sort-'.$result['id'].'" value="'.$result['sort_order'].'" /></td>
		<td width="100%"><div><a href="'.preg_replace('/[^\/]+\/?$/', $result['other_id'].'/', $_POST['url']).'">'.$person['firstname'].' '.$person['lastname'].'</a> ('.$types[$person['sex']][$result['kind']].')</div>
		</td><td width="0"><a class="delete" href="'.end($stack).'/delete/'.$result['id'].'" onclick="return confirm(\''.l(array('nl'=>'Bent u zeker dat u deze relatie wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette relation?','en'=>'Are you sure you want to delete this relation?')).'\');"><img alt="'.l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete')).'" title="'.l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete')).'" src="'.url(ADMINRESOURCES.'images/del.png').'" width="22" height="23"/></a></td></tr>';
    }
    
    private function add_relation($stack,$source,$target,$type) {
	    switch ($type) {
		    case 1:
		    	$this->insert_relation($stack,$target,$source,3);
				break;
			case 2:
		    	$this->insert_relation($stack,$target,$source,2);
				break;
			case 3:
		    	$this->insert_relation($stack,$target,$source,1);
				break;
			case 4:
		    	$this->insert_relation($stack,$target,$source,5);
				break;
			case 5:
		    	$this->insert_relation($stack,$target,$source,4);
				break;
			case 6:
		    	$this->insert_relation($stack,$target,$source,8);
				break;
			case 7:
		    	$this->insert_relation($stack,$target,$source,7);
				break;
			case 8:
		    	$this->insert_relation($stack,$target,$source,6);
				break;
			case 9:
		    	$this->insert_relation($stack,$target,$source,9);
				break;
	    }
	    return $this->insert_relation($stack,$source,$target,$type);
    }
    
    private function insert_relation($stack,$source,$target,$type) {
    	$stack_segments = explode('/',$stack);
    	array_pop($stack_segments);
    	$existing = where(end($stack_segments).'_id',$source)->where('other_id',$target)->where('kind',$type)->get_row($stack);
    	if (!$existing) {
		    insert($stack,array(
		    	end($stack_segments).'_id' => $source,
		    	'other_id' => $target,
		    	'kind' => $type
		    ));
    	}
    	return where(end($stack_segments).'_id',$source)->where('other_id',$target)->where('kind',$type)->get_row($stack);
    }

}
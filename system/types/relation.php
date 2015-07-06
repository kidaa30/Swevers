<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Relation_type extends FW4_Type {
	
	public function print_field($field,$data,$object) { 
		$source = isset($field['source'])?strval($field['source']):$object['stack'];
		$fieldname = strval($field['name']);
		$possible_relations = array();
		
		if (!$structure = FW4_Structure::get_object_structure($source,false)) return false;
		$titlefields = $structure->xpath('string');
		if (!($titlefield = reset($titlefields))) return false;
		$titlefield = strval($titlefield['name']);
		
		$source_name = strtolower(strval($structure['label']));
		
		foreach (get($source) as $possible_relation) {
			$possible_relations[$possible_relation->id] = array('id' => $possible_relation->id, 'title' => $possible_relation->$titlefield);
		}
		
		$current_relations = array();
		if ($data && isset($data->id)) {
			foreach (where($object['name'].'_id = %d',$data->id)->get($object['stack'].'>'.$fieldname) as $current_relation) {
				$current_relations[$current_relation->id] = $possible_relations[$current_relation->other_id];
				$current_relations[$current_relation->id]['sort_order'] = $current_relation->_sort_order;
			}
		} ?>
		
		<div class="input">
	    	<label><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
	    	<table data-fieldname="<?=$field['name']?>" data-objectname="<?=$object['stack']?>" data-source="<?=$source?>" class="list sortable">
	    		<? if (count($current_relations)): ?>
		    		<? foreach ($current_relations as $id => $current_relation): ?>
		    			<tbody data-id="<?=$current_relation['id']?>">
			    			<tr data-id="<?=$id?>" data-other-id="<?=$current_relation['id']?>">
			    				<td width="0"><img class="sort-handle" alt="<?=l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort'))?>" title="<?=l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort'))?>" src="<?=url(ADMINRESOURCES.'images/sort.png')?>" width="10" height="11"/><input type="hidden" name="<?=$field['name']?>-sort[]" value="<?=$current_relation['sort_order']?>"/></td>
			    				<td width="100%">
			    					<div><?=$current_relation['title']?></div>
			    				</td>
			    				<td width="0">
			    					<a class="delete" href="#" onclick="relation_remove($(this).parents('tr'));return false;"><img alt="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" title="<?=l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete'))?>" src="<?=url(ADMINRESOURCES.'images/del.png')?>" width="22" height="23"/></a>
			    					<input type="hidden" name="<?=strval($field['name'])?>_ids[]" value="<?=$current_relation['id']?>"/>
			    				</td>
			    			</tr>
		    			</tbody>
		    		<? endforeach; ?>
		    	<? else: ?>
		    		<tr class="note"><td><?=l(array('nl'=>'Nog geen gegevens.','fr'=>'Pas d&lsquo;info.','en'=>'No data.'))?></td></tr>
	    		<? endif; ?>
	    	</table>
	    	<div class="tablecontrols family">
		    	<input data-object="<?=$source?>" data-current-id="<?=isset($data->id)?$data->id:0?>" class="relation_picker" type="text" name="<?=strval($field['name'])?>" placeholder="Koppel een <?=$source_name?>"/>
	    	</div>
    	</div>
    	
		<script>
			var relation<?=$field['name']?> = <?=json_encode(array_values($possible_relations));?>;
		</script>
    <? }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
		return $data;
	}
	
    public function edited($field,$data,$object) {
	    
    	$fieldname = strval($field['name']);
    	$newrelations = isset($_POST[$fieldname.'_ids'])?$_POST[$fieldname.'_ids']:array();
    	$sorting = isset($_POST[$fieldname.'-sort'])?$_POST[$fieldname.'-sort']:array();
    	
    	$source = isset($field['source'])?strval($field['source']):$object['stack'];
    	
    	if (count($newrelations)) where($object['name'].'_id = %d',$data->id)->where('other_id NOT IN %$',$newrelations)->delete($object['stack'].'>'.$fieldname);
    	else where($object['name'].'_id = %d',$data->id)->delete($object['stack'].'>'.$fieldname);
    	
    	foreach ($data->$fieldname as $relation) {
    		$index = array_search($relation->id,$newrelations);
	    	if ($index !== false) {
		    	where($object['name'].'_id = %d',$data->id)->where('other_id = %d',$relation->id)->update($object['stack'].'>'.$fieldname,array(
			    	'_sort_order' => $sorting[$index]
		    	));
		    	unset($newrelations[$index]);
		    	unset($sorting[$index]);
		    }
    	}
    	
	    foreach ($newrelations as $index => $newrelation) {
		    insert($object['stack'].'>'.$fieldname,array(
		    	$object['name'].'_id' => $data->id,
		    	'other_id' => $newrelation,
		    	'_sort_order' => $sorting[$index]
		    ));
		    if ($source == $object['stack'] && (!isset($field['mutual']) || strval($field['mutual']) != 'false')) {
			    insert($object['stack'].'>'.$fieldname,array(
			    	$object['name'].'_id' => $newrelation,
			    	'other_id' => $data->id
			    ));
		    }
	    }
    }
    
	private static $_deleting = false;
    public function deleted($field,$data) {
	    if (!self::$_deleting && (!isset($field['mutual']) || strval($field['mutual']) != 'false')) {
		    self::$_deleting = true;
	    	$fieldname = $field['name'];
	    	$parentname_id = $field['parent_name'].'_id';
			foreach ($data as $row) {
				if (strval($field['source']) == strval($field['parent_stack'])) where($parentname_id.' = %d',intval($row->other_id))->where('other_id = %d',intval($row->$parentname_id))->delete(strval($field['stack']));
		    }
		    self::$_deleting = false;
		}
    }
    
    public function function_ajax_autocomplete($field,$object,$data) {
    	$query = limit(5)->where('id != %d',intval($_POST['currentid']));
    	
    	$searchable_fields = array();
    	
    	$source = isset($field['source'])?strval($field['source']):$object['stack'];
		
		if (!$structure = FW4_Structure::get_object_structure($source,false)) return false;
		$possible_searchable_fields = $structure->xpath('string');
		$titlefield = reset($possible_searchable_fields);
		$titlefield = strval($titlefield['name']);
		foreach ($structure->xpath('string') as $searchable_field) $searchable_fields[] = strval($searchable_field['name']);
    	
		$search_where_strings = array();
		foreach (explode(' ',$_POST['search']) as $keyword) {
			$search_where_strings[] = 'CONCAT(IFNULL(`'.implode('`,""),IFNULL(`',$searchable_fields).'`,"")) LIKE '.$query->escape('*'.$keyword.'*');
		}
		$query->where(implode(' AND ',$search_where_strings));
    	
    	if (isset($_POST['otherids']) && is_array($_POST['otherids']) && count($_POST['otherids'])) $query->where('id NOT IN %$',$_POST['otherids']);
    	
    	$result = array();
    	foreach ($query->get($source) as $row) {
	    	$result[] = array(
		    	'id' => $row->id,
		    	'title' => $row->$titlefield
	    	);
    	}
    	
	    echo json_encode($result);
    }
    
    function get_structure($field,$fields) {
	    $source = isset($field['source'])?strval($field['source']):$fields['stack'];
    	return '<structure>
    		<object name="'.$field['name'].'" sortable="sortable" source="'.$source.'" order="_sort_order asc,id asc" parent_stack="'.$fields['stack'].'">
    			<number name="other_id" length="10" index="index"/>
    		</object>
    		<dbrelation name="'.$field['name'].'" source="'.$source.'" link_table="'.$fields['stack'].'/'.$field['name'].'" local_key="id" link_local_key="'.$fields['name'].'_id" link_foreign_key="other_id" foreign_key="id"/>
    	</structure>';
    }
    
    function get_scripts() { 
	    return utf8_encode('<script>
			$(function(){
				$("input.relation_picker").autocomplete({
					source: function( request, response ) {
						var otherids = [];
						$(this.element).parents(".input").find("tr[data-other-id]").each(function(){
							otherids.push($(this).data("other-id"));
						});
						$.ajax({
							url: $(this.element).parents(".input").find("table").data("fieldname")+"/ajax_autocomplete/",
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
								    	label: item.title,
										value: item.title,
										id : item.id
									}
								}));
							}
						});
					},
					minLength: 2,
					select: function( event, ui ) {
						if (ui.item) {
							var relationtable = $(this).parents(".input");
							$(this).val("").autocomplete( "search", "" );
							var html = \'<tr data-other-id="\'+ui.item.id+\'"><td width="0"><img class="sort-handle" alt="'.l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort')).'" title="'.l(array('nl'=>'Sorteren','fr'=>'Sorter','en'=>'Sort')).'" src="'.url(ADMINRESOURCES.'images/sort.png').'" width="10" height="11"/><input type="hidden" name="\'+$(this).parents(".input").find("table").data("fieldname")+\'-sort[]"/></td>\';
							html += \'<td width="100%"><div>\'+ui.item.value+\'</div></td>\';
							html += \'<td width="0"><a class="delete" href="#" onclick="relation_remove($(this).parents(\\\'tr\\\'));return false;"><img alt="'.l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete')).'" title="'.l(array('nl'=>'Verwijderen','fr'=>'Supprimer','en'=>'Delete')).'" src="'.url(ADMINRESOURCES.'images/del.png').'" width="22" height="23"/></a><input type="hidden" name="\'+$(this).parents(".input").find("table").data("fieldname")+\'_ids[]" value="\'+ui.item.id+\'"/></td></tr>\';
							relationtable.find("tbody tr.note").remove();
							relationtable.find("tbody").append(html);
							$("table.sortable tbody").sortable( "refresh" );
							var i = 0;
				        	relationtable.find(\'td:first-child input[type="hidden"]\').each(function(index,el){
				        		$(el).val(++i);
				        	});
							return false;
						}
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
			});
			function relation_remove(row) {
				var relationtable = row.parents("table");
				row.remove();
				if (relationtable.find("tr").length == 0) {
					relationtable.find("tbody").prepend(\'<tr class="note"><td>'.l(array('nl'=>'Nog geen gegevens.','fr'=>'Pas d&lsquo;info.','en'=>'No data.')).'</td></tr>\');
				}
			}
		</script>');
    }

}
<?

require_once(BASEPATH.'model.php');

class Resultset implements Iterator {

    private
        $_statement,
        $_data = false,
        $_ids = array(),
        $_id_indexes = array(),
        $_next = false,
        $_rowcount,
        $_children = array(),
        $_relations = array(),
        $_fetchable_fields = array(),
        $_stack,
        $_name,
        $_parents,
        $_parent_name,
        $_parent_id_field,
        $_parent_stack,
        $_parent_ids = array();

    public function __construct($statement,$stack,$name,$with_parent=false) {
        $this->_statement = $statement;
        $this->_rowcount = $this->_statement->rowCount();
        $this->_stack = $stack;
        $this->_name = $name;
        
        $stack_parts = explode('>',$stack);
        array_pop($stack_parts);
        if ($with_parent) {
	        $this->_parent_name = end($stack_parts);
	        if ($this->_parent_name) {
		        $this->_parent_id_field = $this->_parent_name.'_id';
				$this->_parent_stack = implode('>',$stack_parts);
			}
		}
    }
    
    private function _fetch() {
    	$this->_data = $this->_statement->fetchAll();
    	foreach ($this->_data as $key => $row) {
    		$this->_ids[] = intval($row->id);
    		$parent_id_fieldname = $this->_parent_id_field;
    		if ($this->_parent_id_field && isset($row->$parent_id_fieldname)) {
	    		$this->_parent_ids[$row->$parent_id_fieldname] = $row->$parent_id_fieldname;
	    	}
    		$this->_id_indexes[$row->id] = $key;
    		$row->_set_resultset($this);
    		foreach ($this->_fetchable_fields as $fetchable_field) $fetchable_field['type']->on_fetch($fetchable_field['field'],$row);
    	}
    	reset($this->_data);
    }

    public function rewind() {
    	if ($this->_data !== false) {
	        reset($this->_data);
	        $this->next();
	    }
    }
    
    public function first() {
    	if ($this->_data === false) $this->next();
    	return isset($this->_data[0])?$this->_data[0]:NULL;
    }
    
    public function rowCount() { return $this->_rowcount; }
    public function length() { return $this->_rowcount; }
    public function count() { return $this->_rowcount; }
    
    public function ids() {
    	if ($this->_data === false) $this->next();
    	return $this->_ids;
    }
    
    public function to_array() { 
    	if ($this->_data === false) $this->next();
    	return $this->_data;
    }
    public function shift() { 
    	if ($this->_data === false) $this->next();
    	if ($this->_rowcount) $this->_rowcount--;
    	array_shift($this->_ids);
    	return array_shift($this->_data);
    }

    public function valid() { 
    	if ($this->_data === false) $this->next();
    	return $this->_next !== false;
    }

    public function current() {
    	if ($this->_data === false) $this->next();
        return $this->_next[1];
    }

    public function key() {
    	if ($this->_data === false) $this->next();
        return $this->_next[0];
    }

    public function next() {
    	if ($this->_data === false) $this->_fetch();
        $this->_next = each($this->_data);
		return $this->current();
    }
    
    public function row_with_id($id) {
    	if ($this->_data === false) $this->next();
	    if (isset($this->_id_indexes[$id]) && isset($this->_data[$this->_id_indexes[$id]])) return $this->_data[$this->_id_indexes[$id]];
	    else return NULL;
    }
    
    public function get_random() {
	    if ($this->_data === false) $this->next();
	    if ($this->_rowcount == 0) return NULL;
	    return $this->_data[array_rand($this->_data)];
    }
    
    public function _children($children) {
    	$this->_children = $children;
    }
    
    public function _relations($relations) {
    	$this->_relations = $relations;
    }
    
    public function _fetchable_fields($fetchable_fields) {
    	$this->_fetchable_fields = $fetchable_fields;
    }
    
    public function _load_child($child,$id) {
	    if ($this->_stack == '_search_index' && $child == 'object') {
		    if ($this->count() == 0) return null;		    
		    $objects = array();
		    for ($key=0;$key<count($this->_data);$key++) {
			    $this->_data[$key]->object = NULL;
			    $row = $this->_data[$key];
			    if (!isset($objects[$row->object_name])) $objects[$row->object_name] = array();
			    $objects[$row->object_name][$row->object_id] = $key;
		    }
		    $return = null;
		    foreach ($objects as $object_name => $ids) {
			    foreach (where('id IN %$',array_keys($ids))->get($object_name) as $child_object) {
				    if (isset($ids[$child_object->id])) {
					    $key = $ids[$child_object->id];
					    $this->_data[$key]->object = $child_object;
					    if ($this->_data[$key]->id == $id) $return = &$this->_data[$key]->object;
				    }
			    }
		    }
		    return $return;
	    } else if (isset($this->_relations[$child])) {
    		if ($this->count() == 0) return array();
    		$objects = array();
    		
			$local_key = $this->_relations[$child]['local_key'];
			$foreign_key = $this->_relations[$child]['foreign_key'];
    		if ($local_key == 'id') {
				$local_keys = $this->_ids;
			} else {
    			$local_keys = array();
    			for ($i=0;$i<count($this->_data);$i++) $local_keys[] = $this->_data[$i]->$local_key;
			}
			
    		if (isset($this->_relations[$child]['link_table'])) {
    			$child_ids = $relation_ids = array();
    			$link_local_key = $this->_relations[$child]['link_local_key'];
    			$link_foreign_key = $this->_relations[$child]['link_foreign_key'];
    			if ($local_key == 'id') {
    				$local_keys = $this->_ids;
    			} else {
	    			$local_keys = array();
	    			foreach ($this->_data as $row) $local_keys[] = $row->$local_key;
    			}
	    		$child_objects = where($link_local_key.' IN %$',$local_keys);
	    		if (isset($this->_relations[$child]['order'])) $child_objects->order_by($this->_relations[$child]['order']);
	    		$child_objects = $child_objects->get($this->_relations[$child]['link_table']);
	    		if ($child_objects->count() > 0) {
		    		foreach ($child_objects as $child_object) {
		    			$child_ids[] = array($child_object->$link_local_key,$child_object->$link_foreign_key);
		    			$relation_ids[] = $child_object->$link_foreign_key;
		    		}
		    		$child_objects = where($foreign_key.' IN %$',$relation_ids)->get($this->_relations[$child]['source']);
		    		$children = array();
		    		foreach ($child_objects as $child_object) {
		    			$children[$child_object->$foreign_key] = $child_object;
		    			//$child_object->_set_resultset($this);
		    		}
		    		foreach ($child_ids as $child_object) {
		    			if (!isset($objects[$child_object[0]])) $objects[$child_object[0]] = array();
		    			$objects[$child_object[0]][$children[$child_object[1]]->id] = $children[$child_object[1]];
		    		}
		    	}
		    	for ($key=0;$key<count($this->_data);$key++) {
	    			$resultset = new Resultset($child_objects,$this->_stack.'/'.$child,$child);
	    			$resultset->_data = isset($objects[$this->_data[$key]->id])?array_values($objects[$this->_data[$key]->id]):array();
	    			$resultset->_rowcount = count($resultset->_data);
	    			$resultset->_ids = isset($objects[$this->_data[$key]->id])?array_keys($objects[$this->_data[$key]->id]):array();
	    			$this->_data[$key]->$child = $resultset;
	    			if ($this->_data[$key]->id == $id) $return = &$this->_data[$key]->$child;
	    		}
    		} else {
    			$child_objects = where($foreign_key.' IN %$',$local_keys);
    			if (isset($this->_relations[$child]['order'])) $child_objects->order_by($this->_relations[$child]['order']);
    			$child_objects = $child_objects->get($this->_relations[$child]['source']);
	    		foreach ($child_objects as $child_object) {
	    			if ($foreign_key == 'id') {
			    		$objects[$child_object->id] = $child_object;
		    		} else {
	    				if (!isset($objects[$child_object->parent_id])) $objects[$child_object->parent_id] = array();
		    			$objects[$child_object->parent_id][$child_object->id] = $child_object;
		    		}
	    		}
	    		for ($key=0;$key<count($this->_data);$key++) {
	    			if ($foreign_key == 'id') {
			    		$this->_data[$key]->$child = isset($objects[$this->_data[$key]->$local_key])?$objects[$this->_data[$key]->$local_key]:NULL;
		    		} else {
			    		$resultset = new Resultset($child_objects,$this->_relations[$child]['source'],$child);
		    			$resultset->_data = isset($objects[$this->_data[$key]->$local_key])?array_values($objects[$this->_data[$key]->$local_key]):array();
		    			$resultset->_rowcount = count($resultset->_data);
		    			$resultset->_ids = isset($objects[$this->_data[$key]->$local_key])?array_keys($objects[$this->_data[$key]->$local_key]):array();
		    			$this->_data[$key]->$child = $resultset;
		    		}
		    		if ($this->_data[$key]->id == $id) $return = &$this->_data[$key]->$child;
	    		}
    		}
    		
    		return $return;
    	} else if (isset($this->_children[$child])) {
    		if ($this->count() == 0) return array();
    		$objects = array();
    		$parent_key = $this->_name.'_id';
    		$query = is_bool($this->_children[$child])?limit(false):$this->_children[$child];
    		if ($limit = $query->limit()) $query->limit(false);
    		$child_objects = $query->where($parent_key.' IN %$',$this->_ids)->get($this->_stack.'>'.$child);
    		foreach ($child_objects as $child_object) {
    			if (!isset($objects[$child_object->$parent_key])) $objects[$child_object->$parent_key] = array();
    			//$child_object->_set_resultset($this);
    			if (!$limit || count($objects[$child_object->$parent_key]) < $limit) $objects[$child_object->$parent_key][$child_object->id] = $child_object;
    		}
    		$return = array();
    		for ($key=0;$key<count($this->_data);$key++) {
    			$resultset = new Resultset($child_objects,$this->_stack.'/'.$child,$child);
    			$resultset->_data = isset($objects[$this->_data[$key]->id])?array_values($objects[$this->_data[$key]->id]):array();
    			$resultset->_rowcount = count($resultset->_data);
    			$resultset->_ids = isset($objects[$this->_data[$key]->id])?array_keys($objects[$this->_data[$key]->id]):array();
    			$this->_data[$key]->$child = $resultset;
    			if ($this->_data[$key]->id == $id) $return = &$this->_data[$key]->$child;
    		}
    		return $return;
    	}
    	return false;
    }
    
    public function _get_parent_with_id($id) {
	    if ($this->_parents) return $this->_parents->row_with_id($id);
	    
	    if (count($this->_parent_ids) && $this->_parent_stack) {
		    $this->_parents = where('id IN %$',$this->_parent_ids)->get($this->_parent_stack);
		    return $this->_parents->row_with_id($id);
	    }
	    
	    return NULL;
    }
    
    public function _get_parent_id_fieldname() {
	    return $this->_parent_id_field;
    }
    
    public function sort_by_value_of_field($field) {
	    
	    if ($this->_data === false) $this->next();
	    
	    uasort($this->_data, function($a, $b) use ($field) {
	    	return strnatcasecmp($a->$field, $b->$field);
	    });
	    
	    $this->_ids = $this->_id_indexes = array();
	    foreach ($this->_data as $key => $row) {
    		$this->_ids[] = $row->id;
    		$this->_id_indexes[$row->id] = $key;
    	}
		
	}
	
	public function get_stack() { return $this->_stack; }

}
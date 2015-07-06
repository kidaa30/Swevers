<?php

/* ---------------------
FW4 FRAMEWORK - DATABASE
------------------------

The database classes make queries easy and safe. Avoid using these classes directly. Use functions instead. */

require(BASEPATH.'resultset.php');

class RowNotFoundException extends Exception {}

class FW4_Db {

	protected static $instance = NULL;
	
	public static $query_log = array();
	
    public static function get_instance() {
    	if (self::$instance === NULL) {
    		self::$instance = new PDO('mysql:host='.Config::database_server().';dbname='.Config::database(),Config::database_username(),Config::database_password(),array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8, character_set_results = 'utf8', character_set_client = 'utf8', character_set_connection = 'utf8', character_set_database = 'utf8', character_set_server = 'utf8'"));
    		self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    		self::$instance->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, 1);
    	}
    	return self::$instance;
    }
    
}

class Query {

	protected $select = array();
	protected $alias = false;
	protected $from = false;
	
	protected $where = array();
	protected $order_by = array();
	protected $group_by = array();
	
	protected $joins = array();
	
	protected $children = array();
	protected $relations = array();
	protected $fetchable_fields = array();
	
	protected $objectname_prefix = '';
	
	protected $page = 1;
	protected $limit = false;
	protected $db;
	
	protected $structure = false;
	protected $translate = true;
	protected $save_version = true;
	
	protected $model_name = 'Model';
	protected $use_index = array();
	
	public $debug = false;
	
	public function Query() {
		$this->db = FW4_Db::get_instance();
	}
	
	public function get($objectname = false) {
	    if ($objectname && !$this->structure && !($this->structure = FW4_Structure::get_object_structure($this->objectname_prefix.$objectname))) return false;
		
	    if (!count($this->order_by)) {
	        if (isset($this->structure['order'])) {
	        	$this->order_by(strval($this->structure['order']));
	        } else if (isset($this->structure['sortable']) && $this->structure['sortable']) {
	        	$this->order_by('_sort_order, id DESC');
	        }   
	    }
	    	    
	    $translate = array();
	    
	    if ($this->structure) {
	    
	    	// A page always needs a record
	    	if ($this->structure->getName() == 'page' && !count($this->where)) $force_entry = true;
	    	else $force_entry = false;
	    	
		    $types = FW4_Type_Manager::get_instance();
		    
		    foreach ($this->structure->children() as $child) {
		    	if ($child->getName() == 'object' && !isset($this->children[strval($child['name'])])) {
		    		$this->children[strval($child['name'])] = true;
		    	} else if ($this->translate && isset($child['translatable']) && $child['translatable'] && $child['translatable'] != 'false') {
		    		if ($child->getName() == 'slug' && !isset($child['name'])) $child['name'] = 'slug';
		    		$translate[strval($child['name'])] = strval($child['name']).'_'.language();
		    	} else if ($child->getName() == 'dbrelation' && !isset($this->relations[strval($child['name'])])) {
			    	$this->relations[strval($child['name'])] = current($child->attributes());
		    	} else if (count($this->select) == 0 && isset($child['type_name']) && $type_obj = $types->get_type(strval($child['type_name']))) {
			    	if (method_exists($type_obj,'on_fetch')) {
		    			$this->fetchable_fields[] = array(
			    			'type' => $type_obj,
			    			'field' => new SimpleXMLElement($child->asXML())
		    			);
		    		}
	    		} else if ($child->getName() == 'recursive') {
		    		if (count($this->where) == 0) $this->where('parent_id IS NULL');
		    		$this->relations[strval($child['name'])] = array(
				    	'local_key' => 'id',
				    	'foreign_key' => 'parent_id',
				    	'source' => strval($this->structure['stack'])
				    );
	    		}
		    }
		    
		    if (isset($this->structure['recursive'])) {
			    $this->relations[strval($this->structure['name'])] = array(
			    	'local_key' => 'id',
			    	'foreign_key' => 'parent_id',
			    	'source' => strval($this->structure['stack'])
			    );
			    if (isset($this->structure['order'])) $this->relations[strval($this->structure['name'])]['order'] = strval($this->structure['order']);
		    }
		    
		}
		
		if (isset($this->structure['model']) && !count($this->select) && (class_exists(ucfirst($this->structure['model'])) || file_exists(CONTENTPATH.Router::get_content_prefix().strtolower($this->structure['contentname']).'/models/'.strtolower($this->structure['model']).".php"))) {
			if (!class_exists(ucfirst($this->structure['model']))) include_once(CONTENTPATH.Router::get_content_prefix().strtolower($this->structure['contentname']).'/models/'.strtolower($this->structure['model']).".php");
			if (class_exists(ucfirst($this->structure['model']))) $this->model_name = ucfirst($this->structure['model']);
		} else if ($this->model_name != 'Model') {
			if (!class_exists($this->model_name)) include_once(CONTENTPATH.Router::get_content_prefix().strtolower($this->structure['contentname']).'/models/'.strtolower($this->model_name).".php");
		}
	    
	    $sql = $this->get_sql();
	    if ($this->debug) var_dump($sql);
	    $start = microtime(true);
	    $result = new Resultset($this->db->query($sql,PDO::FETCH_CLASS,$this->model_name,array('translate'=>$translate)),($this->structure?strval($this->structure['stack']):$this->from), ($this->structure?strval($this->structure['name']):$objectname), !isset($this->structure['child']));
	    $time = microtime(true) - $start;
	    FW4_Db::$query_log[$sql] = round($time * 1000, 3);
	    $result->_children($this->children);
	    $result->_relations($this->relations);
	    $result->_fetchable_fields($this->fetchable_fields);
	    
	    if ($this->structure && $force_entry && $result->rowCount() == 0) {
	    	if ($this->structure['parent_type'] == 'site') {
	    		$site = current_site();
	    		$id = insert($objectname,array('site_id'=>$site['id']));
	    	} else $id = insert($objectname,array());
	        return $this->get($objectname);
	    }
	    
	    return $result;
	}
	
	public function get_row($objectname = false) {
		return $this->limit(1)->get($objectname)->next();
	}
	
	public function require_row($objectname = false) {
		$result = $this->get_row($objectname);
		if (!$result) throw new RowNotFoundException();
		return $result;
	}
	
	public function get_random_row($objectname = false) {
		return $this->order_by('RAND()')->get_row($objectname);
	}
	
	public function get_random($objectname = false) {
		return $this->order_by('RAND()')->get($objectname);
	}
	
	public function count_rows($objectname = false) {
		if ($objectname && !$this->structure && !($this->structure = FW4_Structure::get_object_structure($this->objectname_prefix.$objectname))) return false;
	    
	    $start = microtime(true);
	    $sql = 'SELECT COUNT(*) AS count FROM ('.$this->get_sql().') as c';
	    $count = $this->db->query($sql)->fetchColumn();
	    $time = microtime(true) - $start;
	    FW4_Db::$query_log[$sql] = round($time * 1000, 3);
	    return $count;
	}
	
	private function get_sql() {
		
		if (!$this->alias) $this->alias = strval($this->structure['name']);
			
	    if (count($this->select)) $sql = 'SELECT `'.($this->from?$this->from:$this->alias).'`.id,'.implode(',',$this->select).' ';
	    else if ($this->from) $sql = 'SELECT `'.$this->from.'`.* ';
	    else $sql = 'SELECT `'.$this->alias.'`.* ';
	    
	    if ($this->from) $sql .= 'FROM `'.$this->from.'`';
	    else $sql .= 'FROM `'.strval($this->structure['path']).'` AS `'.$this->alias.'`';
	    
	    foreach ($this->joins as $key => $join) {
	    	if ($structure = FW4_Structure::get_object_structure($join['objectname'])) {
	    		$this->joins[$key]['structure'] = $structure;
	    	}
	    }
		
		if (count($this->where)) {
			$where_str = implode(' AND ', $this->where);
			
			if ($this->structure) {
				foreach ($this->structure->children() as $child) {
					if ($child->getName() == 'object') {
						$where_str = preg_replace('/(?<![\w\.])('.preg_quote(strval($child['name'])).')(?![\w\.])/s', $child['name'].'.id', $where_str,-1,$replaced);
						if ($replaced) {
							
						}
					}
			    	if ($this->translate && isset($child['translatable']) && $child['translatable'] && $child['translatable'] != 'false') {
			    		if (!isset($child['name'])) $child['name'] = $child->getName();
			    		$where_str = preg_replace('/(^|[^a-z0-9\-\_])'.preg_quote(strval($child['name'])).'($|[^a-z0-9\-\_])/s', '$1'.$child['name'].'_'.language().'$2', $where_str);
			    	}
			    }
			    
			}
		}
		
		foreach ($this->joins as $join) {
			if ($join['direction']) $sql .= ' '.$direction;
			$sql .=' JOIN `'.$join['structure']['path'].'` as '.$join['structure']['name'].' ON '.$join['on'];
		}
		
		if (count($this->use_index)) $sql .= ' FORCE INDEX('.addslashes(implode(',',$this->use_index)).')';
		
		if (count($this->where)) $sql .= ' WHERE '.$where_str;
		
		if (count($this->group_by)) $sql .= ' GROUP BY '.implode(',', $this->group_by);
		if (count($this->order_by)) {
			if ($this->structure) {
				foreach ($this->order_by as &$order_field) {
					$parts = explode(' ',$order_field);
					if ($translatable_field = $this->structure->xpath('*[@name="'.addslashes($parts[0]).'"][@translatable]')) {
						// This field is translatable, sort by current language
						$parts[0] = $parts[0].'_'.language();
						$order_field = implode(' ',$parts);
					}
				}
			}
			$sql .= ' ORDER BY '.implode(',', $this->order_by);
		}
		
		if ($this->limit) $sql .= ' LIMIT '.intval($this->limit*($this->page-1)).','.$this->limit;
		return $sql;
	}
	
	public function update($objectname,$data) {
	
		if (!$this->structure) $this->structure = FW4_Structure::get_object_structure($this->objectname_prefix.$objectname);
		
		if ($this->structure) {
			
			$language_field = false;
	    	$language = language();
			
			// Translate data
			foreach ($this->structure->children() as $child) {
			    if (in_array($child->getName(),array('string','text')) && isset($child['translatable']) && $child['translatable'] && $child['translatable'] != 'false') {
					if (isset($data[strval($child['name'])])) {
						$data[strval($child['name']).'_'.language()] = $data[strval($child['name'])];
						unset($data[strval($child['name'])]);
					}
				} else if ($child->getName() == 'slug') {
					// Setup slugs
					if (isset($child['readonly'])) continue;
					if (!isset($child['name'])) $child['name'] = 'slug';
					if (!isset($data[strval($child['name'])])) {
						$slugdata = array();
						if (!isset($child['format']) && isset($child['source'])) $child['format'] = '['.$child['source'].']';
						preg_match_all('/\[([a-z0-9\_]+)\]/is',strval($child['format']),$matches,PREG_SET_ORDER);
						$name = strval($child['name']);
						$translatable = $invalid = false;
						$slug_fields = array();
				    	foreach ($matches as $match) {
				    		$source = false;
				    		foreach ($this->structure->children() as $subchild) {
								if (strval($subchild['name']) == $match[1]) $source = $subchild;
							}
							if ($source) {
								$slug_fields[strval($source['name'])] = $source;
								if (isset($source['translatable']) && $source['translatable']) $translatable = true;
							} else {
								$slug_fields[$match[1]] = false;
							}
				    	}
				    	if ($translatable) {
					    	foreach (languages() as $code => $lang) {
								$slugdata[$name.'_'.$code] = strval($child['format']);
							}
				    	} else $slugdata[$name] = strval($child['format']);
				    	foreach ($slug_fields as $slug_name => $slug_field) {
					    	if ($translatable) {
						    	foreach (languages() as $code => $lang) {
						    		$slugnamecode = $slug_name.'_'.$code;
						    		$namecode = $name.'_'.$code;
						    		if (isset($data[$slugnamecode])) {
							    		$slugdata[$name.'_'.$code] = str_ireplace('['.$slug_name.']',$data[$slugnamecode],$slugdata[$namecode]);
						    		} else {
							    		unset($slugdata[$name.'_'.$code]);
							    		$invalid = true;
						    		}
						    	}
					    	} else {
					    		if (isset($data[$slug_name])) $slugdata[$name] = str_ireplace('['.$slug_name.']',$data[$slug_name],$slugdata[$name]);
					    		else {
						    		unset($slugdata[$name]);
						    		$invalid = true;
						    	}
					    	}
				    	}
				    	if ($invalid) continue;
				    	if ($translatable) {
					    	foreach (languages() as $code => $lang) {
					    		$namecode = $name.'_'.$code;
								$slugdata[$namecode] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', remove_accents($slugdata[$name.'_'.$code])) )));
								if (!isset($data[$namecode])) $data[$namecode] = $slugdata[$namecode];
							}
							unset($data[$name]);
				    	} else {
				    		$slugdata[$name] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', str_replace('-',' ',remove_accents($slugdata[$name]))) )));
				    		if (!isset($data[$name])) $data[$name] = $slugdata[$name];
				    	}
					}
				} else if ($child->getName() == 'language') {
					$language_field = $child;
					$language = 0;
				}
		    }
		}
		
		$sql_lines = array();
		foreach ($data as $field => $value) {
			if (is_double($value)) $value = number_format($value,10,'.','');
			else if (is_float($value)) $value = number_format($value,2,'.','');
			else if (is_bool($value)) $value = $value?1:0;
			
			if (is_null($value)) $sql_lines[] = '`'.$field.'` = NULL';
			else $sql_lines[] = '`'.$field.'` = '.$this->db->quote($value);
		}
		if (!count($sql_lines)) {
			if ($this->debug) var_dump("No data");
			return 0;
		}
		
		if ($this->structure) {
			$this->db->query('SET @uids := null;');
			$sql = 'UPDATE `'.$this->structure['path'].'` SET '.implode(',',$sql_lines);
		} else {
			$sql = 'UPDATE `'.$objectname.'` SET '.implode(',',$sql_lines);
		}
		
		// Do we need to update the global search index?
		if ($this->structure) {
	    	
		    $types = FW4_Type_Manager::get_instance();
		    
			$search_index = array();
			foreach ($this->structure->children() as $type => $field) {
				if (isset($field['searchable'])) {
					if (isset($field['type_name']) && $type_obj = $types->get_type(strval($field['type_name']))) {
						if (method_exists($type_obj,'get_update_search_index')) {
				    		$fieldname = strval($field['searchable']);
				    		if (isset($field['translatable'])) {
			    				foreach (languages() as $key => $lang) {
			    					if (!isset($search_index[$key])) $search_index[$key] = array();
			    					$search_index[$key][$fieldname] = $type_obj->get_update_search_index($field,$data,$this->structure,$lang);
			    				}
			    			} else {
			    				if (!isset($search_index[$language])) $search_index[$language] = array();
			    				$search_index[$language][$fieldname] = $type_obj->get_update_search_index($field,$data,$this->structure,$language);
			    			}
			    		}
					} else {
						$fieldname = strval($field['searchable']);
						if (isset($field['translatable'])) {
							foreach (languages() as $key => $lang) {
								if (!isset($search_index[$key])) $search_index[$key] = array();
								if (isset($data[strval($field['name']).'_'.$key])) $search_index[$key][$fieldname] = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $data[strval($field['name']).'_'.$key]));
							}
						} else {
							if (!isset($search_index[$language])) $search_index[$language] = array();
							if (isset($data[strval($field['name'])])) $search_index[$language][$fieldname] = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $data[strval($field['name'])]));
						}
					}
				}
			}
			if (count($search_index)) $this->where[] = '( SELECT @uids := CONCAT_WS(\',\', @uids, id) )';
		}
		
		if (count($this->where)) $sql .= ' WHERE '.implode(' AND ', $this->where);
		if ($this->limit) $sql .= ' LIMIT '.intval($this->limit*($this->page-1)).','.$this->limit;
		
		$result = $this->db->query($sql);
		
		if ($this->debug) var_dump($sql);
		
		// Update the global search index
		if ($this->structure && count($search_index)) {
			$q_updated_ids = $this->db->query('SELECT TRIM(LEADING \',\' FROM @uids);')->fetchAll();
			$updated_ids = reset($q_updated_ids);
			$updated_ids = explode(',',reset($updated_ids));
		    foreach ($search_index as $language => &$searchdata) {
		    	if (count($searchdata) && count($updated_ids)) {
		    		where('object_id IN %$',$updated_ids)->where('_language',$language)->where('object_name',strval($this->structure['stack']))->update('_search_index',$searchdata);
		    	}
		    }
		}
		
		return $result;
	}
	
	public function insert($objectname,$data) {
		
		if (!$this->structure) $this->structure = FW4_Structure::get_object_structure($this->objectname_prefix.$objectname);
		
		if ($this->structure) {
			
			$language_field = false;
	    	$language = language();
	    	
	    	$is_recursive = false;
	    	
			// Translate data
			foreach ($this->structure->children() as $child) {
			    if (in_array($child->getName(),array('string','text')) && isset($child['translatable']) && $child['translatable'] && $child['translatable'] != 'false') {
					if (isset($data[strval($child['name'])])) {
						$data[strval($child['name']).'_'.language()] = $data[strval($child['name'])];
						unset($data[strval($child['name'])]);
					}
				} else if ($child->getName() == 'slug') {
					// Setup slugs
					if (!isset($child['name'])) $child['name'] = 'slug';
					if (!isset($data[strval($child['name'])])) {
						$slugdata = array();
						if (!isset($child['format']) && isset($child['source'])) $child['format'] = '['.$child['source'].']';
						preg_match_all('/\[([a-z0-9\_]+)\]/is',strval($child['format']),$matches,PREG_SET_ORDER);
						$name = strval($child['name']);
						$translatable = $invalid = false;
						$slug_fields = array();
				    	foreach ($matches as $match) {
				    		$source = false;
				    		foreach ($this->structure->children() as $subchild) {
								if (strval($subchild['name']) == $match[1]) $source = $subchild;
							}
							if ($source) {
								$slug_fields[strval($source['name'])] = $source;
								if (isset($source['translatable']) && $source['translatable']) $translatable = true;
							} else {
								$slug_fields[$match[1]] = false;
							}
				    	}
				    	if ($translatable) {
					    	foreach (languages() as $code => $lang) {
								$slugdata[$name.'_'.$code] = strval($child['format']);
							}
				    	} else $slugdata[$name] = strval($child['format']);
				    	foreach ($slug_fields as $slug_name => $slug_field) {
					    	if ($translatable) {
						    	foreach (languages() as $code => $lang) {
						    		$slugnamecode = $slug_name.'_'.$code;
						    		$namecode = $name.'_'.$code;
						    		if (isset($data[$slugnamecode])) $slugdata[$name.'_'.$code] = str_ireplace('['.$slug_name.']',$data[$slugnamecode],$slugdata[$namecode]);
						    	}
					    	} else {
					    		if (isset($data[$slug_name])) $slugdata[$name] = str_ireplace('['.$slug_name.']',$data[$slug_name],$slugdata[$name]);
					    		else {
						    		unset($slugdata[$name]);
						    		$invalid = true;
						    	}
					    	}
				    	}
				    	if ($invalid) continue;
				    	if ($translatable) {
					    	foreach (languages() as $code => $lang) {
					    		$namecode = $name.'_'.$code;
								$slugdata[$namecode] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', remove_accents($slugdata[$name.'_'.$code])) )));
								if (!isset($data[$namecode])) $data[$namecode] = $slugdata[$namecode];
							}
							unset($data[$name]);
				    	} else {
				    		$slugdata[$name] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', str_replace('-',' ',remove_accents($slugdata[$name]))) )));
				    		if (!isset($data[$name])) $data[$name] = $slugdata[$name];
				    	}
					}
				} else if ($child->getName() == 'language') {
					$language_field = $child;
					$language = 0;
				} else if ($child->getName() == 'recursive') {
					if (isset($data['parent_id']) && $data['parent_id']) $is_recursive = true;
				}
		    }
		    
		    // Give pages a default parent
		    if (!$is_recursive && $this->structure['parent_name'] && $this->structure['parent_type'] == 'page' && $this->structure['name'] != '_versions' && !isset($data[$this->structure['parent_name'].'_id']) && !(isset($this->structure['child']) && $this->structure['child'] == 'false') ) {
			    $data[$this->structure['parent_name'].'_id'] = 1;
			}
		}
		
		$sql_lines = array();
		foreach ($data as $field => $value){
			if (is_null($value)) continue;
			if (is_object($value)) continue;
			if (is_float($value)) $value = number_format($value,2,'.','');
			if (is_bool($value)) $value = $value?1:0;
			$sql_lines[] = '`'.$field.'` = '.$this->db->quote($value);
		}
		
		if ($this->structure) $sql = 'INSERT INTO `'.$this->structure['path'].'`';
		else $sql = 'INSERT INTO `'.$objectname.'`';
		
		if (count($sql_lines)) $sql .= ' SET '.implode(',',$sql_lines);
		else $sql .= ' (`id`) VALUES (NULL);';
		
		$this->db->query($sql);
		
		$newid = $this->db->lastInsertId();
		
		if ($this->structure) {
			
			$data['id'] = $newid;
		
			// Save a version
			if (isset($this->structure['archived']) && $this->save_version && $this->structure['name'] != '_versions' && count(array_filter($data)) > 1)  {
				$versiondata = $data;
				$version_id = insert($this->structure['stack'].'>_versions',$versiondata);
			}
			
			// Add record to search index if needed
			$types = FW4_Type_Manager::get_instance();
			$search_index = array();
			foreach ($this->structure->children() as $type => $field) {
				if (isset($field['searchable'])) {
					if (isset($field['type_name']) && $type_obj = $types->get_type(strval($field['type_name']))) {
						if (method_exists($type_obj,'get_insert_search_index')) {
				    		$fieldname = strval($field['searchable']);
				    		if (isset($field['translatable'])) {
			    				foreach (languages() as $key => $lang) {
			    					if (!isset($search_index[$key])) $search_index[$key] = array();
			    					$search_index[$key][$fieldname] = $type_obj->get_insert_search_index($field,$data,$this->structure,$lang);
			    				}
			    			} else {
			    				if (!isset($search_index[$language])) $search_index[$language] = array();
			    				$search_index[$language][$fieldname] = $type_obj->get_insert_search_index($field,$data,$this->structure,$language);
			    			}
			    		}
					} else {
						$fieldname = strval($field['searchable']);
						if (isset($field['translatable'])) {
							foreach (languages() as $key => $lang) {
								if (!isset($search_index[$key])) $search_index[$key] = array();
								if (isset($data[strval($field['name']).'_'.$key])) $search_index[$key][$fieldname] = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $data[strval($field['name']).'_'.$key]));
							}
						} else {
							if (!isset($search_index[language()])) $search_index[language()] = array();
							if (isset($data[strval($field['name'])])) $search_index[language()][$fieldname] = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $data[strval($field['name'])]));
						}
					}
				}
			}
			foreach ($search_index as $language => &$searchdata) {
				if (count($searchdata) && $this->structure['name'] != '_versions') {
					$searchdata['object_id'] = $newid;
					$searchdata['object_name'] = strval($this->structure['stack']);
					$searchdata['_language'] = $language;
					
					if (isset($this->structure['archived']) && $this->save_version && $this->structure['name'] != '_versions') $searchdata['_version_id'] = $version_id;
					insert('_search_index',$searchdata);
				}
			}
			
		}
		
		return $newid;
	}
	
	public function pick($field) {
		$this->select[] = $field;
		return $this;
	}
	
	public function delete($objectname) {
		if (!$this->structure = FW4_Structure::get_object_structure($this->objectname_prefix.$objectname)) return false;
		
		return self::_delete($this->structure,$this->structure['stack'],$this->get($this->structure['stack']));
	}
	private function _delete($structure,$stack,$data) {
		
		$ids = $data->ids();
		
		if (count($ids)) {
		
	    	if (isset($structure['archived']) && $structure['name'] != '_versions') {
	    		where('id IN %$',$ids)->delete($structure['stack'].'>_versions');
	    	}
			
			// Remove from search index
			where('object_id IN %$ AND object_name = %s',$ids,$stack)->delete('_search_index');
		
			$types = FW4_Type_Manager::get_instance();
	    	
	    	foreach ($structure->children() as $type => $child) {
	    		if ($type == 'object' && strval($child['name']) != '_versions' && !isset($child['child'])) {
	    			$ids = array();
	    			$childname = strval($child['name']);
	    			foreach ($data as $row) $ids[] = $row->id;
	    			if (count($ids)) {
		    			$childstructure = FW4_Structure::get_object_structure($stack.'>'.$childname);
		    			$childdata = where($structure['name'].'_id IN %$',$ids)->get($childstructure['path']);
			    		$this->_delete($childstructure,$childstructure['path'],$childdata);
			    	}
	    		} else if ($type == 'recursive' && strval($child['name']) != '_versions') {
	    			$ids = array();
	    			foreach ($data as $row) $ids[] = $row->id;
	    			if (count($ids)) {
		    			$childdata = where('parent_id IN %$',$ids)->get($structure['path']);
			    		$this->_delete($structure,$structure['path'],$childdata);
			    	}
	    		} else if (isset($child['type_name']) && $type_obj = $types->get_type(strval($child['type_name']))) {
	    			$type_obj->deleted($child,$data);
	    		}
	    	}
	    	
	    	if (isset($structure['type_name']) && $type_obj = $types->get_type(strval($structure['type_name']))) {
				$type_obj->deleted($structure,$data);
			}
			
			foreach ($ids as $id) {
				if ($structure['name'] == '_versions') $this->db->query('DELETE FROM `'.$structure['path'].'` WHERE version_id = '.$id);
				else $this->db->query('DELETE FROM `'.$structure['path'].'` WHERE id = '.$id);
			}
			
	    	return true;
		}
		
		return false;
		
	}
	
	public function from($table) {
		$this->from = $table;
		
		return $this;
	}
	
	public function where($condition) {
	
		$args = func_get_args();
		$query = str_replace('%$','%s',array_shift($args));
		$query = str_replace('*','%%',$query);
		$args = array_map(array($this,'escape'),$args);
		
		$this->where[] = vsprintf('('.$query.')',$args);
	
		return $this;
	}
	
	public function _array_where($condition,$parameters) {
	
		$parameters = array_map(array($this,'escape'),$parameters);
		$condition = str_replace('%$','%s',$condition);
		
		$this->where[] = vsprintf('('.$condition.')',$parameters);
	
		return $this;
	}
	
	public function or_where($condition) {
	
		$args = func_get_args();
		$query = array_shift($args);
		if (is_array(reset($args))) $args = reset($args);
		$args = array_map(array($this->db,'quote'),$args);
		
		$this->where[] = array_shift($this->where).' OR '.vsprintf('('.$query.')',$args);
	
		return $this;
	}
	
	public function join($objectname,$on) {
		$args = func_get_args();
		$objectname = array_shift($args);
		$on = array_shift($args);
		return $this->_join($objectname,$on,$args,'');
	}
	public function left_join($objectname,$on) {
		$args = func_get_args();
		$objectname = array_shift($args);
		$on = array_shift($args);
		return $this->_join($objectname,$on,$args,'LEFT');
	}
	public function right_join($objectname,$on) {
		$args = func_get_args();
		$objectname = array_shift($args);
		$on = array_shift($args);
		return $this->_join($objectname,$on,$args,'RIGHT');
	}
	public function _join($objectname,$on,$parameters,$direction) {
		$this->joins[] = array(
			'objectname' => $objectname,
			'on' => $on,
			'parameters' => $parameters,
			'direction' => $direction
		);
		return $this;
	}
	
	public function order_by($query) {
		$args = array_slice(func_get_args(),1);
		return $this->_array_order_by($query,$args);
	}
	
	public function _array_order_by($query,$parameters) {
		
		$query = str_replace('%$','%s',$query);
		$query = str_replace('*','%%',$query);
		$parameters = array_map(array($this,'escape'),$parameters);
		
		$this->order_by[] = vsprintf($query,$parameters);
	
		return $this;
	}
	
	public function limit($limit=NULL) {
		if ($limit === NULL) {
			return $this->limit;
		} else {
			$this->limit = intval($limit);
			return $this;
		}
	}
	
	public function page($page=NULL) {
		if ($page === NULL) {
			return $this->page;
		} else {
			$this->page = intval($page);
			return $this;
		}
	}
	
	public function group_by($group_by) {
		$this->group_by[] = $group_by;
		return $this;
	}
	
	public function including($child,$query=false) {
		if (strstr($child,'/')) {
			$parts = explode('/',$child,2);
			$this->children[$parts[0]] = including($parts[1],$query);
		} else {
    		if (is_bool($query)) $this->children[$child] = true;
    		else $this->children[$child] = $query;
    	}
		return $this;
	}
	
	public function search($query,$fields=array()) {
		
		if (!count($fields)) {
			$searchable_fields = array();
			foreach (FW4_Structure::get_searchable_fields() as $field) {
				if ($field->getName() == 'bool') continue;
				$fields[strval($field['searchable'])] = strval($field['searchable']);
			}
		}
		
	    if (!count($this->order_by)) {
	        $this->order_by[] = 'MATCH(`'.implode('`,`',$fields).'`) AGAINST ("'.$this->escape($query).'") DESC';
	    }
	    
	    $this->where[] = '_language = "'.language().'"';
	    $this->where[] = 'MATCH(`'.implode('`,`',$fields).'`) AGAINST ("'.$this->escape($query).'") > 0';
			
	    $sql = 'SELECT *, MATCH(`'.implode('`,`',$fields).'`) AGAINST ("'.$this->escape($query).'") as score FROM `_search_index` WHERE '.implode(' AND ', $this->where);
		
		if (count($this->group_by)) $sql .= ' GROUP BY '.implode(',', $this->group_by);
		if (count($this->order_by)) $sql .= ' ORDER BY '.implode(',', $this->order_by);
		
		if ($this->limit) $sql .= ' LIMIT '.intval($this->limit*($this->page-1)).','.$this->limit;
	    
	    $result = new Resultset($this->db->query($sql,PDO::FETCH_CLASS,'Model',array('translate'=>array())),'_search_index','_search_index');
	    $result->_children($this->children);
	    $result->_relations($this->relations);
	    $result->_fetchable_fields($this->fetchable_fields);
	    
	    return $result;
	}
	
	public function search_count($query,$fields=array()) {
		
		if (!count($fields)) {
			$searchable_fields = array();
			foreach (FW4_Structure::get_searchable_fields() as $field) {
				if ($field->getName() == 'bool') continue;
				$fields[strval($field['searchable'])] = strval($field['searchable']);
			}
		}
	    
	    $this->where[] = '_language = "'.language().'"';
	    $this->where[] = 'MATCH(`'.implode('`,`',$fields).'`) AGAINST ("'.$this->escape($query).'")';
			
	    $sql = 'SELECT count(*) as count FROM `_search_index` WHERE '.implode(' AND ', $this->where);
		
		if (count($this->group_by)) $sql .= ' GROUP BY '.implode(',', $this->group_by);
	    
	    return $this->db->query($sql)->fetchColumn();
	}
	
	public function model($model_name) {
		$this->model_name = $model_name;
		return $this;
	}
	
	public function set_objectname_prefix($prefix) { $this->objectname_prefix = $prefix; }
	
	public function escape($value) {
		if (is_numeric($value)) return $value;
		else if (is_null($value)) return 'NULL';
		else if (is_bool($value)) return $value?1:0;
		else if (is_array($value)) {
			if (empty($value)) return '(-1)';
			else return '('.implode(',',array_map(array($this,'escape'),$value)).')';
		} else return $this->db->quote($value);
	}
	
	public function translate($translate) { $this->translate = $translate; return $this; }
	
	public function use_index($index) {
		$this->use_index[] = $index;
		return $this;
	}
	
	public function has_conditions() {
		return (count($this->where) > 0);
	}
	
}

function pick($field) {
	$query = new Query();
	return $query->pick($field);
}

function from($table) {
	$query = new Query();
	return $query->from($table);
}
function where($condition) {
	$args = array_slice(func_get_args(),1);
	$query = new Query();
	return $query->_array_where($condition,$args);
}
function including($child,$including_query=false) {
	$query = new Query();
	return $query->including($child,$including_query);
}
function limit($limit) {
	$query = new Query();
	return $query->limit($limit);
}
function page($page) {
	$query = new Query();
	return $query->page($page);
}
function group_by($group_by) {
	$query = new Query();
	return $query->group_by($group_by);
}
function order_by($q) {
	$args = array_slice(func_get_args(),1);
	$query = new Query();
	return $query->_array_order_by($q,$args);
}
function count_rows($objectname = false) {
	$query = new Query();
	return $query->count_rows($objectname);
}
function get($objectname = false) {
	$query = new Query();
	return $query->get($objectname);
}
function get_row($objectname = false) {
	$query = new Query();
	return $query->get_row($objectname);
}
function get_random_row($objectname = false) {
	$query = new Query();
	return $query->get_random_row($objectname);
}
function get_random($objectname = false) {
	$query = new Query();
	return $query->get_random($objectname);
}
function update($objectname,$data) {
	$query = new Query();
	return $query->update($objectname,$data);
}
function insert($objectname,$data) {
	$query = new Query();
	return $query->insert($objectname,$data);
}
function search($query,$fields=array()) {
	$q = new Query();
	return $q->search($query,$fields);
}
function search_count($query,$fields=array()) {
	$q = new Query();
	return $q->search_count($query,$fields);
}

function fwjoin($objectname,$on) {
	$args = func_get_args();
	$objectname = array_shift($args);
	$on = array_shift($args);
	$query = new Query();
	return $query->_join($objectname,$on,$args,'');
}
function left_join($objectname,$on) {
	$args = func_get_args();
	$objectname = array_shift($args);
	$on = array_shift($args);
	$query = new Query();
	return $query->_join($objectname,$on,$args,'LEFT');
}
function right_join($objectname,$on) {
	$args = func_get_args();
	$objectname = array_shift($args);
	$on = array_shift($args);
	$query = new Query();
	return $query->_join($objectname,$on,$args,'RIGHT');
}
<?php

/* ----------------------
FW4 FRAMEWORK - STRUCTURE
-------------------------

The structure class is responsible for parsing the structure XML and constructing/maintaining a database. */

class FW4_Structure {

	private static $structure_xml = false;
	private static $structure_xml_expanded = false;
	
	private static $searchable_fields = array();
	
	private static $structure_cache = array();
	private static $structure_cache_friendly = array();
	
	public static function check_structure($xml="",$force_update=false) {
		$filemtime = 0;
		
		set_time_limit(0);
		
		if (!$xml) {
		    
			$xml = '<structure>';
			$handle = opendir(CONTENTPATH);
			$opts = array( 
				'http' => array( 
				    'method'=>"GET", 
				    'header'=>"Content-Type: text/html; charset=utf-8" 
				) 
			); 
			
			$context = stream_context_create($opts); 
		    while (false !== ($file = readdir($handle))) {
		        if (is_dir(CONTENTPATH.$file) && file_exists(CONTENTPATH.$file.'/structure.xml')) {
		        	$current_filemtime = filemtime(CONTENTPATH.$file.'/structure.xml');
		        	
		        	try {
			        	$structure = new SimpleXMLElement('<structure>'.file_get_contents(CONTENTPATH.$file.'/structure.xml',false,$context).'</structure>');
			        } catch (Exception $exception) {
			        	die('Fout bij lezen van xml voor '.$file);
			        }
		        	
		        	foreach ($structure->xpath('//object|//page') as $child) {
			        	$child->addAttribute('contentname',$file);
		        	}
		        	foreach ($structure->children() as $child) {
			        	$xml .= str_replace('&','&amp;',$child->asXML());
		        	}
		        	if ($current_filemtime > $filemtime) $filemtime = $current_filemtime;
		        }
		    }
		    closedir($handle);
		    $xml .= '</structure>';
		    $xml = self::expand_protocols(new SimpleXMLElement($xml))->asXML();
		    
		    self::$structure_xml = new SimpleXMLElement($xml);
		    
		    if (!$force_update) {
		    	$site = current_site();
		    	$last_update = $site->table_check_date;
		    } else $last_update = 0;
		    
		    if ($filemtime > $last_update || $force_update) {
		    	$structure = new SimpleXMLElement($xml);
				if (!count($structure->xpath("//site"))) $structure->addChild('site');
				
				$expanded_xml = self::expand_node($structure);
				
				foreach ($expanded_xml as $type => $data) {
					if ($type == 'page' || $type == 'object' || $type == 'site') self::process_searchable_fields($data);
				}
				
				if (count(self::$searchable_fields)) {
					$searchable = $expanded_xml->addChild('object');
					$searchable->addAttribute('name','_search_index');
					$searchableObject = $searchable->addChild('string');
					$searchableObject->addAttribute('name','object_name');
					$searchableObject->addAttribute('index','index');
					$searchableLanguage = $searchable->addChild('string');
					$searchableLanguage->addAttribute('name','_language');
					$searchableLanguage->addAttribute('index','index');
					$searchableLanguage->addAttribute('length','2');
					$searchableId = $searchable->addChild('number');
					$searchableId->addAttribute('name','object_id');
					$searchableId->addAttribute('index','index');
					$searchableVersion = $searchable->addChild('number');
					$searchableVersion->addAttribute('name','_version_id');
					$searchableVersion->addAttribute('index','index');
					foreach (self::$searchable_fields as $searchable_field => $searchable_type) {
						$searchfield = $searchable->addChild($searchable_type);
						$searchfield->addAttribute('name', $searchable_field);
						if ($searchable_type == 'bool') $searchfield->addAttribute('allownull', 'allownull');
						if (in_array($searchable_type,array('text','string'))) $searchfield->addAttribute('index', 'fulltext');
					}
				}
				
				$db = FW4_Db::get_instance();
				
				foreach ($expanded_xml as $type => $data) {
					if ($type == 'page' || $type == 'object' || $type == 'site') self::process_object($data);
				}
				
				update('site',array(
					'table_check_date' => intval($filemtime),
					'structure_xml' => $xml,
					'structure_xml_expanded' => $expanded_xml->asXML()
				));
				
				FW4_Site::reload_site();
				
				//self::rebuild_search_index();
				
				return $xml;
			}
		} else {
			$structure = new SimpleXMLElement($xml);
			
			foreach ($structure as $type => $data) {
				if ($type == 'page' || $type == 'object' || $type == 'site') self::process_object($data);
			}	
		}
	}
	
	public static function get_object_structure($objectname,$db_friendly=true) {
		
		if ($objectname == 'site') return new SimpleXMLElement('<object path="site" name="site" stack="site"><object name="images" path="site>images" stack="site>images" model="image"/><object name="downloads" path="site>downloads" stack="site>downloads"/><object name="videos" path="site>videos" stack="site>videos"><string fieldname="video" videostack="site>videos" type_name="video"/></object>'.Config::site_fields().'</object>');
		else if ($objectname == 'site>images' || $objectname == 'site/images') return new SimpleXMLElement('<object name="images" path="site>images" stack="site>images" model="image"/>');
		else if ($objectname == 'site>downloads' || $objectname == 'site/downloads') return new SimpleXMLElement('<object name="downloads" path="site>downloads" stack="site>downloads"/>');
		else if ($objectname == 'site>videos' || $objectname == 'site/videos') return new SimpleXMLElement('<object name="videos" path="site>videos" stack="site>videos"><string fieldname="video" videostack="site>videos" type_name="video"/></object>');
		
		if (!self::$structure_xml && !$db_friendly) self::$structure_xml = new SimpleXMLElement(self::get_xml());
		else if (!self::$structure_xml_expanded && $db_friendly) self::$structure_xml_expanded = new SimpleXMLElement(self::get_expanded_xml());
		
		$xpath = '';
		$objectname = str_replace('>','/>',$objectname);
		foreach (array_filter(explode('/', $objectname)) as $name) {
			if (substr($name,0,1) == '>') $xpath .= "/*[@name='".substr($name,1)."' and (name()='object' or name()='page')]";
			else $xpath .= "//*[@name='".$name."' and (name()='object' or name()='page')]";
		}
		
		if (substr($xpath,0,2) == '/*') $xpath = '/structure'.$xpath;
		
		if (!$xpath) return false;
		
		if ($db_friendly) {
			if (isset($structure_cache_friendly[$xpath])) return clone $structure_cache_friendly[$xpath];
			$node = self::$structure_xml_expanded->xpath($xpath);
		} else {
			if (isset($structure_cache[$xpath])) return clone $structure_cache[$xpath];
			$node = self::$structure_xml->xpath($xpath);
		}
		
		if (count($node) < 1) return false;
		$node = array_shift($node);
		
		$node['parent_type'] = '';
		$node['parent_name'] = '';
		$node['path'] = '';
		$node['stack'] = '';
		
		$parents = $node->xpath("ancestor::*");
		
		foreach ($parents as $parent) {
			if ($parent->getName() != 'structure') {
				$node['parent_type'] = $parent->getName();
				$node['parent_name'] = strval($parent['name']);
			}
			if ($parent->getName() == 'page' || $parent->getName() == 'object') {
				$node['path'] .= $parent['name'].'>';
				$node['stack'] .= $parent['name'].'>';
			}
		}
		$node['path'] .= $node['name'];
		$node['stack'] .= $node['name'];
		
		if ($db_friendly) {
			$node = clone $node;
			$types = FW4_Type_Manager::get_instance();
			foreach ($node as $type => $child) {
				if ($type_obj = $types->get_type($type)) {
					$xml = new SimpleXMLElement($type_obj->get_structure($child,$node));
					foreach ($xml->children() as $field) self::merge_xml($node,$field);
				}
			}
			$structure_cache_friendly[$xpath] = $node;
		} else {
			$node = clone $node;
			$structure_cache[$xpath] = $node;
		}
		
		return $node;
	}
	
	public static function add_searchable_field($name,$type) {
		$types = FW4_Type_Manager::get_instance();
		switch ($type) {
			case 'number':
				if (!isset(self::$searchable_fields[$name]) || in_array(self::$searchable_fields[$name],array('bool'))) self::$searchable_fields[$name] = $type;
				break;
			case 'email':
				if (!isset(self::$searchable_fields[$name]) || in_array(self::$searchable_fields[$name],array('number','bool'))) self::$searchable_fields[$name] = 'string';
				break;
			case 'text':
				if (!isset(self::$searchable_fields[$name]) || in_array(self::$searchable_fields[$name],array('number','string','bool'))) self::$searchable_fields[$name] = $type;
				break;
			case 'bool':
				if (!isset(self::$searchable_fields[$name])) self::$searchable_fields[$name] = $type;
				break;
			case 'string':
				if (!isset(self::$searchable_fields[$name]) || in_array(self::$searchable_fields[$name],array('number','bool'))) self::$searchable_fields[$name] = $type;
				break;
			default:
				if ($type_obj = $types->get_type($type)) {
					if (method_exists($type_obj,'get_search_index') && (!isset(self::$searchable_fields[$name]) || in_array(self::$searchable_fields[$name],array('number','bool')))) self::$searchable_fields[$name] = 'string';
				}
				break;
		}
	}
	
	public static function get_pages() {
		if (!self::$structure_xml) self::$structure_xml = new SimpleXMLElement(self::get_xml());
		
		return self::$structure_xml->xpath("//page");
	}
	
	public static function get_searchable_fields() {
		if (!self::$structure_xml) self::$structure_xml = new SimpleXMLElement(self::get_xml());
		return self::$structure_xml->xpath("//*[@searchable]");
	}
	
	private static function process_object($data,$parent_name='',$current_tree='') {
		
		$fields = array();
		
		if ($data['name'] == '_versions') {
			$fields['version_id'] = array('type'=>'int','length'=>10,'index'=>'primary');
			$fields['id'] = array('type'=>'int','length'=>10,'index'=>'index');
			if ($parent_name) $fields[$parent_name.'_id'] = array('type'=>'int','length'=>10,'index'=>'index');
		} else {
			$fields['id'] = array('type'=>'int','length'=>10,'index'=>'primary');
			if ($parent_name && !isset($data['child'])) $fields[$parent_name.'_id'] = array('type'=>'int','length'=>10,'index'=>'index');
		}
		
		if ($data->getName() == 'site') {
			$data = new SimpleXMLElement('<object path="site" name="site" stack="site">'.Config::site_fields().'</object>');
			$fields['name'] = array('type'=>'varchar','length'=>150,'default'=>'');
			$fields['url'] = array('type'=>'varchar','length'=>150,'default'=>'');
			$language_codes = array_keys(languages());
			if ($countries = Config::countries()) $language_codes = array_keys($countries);
			if (count($language_codes) > 1) {
				foreach ($language_codes as $code) {
					$fields['url_'.$code] = array('type'=>'varchar','length'=>150,'default'=>'');
				}
			}
			$fields['table_check_date'] = array('type'=>'int','length'=>10);
			$fields['routing_check_date'] = array('type'=>'int','length'=>10);
			$fields['structure_xml'] = array('type'=>'mediumtext');
			$fields['structure_xml_expanded'] = array('type'=>'mediumtext');
			$fields['routing'] = array('type'=>'text');
			$fields['live'] = array('type'=>'bool','default'=>'0');
			$fields['piwik_id'] = array('type'=>'varchar','length'=>50,'default'=>'');
			$cmsdownloads = $data->addChild('files');
			$cmsdownloads->addAttribute('name', 'downloads');
			$cmsdownloads->addAttribute('sortable', 'true');
			$cmsimages = $data->addChild('images');
			$cmsimages->addAttribute('name', 'images');
			$cmsimages->addAttribute('sortable', 'true');
			$cmsvideos = $data->addChild('object');
			$cmsvideos->addAttribute('name', 'videos');
			$cmsvideos->addAttribute('sortable', 'true');
			$cmsvideo = $cmsvideos->addChild('video');
			$cmsvideo->addAttribute('name', 'video');
		}
		
		$attributes = $data->attributes();
		$children = $data->children();
		
		if (!isset($attributes['name'])) return false;
		
		if (isset($data['sortable']) && $data['sortable']) $fields['_sort_order'] = array('type'=>'int','length'=>10,'index'=>'index');
		
		if (isset($data['recursive']) && $data['recursive']) $fields['parent_id'] = array('type'=>'int','length'=>10,'index'=>'index');
		
		foreach ($children as $key => $child) $fields = self::process_field($key,$child,$fields,$data,$current_tree);
		
		self::modify_table($current_tree.$attributes['name'],$fields,$data);
	}
	
	private static function process_searchable_fields($data) {
		if (in_array($data->getName(),array('object','page'))) {
			foreach ($data->children() as $key => $child) self::process_searchable_fields($child);
		} else if (isset($data['searchable'])) {
			$type = $data->getName();
			if (isset($data['type_name'])) $type = strval($data['type_name']);
			self::add_searchable_field(strval($data['searchable']),$type);
		}
	}
	
	private static function process_field($type,$field,$fields,$parent,$current_tree='') {
		
		$types = FW4_Type_Manager::get_instance();
		
		switch ($type) {
			case 'object':
				if ($field['name'] == '_versions') {
					$current_tree_parts = array_filter(explode('>',$current_tree));
					self::process_object($field,end($current_tree_parts),$current_tree.$parent['name'].'>');
				} else self::process_object($field,$parent['name'],$current_tree.$parent['name'].'>');
				break;
			case 'recursive':
				$fields['parent_id'] = array('type'=>'int','length'=>10,'index'=>'index');
				break;
			case 'page':
				self::process_object($field,'site');
				break;
			case 'string':
			case 'email':
				if (isset($field['translatable']) && $field['translatable'] && $field['translatable'] != false) {
					foreach (languages() as $code => $lang) {
						$fields[strval($field['name']).'_'.$code] = array('type'=>'varchar','length'=>isset($field['length'])?$field['length']:250,'default'=>'');
						if (isset($field['index']) && $field['index'] == 'fulltext') {
							$fields[strval($field['name']).'_'.$code]['index'] = 'fulltext';
							$fields[strval($field['name']).'_'.$code]['key_name'] = '_search_index_'.$code;
						} else if (isset($field['index'])) {
							$fields[strval($field['name']).'_'.$code]['index'] = $field['index'];
						}
					}
				} else {
					$fields[strval($field['name'])] = array('type'=>'varchar','length'=>isset($field['length'])?$field['length']:250,'default'=>'');
					if (isset($field['index']) && $field['index'] == 'fulltext') {
						$languages = array_keys(languages());
						$fields[strval($field['name'])]['index'] = 'fulltext';
						$fields[strval($field['name'])]['key_name'] = '_search_index_'.reset($languages);
					} else if (isset($field['index'])) {
						$fields[strval($field['name'])]['index'] = $field['index'];
					}
				}
				break;
			case 'password':
				$fields[strval($field['name'])] = array('type'=>'varchar','length'=>100,'default'=>'');
				$fields[strval($field['name']).'_attempts'] = array('type'=>'varchar','length'=>150,'default'=>'');
				break;
			case 'bool':
				$fields[strval($field['name'])] = array('type'=>'char','length'=>1);
				if (!isset($field['allownull'])) $fields[strval($field['name'])]['default'] = 0;
				if (isset($field['index'])) $fields[strval($field['name'])]['index'] = 'index';
				else $fields[strval($field['name'])]['allownull'] = true;
				break;
			case 'date':
			case 'number':
				$fields[strval($field['name'])] = array('type'=>'int','length'=>10);
				if (isset($field['index'])) $fields[strval($field['name'])]['index'] = 'index';
				if (isset($field['default'])) $fields[strval($field['name'])]['default'] = $field['default'];
				break;
			case 'float':
				$fields[strval($field['name'])] = array('type'=>'float','length'=>'14,4');
				if (isset($field['index'])) $fields[strval($field['name'])]['index'] = 'index';
				break;
			case 'double':
				$fields[strval($field['name'])] = array('type'=>'double','length'=>'20,10');
				if (isset($field['index'])) $fields[strval($field['name'])]['index'] = 'index';
				break;
			case 'text':
				if (isset($field['translatable']) && $field['translatable'] && $field['translatable'] != 'false') {
					foreach (languages() as $code => $lang) {
						$fields[strval($field['name']).'_'.$code] = array('type'=>'text','default'=>'');
						if (isset($field['index']) && $field['index'] == 'fulltext') {
							$fields[strval($field['name']).'_'.$code]['index'] = 'fulltext';
							$fields[strval($field['name']).'_'.$code]['key_name'] = '_search_index_'.$code;
						}
					}
				} else {
					$fields[strval($field['name'])] = array('type'=>'text','default'=>'');
					if (isset($field['index']) && $field['index'] == 'fulltext') {
						$languages = array_keys(languages());
						$fields[strval($field['name'])]['index'] = 'fulltext';
						$fields[strval($field['name'])]['key_name'] = '_search_index_'.reset($languages);
					}
				}
				break;
			case 'slug':
				if (!isset($field['format']) && isset($field['source'])) $field['format'] = '['.$field['source'].']';
				preg_match_all('/\[([a-z0-9\_]+)\]/is',strval($field['format']),$matches,PREG_SET_ORDER);
				$name = isset($field['name'])?strval($field['name']):'slug';
				$translatable = false;
		    	foreach ($matches as $match) {
		    		$source = false;
		    		foreach ($parent->children() as $child) {
						if (strval($child['name']) == $match[1]) $source = $child;
					}
					if ($source && isset($source['translatable']) && $source['translatable']) $translatable = true;
		    	}
				if ($translatable) {
					foreach (languages() as $code => $lang) {
						$fields[$name.'_'.$code] = array('type'=>'varchar','length'=>150,'default'=>'','index'=>'index');
					}
				} else $fields[$name] = array('type'=>'varchar','length'=>150,'default'=>'','index'=>'index');
				break;
			case 'fieldset':
				foreach ($field->children() as $key => $child) $fields = self::process_field($key,$child,$fields,$parent,$current_tree);
				break;
			case 'index':
				$fields[strval($field['name'])] = array('type'=>'index','index'=>'index','fields'=>strval($field['fields']));
				break;
			default:
				if ($type_obj = $types->get_type($type)) {
					$xml = new SimpleXMLElement($type_obj->get_structure($field,$parent));
					foreach ($xml->children() as $key => $child) $fields = self::process_field($key,$child,$fields,$parent,$current_tree);
				}
				break;
		}
		
		return $fields;
	}
    
    private static function modify_table($tablename,$fields,$structure) {
    
    	if (!is_array($fields) || !count($fields)) return false;
    	
    	$primary_keys = $indexes = $foreign_keys = $searchable_keys = array();
    	
    	$sql_lines = array();
    	
    	foreach ($fields as $name => $field) {
	    	
	    	if ($field['type'] == 'index') {
		    	$indexes[$name] = strval($field['fields']);
		    	continue;
	    	}
	    	
    		$sql = '`'.$name.'` '.$field['type'];
    		
    		if (isset($field['length'])) $sql .= '('.$field['length'].')';
    		
    		if (!isset($field['index'])) $field['index'] = '';
    		
    		if ($field['index']=='primary' || isset($field['default'])) $sql .= ' NOT NULL ';
    		
    		if (isset($field['default'])) $sql .= " DEFAULT '".$field['default']."'";
    		else if (isset($field['allownull'])) $sql .= " DEFAULT NULL";
    		
    		if ($field['index']=='primary') {
    			$sql .= ' auto_increment';
    			$primary_keys[] = $name;
    		}
    		if ($field['index']=='index') $indexes[$name] = $name;
    		
    		if ($field['index']=='fulltext') {
				if (!isset($searchable_keys[$field['key_name']])) $searchable_keys[$field['key_name']] = array();
				$searchable_keys[$field['key_name']][] = $name;
    		}
    		
	    	$sql_lines[$name] = $sql;
    	}
    	
		$table = self::describe_table($tablename);
		$db = FW4_Db::get_instance();
		
		if ($table === false) {
    		
	    	$sql = "CREATE TABLE `".$tablename."` ( ".implode(", ", $sql_lines);
	    	if (count($primary_keys)) $sql .= ', PRIMARY KEY (`'.implode('`,`',$primary_keys).'`)';
	    	foreach ($indexes as $indexname => $fields) {
		    	$sql .= ', INDEX `'.$indexname.'` ('.$fields.' ASC)';
	    	}
	    	foreach ($searchable_keys as $searchable_key_name => $searchable_key_fields) {
		    	$sql .= ', FULLTEXT INDEX `'.$searchable_key_name.'` (`'.implode('`,`',$searchable_key_fields).'`)';
		    	foreach ($searchable_key_fields as $index) {
			    	$sql .= ', FULLTEXT INDEX `'.$index.'` (`'.$index.'`)';
		    	}
	    	}
	    	$sql .= ") ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	    	
	    	$db->query($sql);
	    
	    } else {
	    	$modify_lines = $current_fulltext = $new_fulltext = array();
	    	
	    	foreach ($fields as $name => $field) {
		    	
		    	if ($field['type'] == 'index') {
			    	if (!isset($table[$name])) {
				    	$modify_lines[] = 'ADD INDEX `'.$name.'` ('.$field['fields'].')';
			    	} else if ($field['fields'] != implode(',',$table[$name]['fields'])) {
				    	$modify_lines[] = 'DROP INDEX `'.$name.'`';
				    	$modify_lines[] = 'ADD INDEX `'.$name.'` ('.$field['fields'].')';
			    	}
					unset($table[$name]);
			    	continue;
		    	}
    			
    			if (!isset($field['index'])) $field['index'] = '';
    			if (!isset($field['default']) && !isset($field['allownull'])) $field['default'] = '';
	    	
	    		if (!isset($table[$name])) {
	    		
	    			$modify_lines[] = 'ADD '.$sql_lines[$name];
	    			if ($field['index']=='primary') $modify_lines[] = 'ADD PRIMARY KEY (`'.$name.'`)';
	    			if ($field['index']=='index') $modify_lines[] = 'ADD INDEX (`'.$name.'`)';
	    			if ($field['index']=='fulltext') {
	    				if (!isset($new_fulltext[$field['key_name']])) $new_fulltext[$field['key_name']] = array();
	    				$new_fulltext[$field['key_name']][] = $name;
	    				$modify_lines[] = 'ADD FULLTEXT INDEX (`'.$name.'`)';
	    			}
	    			
	    		} else if ($field['type'] != $table[$name]['type'] || (isset($field['length']) && $field['length'] != $table[$name]['length'] && !is_null($table[$name]['length'])) || $field['index'] != $table[$name]['index'] || (isset($field['default']) && isset($table[$name]['default']) && $field['default'] != $table[$name]['default'])){
	    			if ($table[$name]['index']=='primary' && $field['index']!='primary') $modify_lines[] = 'DROP PRIMARY KEY `'.$name.'`';
	    			if ($table[$name]['index']=='index' && $field['index']!='index') $modify_lines[] = 'DROP INDEX `'.$name.'`';
	    			if ($table[$name]['index']=='fulltext' && $field['index']!='fulltext') $modify_lines[] = 'DROP INDEX `'.$name.'`';
	    			
	    			if ($field['index']=='fulltext') {
	    				if (!isset($new_fulltext[$field['key_name']])) $new_fulltext[$field['key_name']] = array();
	    				$new_fulltext[$field['key_name']][] = $name;
	    			}
	    			if ($table[$name]['index']=='fulltext') {
	    				if (!isset($current_fulltext[$table[$name]['key_name']])) $current_fulltext[$table[$name]['key_name']] = array();
	    				$current_fulltext[$table[$name]['key_name']][] = $name;
	    			}
	    			
	    			$modify_lines[] = 'MODIFY '.$sql_lines[$name];
	    			
	    			if ($field['index']=='primary' && $table[$name]['index']!='primary') $modify_lines[] = 'ADD PRIMARY KEY (`'.$name.'`)';
	    			if ($field['index']=='index' && $table[$name]['index']!='index') $modify_lines[] = 'ADD INDEX (`'.$name.'`)';
	    			if ($field['index']=='fulltext' && $table[$name]['index']!='fulltext') $modify_lines[] = 'ADD FULLTEXT INDEX (`'.$name.'`)';
	    			
	    		} else {
		    		if ($field['index']=='fulltext') {
	    				if (!isset($new_fulltext[$field['key_name']])) $new_fulltext[$field['key_name']] = array();
	    				$new_fulltext[$field['key_name']][] = $name;
	    			}
	    			if ($table[$name]['index']=='fulltext') {
	    				if (!isset($current_fulltext[$table[$name]['key_name']])) $current_fulltext[$table[$name]['key_name']] = array();
	    				$current_fulltext[$table[$name]['key_name']][] = $name;
	    			}
	    		}
	    		unset($table[$name]);
	    		
	    	}
	    	
	    	if ($current_fulltext != $new_fulltext) {
		    	foreach ($current_fulltext as $fulltext_name => $fulltext_keys) {
			    	if (isset($new_fulltext[$fulltext_name])) {
				    	if ($fulltext_keys != $new_fulltext[$fulltext_name]) {
					    	$modify_lines[] = 'DROP INDEX `'.$fulltext_name.'`';
				    	} else unset($new_fulltext[$fulltext_name]);
			    	} else {
				    	$modify_lines[] = 'DROP INDEX `'.$fulltext_name.'`';
			    	}
		    	}
		    	foreach ($new_fulltext as $fulltext_name => $fulltext_keys) {
		    		//if (isset($current_fulltext[$fulltext_name])) $modify_lines[] = 'DROP INDEX `'.$fulltext_name.'`';
					$modify_lines[] = 'ADD FULLTEXT INDEX `'.$fulltext_name.'` (`'.implode('`,`',$fulltext_keys).'`)';
				}
	    	}
	    	
	    	foreach ($table as $name => $field) {
		    	if (isset($field['index']) && isset($field['fields'])) {
			    	$modify_lines[] = 'DROP INDEX `'.$name.'`';
		    	} else {
			    	$modify_lines[] = 'DROP `'.$name.'`';
		    	}
	    	}
	    	
	    	if (count($modify_lines)) {
		    	$sql = "ALTER TABLE `".$tablename."` ".implode(", ", $modify_lines).";";
		    	$db->query($sql);
		    }
    	}
    }
    
    public static function describe_table($table) {
    	$result = array();
    	
    	try {
	    	$db = FW4_Db::get_instance();
	    	$query = $db->query("SHOW COLUMNS FROM `".$table."`");
	    } catch (PDOException $exception) {
	    	$error = $db->errorInfo();
	    	if ($error[1]==1146 || $error[1]==1017) return false;
	    }
    	
    	foreach ($query as $row) {
    		$field = array(
    			'type' => strstr($row['Type'],'(')?substr($row['Type'], 0, strpos($row['Type'], "(")):$row['Type'],
    			'length' => strstr($row['Type'],'(')?substr($row['Type'], strpos($row['Type'], "(") + 1, -1):NULL,
    			'null' => $row['Null'] == 'YES',
    			'index' => false,
    			'key_name' => false,
    			'default' => is_null($row['Default'])?false:$row['Default']
    		);
    		$result[$row['Field']] = $field;
    	}
    	
    	try {
	    	$query = $db->query("SHOW INDEX FROM `".$table."`;");
	    } catch (PDOException $exception) {
	    	$error = $db->errorInfo();
	    	if ($error[1]==1146 || $error[1]==1017) return false;
	    }
    	
    	foreach ($query as $row) {
	    	if ($row['Key_name'] == 'PRIMARY') $result[$row['Column_name']]['index'] = 'primary';
	    	else if ($row['Index_type'] == 'FULLTEXT') {
	    		$result[$row['Column_name']]['index'] = 'fulltext';
	    		if ($row['Key_name'] != $row['Column_name']) $result[$row['Column_name']]['key_name'] = $row['Key_name'];
	    	} else if ($row['Column_name'] == $row['Key_name'] && $row['Seq_in_index'] == 1 && isset($row['Column_name'])) {
		    	$result[$row['Column_name']]['index'] = 'index';
	    	} else {
		    	if (!isset($result[$row['Key_name']])) $result[$row['Key_name']] = array(
			    	'fields' => array(),
			    	'index' => 'index'
		    	);
		    	$result[$row['Key_name']]['fields'][] = $row['Column_name'];
		    }
	    	
    	}
    	return $result;
    }
    
    private static function get_xml() {
    
    	$site = current_site();
    	return $site->structure_xml;
    	
    }
    
    private static function get_expanded_xml() {
    
    	$site = current_site();
    	return $site->structure_xml_expanded;
    	
    }
    
    private static function merge_xml(&$a,$b) {
	    if ($b->count() != 0)  $new = $a->addChild($b->getName());
	    else $new = $a->addChild($b->getName(), $b);
	    
	    foreach ($b->attributes() as $key => $value) $new->addAttribute($key, $value);
	    
	    if ($b->count() != 0) { 
	        foreach ($b->children() as $child) self::merge_xml($new,$child);
	    }
    }
    
    private static function expand_node($node) {
	    return simplexml_import_dom(self::expand_DOM_node(dom_import_simplexml($node)));
    }
    
    private static function expand_DOM_node($node,$parent=false) {
    	$types = FW4_Type_Manager::get_instance();
		$toexpand = array();
		$toreplace = array();
		$slugnodes = array();
		
		if (Config::admin_enabled() && ($node->nodeName == 'object' || $node->nodeName == 'page')) {
			
			$creator = $node->ownerDocument->createElement('creator');
			$creator->setAttribute('name','created_by_user');
			$node->appendChild($creator);
			
			$editor = $node->ownerDocument->createElement('editor');
			$editor->setAttribute('name','edited_by_user');
			$node->appendChild($editor);
			
			$create_date = $node->ownerDocument->createElement('timedate');
			$create_date->setAttribute('name','created_at_date');
			$node->appendChild($create_date);
			
			$edit_date = $node->ownerDocument->createElement('timedate');
			$edit_date->setAttribute('name','edited_at_date');
			$node->appendChild($edit_date);
			
		}
		
		if ($parent && $parent->nodeName != 'structure') {
			$node->setAttribute('parent_type',$parent->nodeName);
			$node->setAttribute('parent_name',$parent->getAttribute('name'));
			$node->setAttribute('stack',$parent->getAttribute('stack').'>'.$node->getAttribute('name'));
			$node->setAttribute('path',$parent->getAttribute('path').'>'.$node->getAttribute('name'));
		} else {
			$node->setAttribute('stack',$node->getAttribute('name'));
			$node->setAttribute('path',$node->getAttribute('name'));
		}
		
    	foreach ($node->childNodes as $child) {
	    	if ($child->nodeName == 'object' || $child->nodeName == 'page') $toexpand[] = $child;
	    	else if ($type_obj = $types->get_type($child->nodeName)) $toreplace[] = $child;
	    	else if ($child->nodeName == 'slug') $slugnodes[] = $child;
    	}
    	foreach ($toexpand as $child) $node->replaceChild(self::expand_DOM_node($child,$node),$child);
    	foreach ($toreplace as $child) {
    		$typename = $child->nodeName;
    		$type_obj = $types->get_type($typename);
	    	$xml = new SimpleXMLElement($type_obj->get_structure(simplexml_import_dom($child),simplexml_import_dom($node)));
	    	$node->removeChild($child);
	    	foreach ($xml->children() as $newchild) {
	    		$newchild = dom_import_simplexml($newchild);
	    		$newchild->setAttribute('type_name',$typename);
	    		$newchild = $node->ownerDocument->importNode($newchild,true);
		    	$node->appendChild(self::expand_DOM_node($newchild,$node));
	    	}
    	}
    	foreach ($slugnodes as $child) {
    		if (!$child->hasAttribute('format') && $child->hasAttribute('source')) $child->setAttribute('format','['.$child->getAttribute('source').']');
    		preg_match_all('/\[([a-z0-9\_]+)\]/is',strval($child->getAttribute('format')),$matches,PREG_SET_ORDER);
    		$name = $child->hasAttribute('name')?strval($child->getAttribute('name')):'slug';
    		$translatable = false;
    		$slug_fields = array();
    		foreach ($matches as $match) {
    			$source = false;
    			foreach ($node->childNodes as $objchild) {
    				if ($objchild->nodeName != '#text' && strval($objchild->getAttribute('name')) == $match[1] && $objchild->hasAttribute('translatable')) {
    					$child->setAttribute('translatable','translatable');
    				}
    			}
    		}
    	}
    	if ($node->hasAttribute("archived") && ($node->nodeName == 'object' || $node->nodeName == 'page')) {
	    	$archive = $node->cloneNode(true);
    		$toremove = array();
	    	foreach ($archive->childNodes as $child) {
		    	if ($child->nodeName == 'object' || $child->nodeName == 'page') $toremove[] = $child;
	    	}
	    	foreach ($toremove as $child) {
		    	$archive->removeChild($child);
		    }
			$archive->setAttribute('name','_versions');
			$archive->removeAttribute('order');
			
			$newnode = $node->ownerDocument->createElement('object');
		    foreach ($archive->childNodes as $child){
		        $child = $archive->ownerDocument->importNode($child->cloneNode(true), true);
		        $newnode->appendChild($child);
		    }
		    foreach ($archive->attributes as $attrName => $attrNode) {
		        $newnode->setAttribute($attrName,$attrNode->textContent);
		    }
		    $node->appendChild($newnode);
    	}
	    return $node;
    }
    
    private static function expand_protocols($node) {
	    return simplexml_import_dom(self::expand_DOM_protocols(dom_import_simplexml($node),$node));
    }
    
    private static function expand_DOM_protocols($node,$xml) {
		$toexpand = array();
		
		if ($node->hasAttribute('implements')) {
			$protocol_name = $node->getAttribute('implements');
			$protocol_structure = $xml->xpath('//protocol[@name="'.$protocol_name.'"]');
			if (!count($protocol_structure)) continue;
			$protocol_structure = $protocol_structure[0];
			
			$first_child = $node->firstChild;
			
			foreach (dom_import_simplexml($protocol_structure)->childNodes as $child) {
		    	if ($first_child) $node->insertBefore($child->cloneNode(true),$first_child);
		    	else $node->appendChild($child->cloneNode(true));
	    	}
			
			$first_child = $node->firstChild;
	    	
	    	foreach ($node->childNodes as $child) {
		    	if ($child->hasAttributes() && $child->getAttribute('before_implementation')) {
			    	$node->insertBefore($child,$first_child);
		    	}
	    	}
	    	
		}
		
    	foreach ($node->childNodes as $child) {
	    	if ($child->nodeName == 'object' || $child->nodeName == 'page') $toexpand[] = $child;
    	}
    	foreach ($toexpand as $child) $node->replaceChild(self::expand_DOM_protocols($child,$xml),$child);
    	
    	return $node;
    	
    	foreach ($slugnodes as $child) {
    		if (!$child->hasAttribute('format') && $child->hasAttribute('source')) $child->setAttribute('format','['.$child->getAttribute('source').']');
    		preg_match_all('/\[([a-z0-9\_]+)\]/is',strval($child->getAttribute('format')),$matches,PREG_SET_ORDER);
    		$name = $child->hasAttribute('name')?strval($child->getAttribute('name')):'slug';
    		$translatable = false;
    		$slug_fields = array();
    		foreach ($matches as $match) {
    			$source = false;
    			foreach ($node->childNodes as $objchild) {
    				if ($objchild->nodeName != '#text' && strval($objchild->getAttribute('name')) == $match[1] && $objchild->hasAttribute('translatable')) {
    					$child->setAttribute('translatable','translatable');
    				}
    			}
    		}
    	}
    	if ($node->hasAttribute("archived") && ($node->nodeName == 'object' || $node->nodeName == 'page')) {
	    	$archive = $node->cloneNode(true);
    		$toremove = array();
	    	foreach ($archive->childNodes as $child) {
		    	if ($child->nodeName == 'object' || $child->nodeName == 'page') $toremove[] = $child;
	    	}
	    	foreach ($toremove as $child) {
		    	$archive->removeChild($child);
		    }
			$archive->setAttribute('name','_versions');
			$archive->removeAttribute('order');
			
			$newnode = $node->ownerDocument->createElement('object');
		    foreach ($archive->childNodes as $child){
		        $child = $archive->ownerDocument->importNode($child->cloneNode(true), true);
		        $newnode->appendChild($child);
		    }
		    foreach ($archive->attributes as $attrName => $attrNode) {
		        $newnode->setAttribute($attrName,$attrNode->textContent);
		    }
		    $node->appendChild($newnode);
    	}
	    return $node;
    }
    
    public static function rebuild_search_index() {
    	if (!self::$structure_xml_expanded) self::$structure_xml_expanded = new SimpleXMLElement(self::get_expanded_xml());
    	$searchable_objects = self::rebuild_search_index_for_object(self::$structure_xml_expanded);
    }
    
    private static function rebuild_search_index_for_object($structure) {
    	$searchable_objects = array();
    	$search_index = array();
    	$languages = languages();
    	$types = FW4_Type_Manager::get_instance();
    	
    	$language_field = false;
    	$language = language();
    	foreach ($structure->xpath('string[@type_name="language"]') as $possible_language_field) {
	    	$language_field = $possible_language_field;
	    	$language = 0;
    	}
    	
    	foreach ($structure->children() as $type => $field) {
    		if ($type == 'page' || $type == 'object' || $type == 'site') $searchable_objects = array_merge($searchable_objects,self::rebuild_search_index_for_object($field));
    		else if (isset($field['searchable']) && isset($field['type_name']) && $type_obj = $types->get_type(strval($field['type_name']))) {
	    		if (method_exists($type_obj,'get_search_index')) {
		    		$fieldname = strval($field['searchable']);
		    		if (isset($field['translatable'])) {
	    				foreach ($languages as $key => $lang) {
	    					if (!isset($search_index[$key])) $search_index[$key] = array();
	    					$search_index[$key][$fieldname] = array('type'=>$type_obj,'field'=>$field);
	    				}
	    			} else {
	    				if (!isset($search_index[$language])) $search_index[$language] = array();
	    				$search_index[$language][$fieldname] = array('type'=>$type_obj,'field'=>$field);
	    			}
	    		}
    		} else if (isset($field['searchable'])) {
    			$fieldname = strval($field['searchable']);
    			if (isset($field['translatable'])) {
    				foreach ($languages as $key => $lang) {
    					if (!isset($search_index[$key])) $search_index[$key] = array();
    					$search_index[$key][$fieldname] = strval($field['name']).'_'.$key;
    				}
    			} else {
    				if (!isset($search_index[$language])) $search_index[$language] = array();
    				$search_index[$language][$fieldname] = strval($field['name']);
    			}
    		}
    	}
    	
    	if (count($search_index)) {
    		$page = 1;
    		do {
    			$results = limit(50)->page($page++)->get(strval($structure['stack']));
    			foreach ($results as $result) {
    				foreach ($search_index as $language => $searchfields) {
	    				if ($language === 0) {
		    				$language_field_name = strval($language_field['name']);
		    				$language = $result->$language_field_name;
		    				where('object_id = %d',$result->id)->where('_language != %s',$language)->where('object_name = %s',strval($structure['stack']))->delete('_search_index');
	    				}
    					$data = array();
    					foreach ($searchfields as $searchfieldname => $searchfield) {
    						if (is_array($searchfield)) $data[$searchfieldname] = $searchfield['type']->get_search_index($searchfield['field'],$result,$structure,$language);
    						else if (isset($result->$searchfield)) $data[$searchfieldname] = $result->$searchfield;
    					}
    					if (count($data)) {
	    					$existing = where('object_id = %d',$result->id)->where('_language = %s',$language)->where('object_name = %s',strval($structure['stack']))->get_row('_search_index');
	    					if ($existing) {
	    						where('id = %d',$existing->id)->update('_search_index',$data);
	    						where('id != %d',$existing->id)->where('object_id = %d',$result->id)->where('_language = %s',$language)->where('object_name = %s',strval($structure['stack']))->delete('_search_index');
	    					} else {
	    						$data['object_id'] = $result->id;
	    						$data['object_name'] = strval($structure['stack']);
	    						$data['_language'] = $language;
	    						insert('_search_index',$data);
	    					}
	    				}
    				}
    			}
    		} while (count($results) == 50);
    		$searchable_objects[] = strval($structure['stack']);
    	}
    	return $searchable_objects;
    }
    
}
    
function is_of_type($record,$type) {
	if (!is_array($record) || !isset($record['_object_path'])) return false;
	return preg_match('/(>|^)'.preg_quote($type).'$/i',$record['_object_path']) == 1;
}
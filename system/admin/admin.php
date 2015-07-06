<?

class FW4_Admin extends Controller {
	
	private static $current_path = array();
	
	private static $data = array();
	private static $parent = array();
	public static $parent_structure = false;
	public static $parent_item = false;
	public static $current_item = false;
	private static $rootname = false;
	
	public static $recursive_levels = 0;
	
	public static $has_headers = false;
	public static $in_fieldset = false;
	public static $duplicating = false;
	
	public static function show() {
	
		use_library('navstack');
		
		FW4_Structure::check_structure();
		
		FW4_User::$include_superadmin = true;
		
		$language_codes = array_keys(languages());
		Router::set_language(array_shift($language_codes));
		View_Loader::get_instance()->set_path(BASEPATH.'admin/');
		
		return self::route();
		
	}
	
	private static function login() {
		
		if (FW4_User::is_logged_in()) redirect(url(ADMINDIR,false));
		
		$error = false;
		
		if (isset($_POST['email']) && isset($_POST['password'])) {
			try {
				if (FW4_User::log_in($_POST['email'],$_POST['password'])) redirect(url(ADMINDIR,false));
				else $error = l(array('nl'=>'Wij konden u niet aanmelden met de door u opgegeven gegevens.','en'=>'We couldn\'t log you in with the provided information.','fr'=>'Nous ne pouvions pas vous connecter avec les informations fournies.'));
			} catch (Exception $e) {
				$error = l(array('nl'=>'U hebt te vaak geprobeerd aan te melden. Probeer binnen een uur opnieuw.','en'=>'You have attempted to login too frequently. Please try again in an hour.','fr'=>'Vous avez essay&eacute; trop souvent de vous connecter. Veuillez r&eacute;essayer dans une heure.'));
			}
		}
		
		$site = current_site();
		
		echo view("login",array(
			'site' => $site,
			'error' => $error
		));
		
		return true;
	}
	
	private static function forgotpass() {
		
		if (FW4_User::is_logged_in()) redirect(url(ADMINDIR,false));
		
		$error = $success = false;
		
		$site = current_site();
		
		if (isset($_POST['email'])) {
			$user = where('email LIKE %s',$_POST['email'])->get_row('user');
			if ($user) {
				$code = random_string(25);
				where('id = %d',$user->id)->update('user',array(
					'password_code' => $code
				));
				use_library('email');
				$link = url(ADMINDIR.'/reset-password/'.$code.'/',false);
				html_mail('noreply@'.$_SERVER['SERVER_NAME'],$site->name,$user->email,'Jouw wachtwoord opnieuw instellen','Hallo '.$user->firstname.',<br/>
<br/>
Jij of iemand anders heeft ons gemeld dat je jouw wachtwoord vergeten bent. Je kan een nieuw wachtwoord instellen op <a href="'.$link.'">'.$link.'</a>.<br/>
Indien je niet gevraagd hebt achter een nieuw wachtwoord, dan kan je dit bericht gewoon negeren.<br/>
<br/>
Vriendelijke groeten,<br/>
Het '.$site->name.' team');
				$success = l(array('nl'=>'We hebben je een e-mail gestuurd met instructies om je wachtwoord opnieuw in te stellen.'));
			} else {
				$error = l(array('nl'=>'Dit e-mail adres is onbekend.'));
			}
		}
		
		echo view("forgotpass",array(
			'site' => $site,
			'error' => $error,
			'success' => $success
		));
		
		return true;
	}
	
	private static function resetpass($user) {
		
		if (FW4_User::is_logged_in()) redirect(url(ADMINDIR,false));
		
		$error = $success = false;
		
		$site = current_site();
		
		if (isset($_POST['password'])) {
			if (strlen($_POST['password']) < 6) {
				$error = 'Het door u gekozen wachtwoord is te kort. Kies bij voorkeur een wachtwoord van minstens 6 tekens.';
			} else if ($_POST['password'] != $_POST['confirm-password']) {
				$error = 'De door u opgegeven wachtwoorden komen niet overeen.';
			} else {
				where('id = %d',$user->id)->update('user',array(
					'password_code' => '',
					'password' => FW4_User::hash_password($_POST['password']),
					'password_attempts' => ''
				));
				log_in($user->email,$_POST['password']);
				redirect(url(ADMINDIR,false));
			}
		}
		
		echo view("resetpass",array(
			'site' => $site,
			'error' => $error,
			'success' => $success
		));
		
		return true;
	}
	
	private static function route() {
	
		// Set the PHP locale to correspond to our defined language.
		if (language() == 'en') setlocale(LC_ALL, 'en_US');
		else if (language() == 'nl') setlocale(LC_ALL, 'nl_BE');
		else if (language() == 'fr') setlocale(LC_ALL, 'fr_FR');
		else if (language() == 'de') setlocale(LC_ALL, 'de_DE');
	
		// Show the login page if requested
		if (segment(0) == 'login') return self::login();
		
		// Show the forgot pass page if requested
		if (segment(0) == 'forgot') return self::forgotpass();
		
		// Show the forgot pass page if requested
		if (segment(0) == 'reset-password' && strlen(segment(1)) > 24 && $user = where('password_code = %s',segment(1))->get_row('user')) return self::resetpass($user);
	
		// Rebuild search index if requested
		if (segment(0) == 'rebuild_search_index') {
			FW4_Structure::rebuild_search_index();
			redirect(url(ADMINDIR,false));
		}
		
		// User should be logged in. If not, demand login.
		if (!FW4_User::is_logged_in()) redirect(url(ADMINDIR.'/login',false));
		
		// User is logged in. Make sure he's an admin.
		if (!FW4_User::is_admin()) {
			FW4_User::log_out();
			redirect(url(ADMINDIR.'/login',false));
		}
		
		// Log out if requested
		if (segment(0) == 'logout') {
			FW4_User::log_out();
			redirect(url(ADMINDIR.'/login',false));
		}
		
		// If not viewing any specific page. Redirect to the first one.
		// If at some point we'd like to implement a dashboard, this would be the place to show it.
		if (!count(segments())) {
			$pages = self::get_pages();
			$page = array_shift($pages);
			redirect(url(ADMINDIR.'/'.$page['name'],false));
		}
		
		// If the user is viewing a special page (like a popup), show it.
		if (substr(segment(0),0,1) == '_' && file_exists(BASEPATH.'admin/special/'.substr(segment(0),1).'.php')) {
			include(BASEPATH.'admin/special/'.substr(segment(0),1).'.php');
			return true;
		}
		
		// Check if this is an AJAX request.
		if (substr(segment(0),0,5) == 'ajax_') {
			$segments = segments();
			call_user_func_array(get_class().'::'.array_shift($segments), $segments);
			return true;
		}
		
		return self::handle_item(segments());
	
	}
	
	public static function handle_item($segments,$data=array()) {
		$types = FW4_Type_Manager::get_instance();
		
		if (!$item = array_shift($segments)) return false;
		
		self::$current_path[] = $item;
		
		if ($structure = FW4_Structure::get_object_structure('>'.implode('>', self::$current_path),false)) {
			self::$parent_structure = $structure;
			
			if ($structure->getName() == 'page') return self::handle_page($structure,$segments);
			else if ($structure->getName() == 'object') return self::handle_object($structure,$segments,$data);

		} else if (preg_match('/list_data_([a-z0-9\-\_]+)/is',$item,$matches) && function_exists('datasource_'.$matches[1])) {
			if (reset($segments) == 'export') {
				$listdata = call_user_func('datasource_'.$matches[1],true);
				self::export_list($matches[1],$listdata);
				return true;
			}
			
		} else if ($item == '_version' && isset(self::$parent_structure['archived'])) {
			$version = where('version_id = %d AND id = %d',array_shift($segments),$data->id)->get_row(self::$parent_structure['stack'].'>_versions');
			
			if (!$version) return false;
			if (!count($segments)) {
				self::print_version(self::$parent_structure,$version);
				return true;
			} else if (reset($segments) == 'delete') {
				where('version_id = %d',$version->version_id)->delete(self::$parent_structure['stack'].'>_versions');
				redirect(preg_replace('/[^\/]+\/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI']));
			} else if (reset($segments) == 'restore') {
				$version = where('version_id = %d',$version->id)->translate(false)->get_row(self::$parent_structure['stack'].'>_versions');
				unset($version->version_id);
				where('id = %d',$version->id)->update(self::$parent_structure['stack'],$version->to_array());
				//insert(self::$parent_structure['stack'].'>_versions',$version->to_array());
				redirect(preg_replace('/[^\/]+\/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI']));
			}
			
		} else if (self::$parent_structure) {
		
			$result = self::handle_fields(self::$parent_structure,$item,$segments,$data);
			if ($result) return true;
			
		}
		
		if ($type = $types->get_type($item)) {
			$action = 'function_'.array_shift($segments);
		
			if (method_exists($type, $action)) {
				
				$arguments = array_merge(array($structure,$data),$segments);
				
				$reflector = new ReflectionClass(get_class($type));
				if (count($arguments) >= $reflector->getMethod($action)->getNumberOfRequiredParameters()) {
					call_user_func_array(array($type,$action), $arguments);
					return true;
				}
				
			}
		}
		
		return false;
		
	}
	
	private static function handle_fields($structure,$item,$segments,$data) {
	
		$types = FW4_Type_Manager::get_instance();
		
		foreach ($structure->children() as $child) {
			if (isset($child['name']) && $child['name'] == $item && $type = $types->get_type($child->getName())) {

				$action = 'function_'.array_shift($segments);
		
				if (method_exists($type, $action)) {
					
					$arguments = array_merge(array($child,self::$parent_structure,$data),$segments);
					
					$reflector = new ReflectionClass(get_class($type));
					if (count($arguments) >= $reflector->getMethod($action)->getNumberOfRequiredParameters()) {
						array_pop(self::$current_path);
						$result = call_user_func_array(array($type,$action), $arguments);
						if (!is_null($result)) return $result;
						return true;
					}
					
				}
			} else if ($child->getName() == 'fieldset') {
				$result = self::handle_fields($child,$item,$segments,$data);
				if ($result) return true;
			}
		}
		
		return false;
		
	}
	
	private static function handle_page($structure,$segments) {

		$data = get_row('>'.$structure['stack']);
			
		if ($structure['parent_type'] == 'site') {
			$site = current_site();
			self::$parent = array('site_id'=>$site['id']);
		}
		
		if (count($segments)) {
			self::$parent = array($structure['name'].'_id' => $data->id);
			return self::handle_item($segments,$data);
		} else {
			
			// Clear breadcrumbs
			navigation_stack()->clear();
			
			self::edit_object($structure,$data);
			return true;
		}

	}
	
	private static function handle_object($structure,$segments,$data=array()) {
		
		$action = array_shift($segments);
			
		if (!$action) return false;
		
		if (is_numeric($action)) {
		
			$data = where('id = %d',intval($action))->get_row($structure['stack']);
			
			if (!$data) return false;
			self::$current_item = $data;
			
			if (reset($segments) == 'delete') {
				where('id = %d',$data->id)->delete($structure['stack']);
				if (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] && $_SERVER['HTTP_REFERER'] != preg_replace('/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI'])) redirect($_SERVER['HTTP_REFERER']);
				redirect(preg_replace('/[^\/]+\/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI']));
			} else if (reset($segments) == 'duplicate') {
				array_shift($segments);
				unset($data->id);
				self::$duplicating = true;
			}
			
			if (count($segments)) {
			
				self::$parent_item = $data;
			
				$recursive = $structure->xpath('recursive');
				$recursive = reset($recursive);
				if ($recursive && (reset($segments) == strval($recursive['name'])) && (!isset($recursive['levels']) || self::$recursive_levels < $recursive['levels'])) {
					self::$parent = array('parent_id'=>$data->id);
					self::$recursive_levels++;
					if (isset($recursive['title'])) $structure['title'] = $recursive['title'];
					if (isset($recursive['label'])) $structure['label'] = $recursive['label'];
					array_shift($segments);
					return self::handle_object($structure,$segments,$data);
				} else {
					if (!self::$duplicating) self::$parent = array($structure['name'].'_id'=>$data->id);
					return self::handle_item($segments,$data);
				}
			} else {
				self::edit_object($structure,$data);
				return true;
			}
			
		} else if ($action == 'add') {
			
			if (count($segments)) {
				self::$parent = false;
				return self::handle_item($segments);
			} else {
				self::edit_object($structure);
				return true;
			}
		} else if ($action == 'export' && !count($segments) && isset($structure['exportable'])) {
			$keys = array_keys(self::$parent);
			$query = where(reset($keys).' = %d',$data->id);
			if (isset($_SESSION['filter_'.$structure['stack']])) $searchable_fields = self::apply_ajax_filter($query,$structure,$structure,$_SESSION['filter_'.$structure['stack']]);
			$data = $query->get($structure['stack']);
			self::export_object($structure,$data);
			return true;
		}
		
		return false;

	}
	
	private static function ajax_sort() {
		$table = implode('/',func_get_args());
		
		$structure = FW4_Structure::get_object_structure($table,true);
		if (!$structure) return false;
		
		$page = intval($_POST['page'])-1;
		unset($_POST['page']);
		
		self::update_sorting_field($table);
		
		foreach ($_POST as $key => $value) {
			if (preg_match('/^sort-(\d+)$/is', $key, $match)) {
				where('id = %d',intval($match[1]))->update($table,array('_sort_order'=>(intval($value)+$page*50)));
			}
		}
		
		if (isset($structure['archived']) && strval($structure['name']) != '_versions') {
			$db = FW4_Db::get_instance();
			$db->query('UPDATE `'.$structure['path'].'>_versions` versions JOIN `'.$structure['path'].'` origtable ON origtable.id = versions.id SET versions._sort_order = origtable._sort_order WHERE versions.version_id IN (SELECT * FROM (SELECT MAX(version_id) FROM `'.$structure['path'].'>_versions` GROUP BY id) as version_ids)');
		}
	}
	
	private static function ajax_object_page() {
		$segments = func_get_args();
		$structure = FW4_Structure::get_object_structure(implode('/',$segments),false);
		
		if (!$structure || !isset($_POST['page']) || !isset($_POST['parentname']) || !isset($_POST['parentid'])) return false;
		
		self::object_page_for_ajax($structure,$_POST['page'],$_POST['parentname'],$_POST['parentid']);
	}
	
	private static function object_page_for_ajax($structure,$page,$parentname,$parentid) {
		
		self::$parent_structure = FW4_Structure::get_object_structure($parentname,false);
		self::$current_item = where('id = %d',$parentid)->get_row($parentname);
		
		unset($_SESSION['filter_'.$structure['stack']]);
		$query = where(self::$parent_structure['name'].'_id = %d',self::$current_item->id)->limit(50)->page(intval($page));
		foreach ($structure->children() as $type => $subfield) {
			if ($type == 'object') $query->including(strval($subfield['name']));
			if ($type == 'summary' && isset($subfield['needs'])) {
				foreach (explode(',',strval($subfield['needs'])) as $needs) $objectquery->including(trim($needs));
			}
			if ($type == 'recursive') {
				$query->where('parent_id IS NULL');
			}
		}
		$data = $query->get($structure['stack']);
		$details = self::object_list_details($structure);
		echo view('object_rows',array(
			'data' => $data,
			'field' => $structure,
			'shownfields' => $details['shownfields'],
			'allow_edit' => true,
			'delete_limits' => $details['delete_limits'],
			'object' => $structure,
			'recursive_name' => $details['recursive_name']
		));
	}
	
	private static function ajax_move_to_page() {
		$segments = func_get_args();
		$structure = FW4_Structure::get_object_structure(implode('/',$segments),false);
		if (!$structure || !isset($_POST['page']) || !isset($_POST['id']) || !isset($_POST['parentname']) || !isset($_POST['parentid'])) return false;
		$page = intval($_POST['page']);
		
		self::update_sorting_field($structure['stack']);
		
		$target = limit(50)->page($page)->get_row($structure['stack']);
		$row = where('id = %d',intval($_POST['id']))->get_row($structure['stack']);
		if ($row['_sort_order'] < $target['_sort_order']) {
			at_most('_sort_order',$target['_sort_order'])->more_than('_sort_order',$row['_sort_order'])->decrement($structure['stack'],'_sort_order');
		} else {
			less_than('_sort_order',$row['_sort_order'])->at_least('_sort_order',$target['_sort_order'])->increment($structure['stack'],'_sort_order');
		}		
		where('id = %d',$row['id'])->update($structure['stack'],array(
			'_sort_order' => $target['_sort_order']
		));
		if (isset($structure['archived']) && strval($structure['name']) != '_versions') {
			$db = FW4_Db::get_instance();
			$db->query('UPDATE `'.$structure['path'].'>_versions` versions JOIN `'.$structure['path'].'` origtable ON origtable.id = versions.id SET versions._sort_order = origtable._sort_order WHERE versions.version_id IN (SELECT * FROM (SELECT MAX(version_id) FROM `'.$structure['path'].'>_versions` GROUP BY id) as version_ids)');
		}
		self::object_page_for_ajax($structure,$page,$_POST['parentname'],$_POST['parentid']);
	}
	
	private static function ajax_filter() {
		$segments = func_get_args();
		$structure = FW4_Structure::get_object_structure(implode('/',$segments),false);
		if (!$structure || !isset($_POST['page']) || !isset($_POST['parentname']) || !isset($_POST['parentid'])) die();
		
		self::$parent_structure = FW4_Structure::get_object_structure($_POST['parentname'],false);
		self::$current_item = where('id = %d',$_POST['parentid'])->get_row($_POST['parentname']);
		
		$query = where($structure['parent_name'].'_id = %d',self::$current_item->id)->page(intval($_POST['page']));
		
		$_SESSION['filter_'.$structure['stack']] = array(
			'page' => $_POST['page']
		);
		
		$searchable_fields = self::apply_ajax_filter($query,$structure,$structure,$_POST);
		
		if (isset($_POST['search']) && $_POST['search']) {
		
			$_SESSION['filter_'.$structure['stack']]['search'] = $_POST['search'];
			
			$search_where_strings = array();
			foreach (explode(' ',$_POST['search']) as $keyword) {
				$search_where_strings[] = 'CONCAT(IFNULL('.implode(',""),IFNULL(',$searchable_fields).',"")) LIKE '.$query->escape('*'.$keyword.'*');
			}
			$query->where(implode(' AND ',$search_where_strings));
		}
		
		foreach ($structure->children() as $type => $subfield) {
			if ($type == 'recursive') {
				$query->where('parent_id IS NULL');
			}
		}
		
		$pages = ceil($query->count_rows($structure['stack'])/50);
		$data = $query->limit(50)->get($structure['stack']);
		$details = self::object_list_details($structure);
		
		echo json_encode(array(
			'html' => view('object_filtered',array(
				'data' => $data,
				'field' => $structure,
				'shownfields' => $details['shownfields'],
				'delete_limits' => $details['delete_limits'],
				'allow_edit' => false,
				'object' => $structure
			)),
			'pages' => $pages
		));
	}
	
	private static function apply_ajax_filter($query,$fields,$object,$input) {
		$searchable_fields = array();
		foreach ($fields as $type => $field) {
		
			if (isset($_POST['filter_'.$field['name']])) $_SESSION['filter_'.$object['stack']]['filter_'.$field['name']] = $_POST['filter_'.$field['name']];
			
			switch ($type) {
				case 'fieldset':
					$searchable_fields = array_merge($searchable_fields,self::apply_ajax_filter($query,$field,$object,$input));
				case 'string':
				case 'email':
				case 'text':
				case 'bank':
				case 'number':
					if (isset($field['filterable'])) {
						if (isset($field['translatable'])) {
							foreach (languages() as $key => $lang) {
								$searchable_fields[] = '`'.$field['name'].'_'.$key.'`';
							}
						} else {
							$searchable_fields[] = '`'.$field['name'].'`';
						}
					}
					break;
				case 'filter':
					$classname = ucfirst($object['contentname']);
					$function_name = 'filter_'.strval($field['name']);
					if (class_exists($classname) && method_exists($classname,$function_name) && $input['filter_'.$field['name']]) {
						View_Loader::get_instance()->set_path(CONTENTPATH.str_replace(' ', '',strtolower(strval($object['contentname']))));
						call_user_func_array($classname.'::'.$function_name, array($query,$input['filter_'.$field['name']],$field,$object));
						View_Loader::get_instance()->set_path(BASEPATH.'admin/');
					}
					break;
				case 'date':
				case 'timedate':
					if (isset($field['filterable'])) {
						if (isset($input['filter_'.$field['name']])) {
							if ($input['filter_'.$field['name']] !== '') {
								if ($input['filter_'.$field['name']] == 1) $query->where($field['name'].' > '.mktime(0,0,0));
								else if ($input['filter_'.$field['name']] == 2) $query->where($field['name'].' < '.mktime(0,0,0).' AND '.$field['name'].' > '.strtotime('-1 day',mktime(0,0,0)));
								else if ($input['filter_'.$field['name']] == 3) $query->where($field['name'].' > '.strtotime('-7 days',mktime(0,0,0)));
								else if ($input['filter_'.$field['name']] == 4) $query->where($field['name'].' > '.strtotime('-30 days',mktime(0,0,0)));
								else if ($input['filter_'.$field['name']] == 5) $query->where($field['name'].' > '.strtotime('-1 year',mktime(0,0,0)));
								else if ($input['filter_'.$field['name']] == 6) $query->where($field['name'].' > '.mktime(0,0,0,date('n'),1));
								else if ($input['filter_'.$field['name']] == 7) $query->where($field['name'].' > '.mktime(0,0,0,date('n')-((date('n')-1)%3),1));
								else if ($input['filter_'.$field['name']] == 8) $query->where($field['name'].' > '.mktime(0,0,0,date('n')-((date('n')-1)%3)-3,1).' AND '.$field['name'].' < '.mktime(0,0,0,date('n')-((date('n')-1)%3),1));
								else if ($input['filter_'.$field['name']] == 9) $query->where($field['name'].' > '.mktime(0,0,0,date('n')-1,1).' AND '.$field['name'].' < '.mktime(0,0,0,date('n'),1));
								else if ($input['filter_'.$field['name']] == 10) $query->where($field['name'].' > '.mktime(0,0,0,date('n')-2,1).' AND '.$field['name'].' < '.mktime(0,0,0,date('n')-1,1));
								else if ($input['filter_'.$field['name']] == 11) $query->where($field['name'].' > '.mktime(0,0,0,date('n')-3,1).' AND '.$field['name'].' < '.mktime(0,0,0,date('n')-2,1));
							}
						}
					}
					break;
				case 'bool':
					if (isset($field['filterable'])) {
						if (isset($input['filter_'.$field['name']])) {
							if ($input['filter_'.$field['name']] !== '') {
								if ($input['filter_'.$field['name']]) $query->where($field['name'].' > 0');
								else $query->where('(`'.$field['name'].'` = 0 OR `'.$field['name'].'` IS NULL OR `'.$field['name'].'` = "")');
							}
						}
					}
					break;
				case 'choice':
					if (isset($field['filterable']) && isset($input['filter_'.$field['name']]) && $input['filter_'.$field['name']] !== '') {
						if ($input['filter_'.$field['name']]) {
							if (isset($field['source'])) {
								if (isset($field['multiple'])) {
									$ids = array();
									$idfieldname = $object['name'].'_id';
									foreach (where($field['name'].' = %d',$input['filter_'.$field['name']])->get($object['stack'].'>'.$field['name']) as $row) {
										$ids[] = $row->$idfieldname;
									}
									if (!count($ids)) $ids[] = 0;
									$query->where('id IN %$',$ids);
								} else {
									$query->where($field['name'].'_id = %d',$input['filter_'.$field['name']]);
								}
							} else $query->where($field['name'].' = %d',$input['filter_'.$field['name']]);
						}
					}
					break;
			}
		}
		return $searchable_fields;
	}
	
	private static function edit_object($object,$data=false,$parent=array()) {
		global $config;
		
    	$types = FW4_Type_Manager::get_instance();
    	
    	$user = FW4_User::get_user();
    	
		$error = $success = false;
		
		if ($data === false) $data = new stdClass();
    	
		if (count($_POST) || count($_FILES)) {
			$newdata = self::prepare_posted_data($object,$data);
			
			$has_changed = $is_new = false;
			foreach ($newdata as $key => $value) {
				if (!isset($data->$key) || $newdata[$key] != $data->$key) $has_changed = true;
			}
			
			if ($has_changed) {
				$newdata['edited_by_user'] = $user->id;
				$newdata['edited_at_date'] = time();
				
				$rightless_data = self::process_rightless_values($object);
				if (count($rightless_data)) $newdata = array_merge($newdata,$rightless_data);
			}

			if (isset($data->id)) {
				where('id = %d',intval($data->id))->update(strval($object['stack']),$newdata);
				$_SESSION['successmessage'] = 'De wijzigingen werden succesvol opgeslagen.';
				$newdata['id'] = $data->id;
			} else {
				$is_new = true;
				if (isset($object['sortable'])) {
					$maxsorting = pick('MAX(_sort_order) as maxorder')->get_row($object['stack']);
					$newdata['_sort_order'] = $maxsorting->maxorder + 1;
				}
				$newdata['created_by_user_id'] = $user->id;
				$newdata['created_at_date'] = time();
				$newdata = array_merge($newdata,self::$parent);
				if (isset($object['label'])) {
					$_SESSION['successmessage'] = 'Uw '.strtolower($object['label']).' werd succesvol toegevoegd.';
				} else {
					$_SESSION['successmessage'] = 'Het item werd succesvol toegevoegd.';
				}
				$newdata['id'] = insert($object['stack'],$newdata,false);
			}
			
			if (isset($object['onsave'])) {
				$classname = ucfirst($object['contentname']);
				$function_name = strval($object['onsave']);
				if (class_exists($classname) && method_exists($classname,$function_name)) {
					View_Loader::get_instance()->set_path(CONTENTPATH.str_replace(' ', '',strtolower(strval($object['contentname']))));
					try {
						$result = call_user_func_array($classname.'::'.$function_name, array($object,$data,array_merge($newdata,self::$parent)));
						if (is_string($result)) $success = $result;
					} catch (Exception $e) {
					    $error = $e->getMessage();
					}
					View_Loader::get_instance()->set_path(BASEPATH.'admin/');
				}
			}
			
			$data = where('id = %d',$newdata['id'])->get_row(strval($object['stack']));
			
			self::post_process_fields($object,$object,$data);
			
			if (!$is_new) {
				if (isset($object['archived']) && $has_changed)  {
					$versiondata = where('id = %d',$data->id)->translate(false)->get_row($object['stack']);
					$version_id = insert($object['stack'].'>_versions',$versiondata->to_array());
				}
				
				$search_index = array();
				$search_index_languages = array();
				self::post_process_searchable_fields($object,$object,$data,$search_index_languages,$search_index);
				
				if (!count($search_index_languages)) $search_index_languages[language()] = array();
				if (count($search_index)) {
					foreach ($search_index_languages as $language => $searchdata) {
						foreach ($search_index as $key => $index) {
							$search_index_languages[$language][$key] = $index;
						}
					}
				}
				foreach ($search_index_languages as $language => &$searchdata) {
					if (count($searchdata)) {
						$searchdata['object_id'] = $data->id;
						$searchdata['object_name'] = strval($object['stack']);
						$searchdata['_language'] = $language;
						
						if (isset($object['archived'])) {
							$searchdata['_version_id'] = $version_id;
							insert('_search_index',$searchdata);
						} else {
							$existing = where('object_id = %d',$searchdata['object_id'])->where('_language = %s',$searchdata['_language'])->where('object_name = %s',$searchdata['object_name'])->get_row('_search_index');
							if ($existing) where('id = %d',$existing->id)->update('_search_index',$searchdata);
							else insert('_search_index',$searchdata);
						}
					}
				}
			}
			
			if ($_SESSION['error']) {
				$error = $_SESSION['error'];
				unset($_SESSION['error']);
			}
			
			if (!$error && !$result) {
				if ($object->getName() == 'page') redirect($_SERVER['REQUEST_URI']);
				else redirect(preg_replace('/[^\/]+\/[^\/]+(\/|\/duplicate|\/duplicate\/)?$/', '', $_SERVER['REQUEST_URI']));
			}
		}
		
		$label = $object['label'];
		if (!$label) $label = ucwords($object['name']);
    	
    	$translatable = false;
    	foreach ($object->children() as $type => $field) {
    		if (in_array($type,array('string','text')) && isset($field['translatable']) && $field['translatable']) $translatable = true;
    	}
    	
    	$actionlabel = l(array(
    		'nl' => $label.(isset($data->id)?' bewerken':' toevoegen'),
    		'en'=>(isset($data->id)?'Edit ':'Add ').strtolower($label),
    		'fr'=>(isset($data->id)?'Modifier ':'Ajouter ').strtolower($label)
    	));
    	if (isset($object['editing_disabled'])) {
	    	$actionlabel = l(array(
	    		'nl' => $label.' bekijken',
	    		'en' => 'View '.strtolower($label),
	    		'fr' => 'Voir '.strtolower($label)
	    	));
    	}
    	
    	// Define title for breadcrumb
    	$navstacktitle = strval($label);
    	
    	// Determine if we can show something more specific as a title
    	// Does the object have a title format defined?
    	if (isset($object['format']) && isset($data->id)) {
    		// Replace placeholders with data
    		$displayvalue = strval($object['format']);
    		preg_match_all('/\[([a-z0-9\_]+)\]/is',$displayvalue,$matches,PREG_SET_ORDER);
    		foreach ($matches as $match) {
	    		$fieldname = strval($match[1]);
    	    	$displayvalue = str_ireplace($match[0],$data->$fieldname,$displayvalue);
    		}
    		$actionlabel = $displayvalue;
    		$navstacktitle = $displayvalue;
    	} else {
    		$titlefields = $object->xpath('string');
    		if ($titlefield = reset($titlefields)) {
    			$titlefield = strval($titlefield['name']);
    			if (isset($data->$titlefield) && $data->$titlefield) {
    				$actionlabel = $data->$titlefield;
    				$navstacktitle = $data->$titlefield;
    			}
    		}
    	}
    	
    	if (self::$duplicating) $navstacktitle = l(array('nl'=>'Dupliceren','en'=>'Duplicate'));
    	
		
		echo view('head',array(
			'pages' => self::get_pages(),
			'title' => $actionlabel,
			'navstacktitle' => $navstacktitle,
			'user' => $user,
			'site' => current_site()
		));
		
		$showtitle = true;
		if (count($object->children()) > 1){
			$first = $object->children();
			$first = $first[0];
			if ($first->getName() == 'header') $showtitle = false;
		}
		if ($showtitle) echo '<h2>'.$actionlabel.'</h2>';
		
		if (isset($object['archived']) && isset($data->id)) {
			echo '<a class="button versions" href="#"><img src="'.url(ADMINRESOURCES.'images/versions.png',false).'" width="16" height="14"/>'.l(array(
				'nl' => 'Vorige versies'
			)).'</a>';
			$versions = where('id = %d',$data->id)->order_by('version_id desc')->limit(11)->get($object['stack'].'>_versions');
			$versions->shift();
			echo '<div class="versionsmenu">';
			
			if ($versions->count()) {
				$users = FW4_User::get_users();
				foreach ($versions as $version) {
					$versionuser = isset($users[$version->edited_by_user])?$users[$version->edited_by_user]:'';
					if (is_object($versionuser) && isset($versionuser->name)) $versionuser = $versionuser->name;
					else if (is_object($versionuser) && isset($versionuser->lastname)) $versionuser = $versionuser->firstname.' '.$versionuser->lastname;
					else if (is_object($versionuser)) $versionuser = '';
					echo '<div class="version"><a href="_version/'.$version->version_id.'">'.strftime('%a %e %B %k:%M',$version->edited_at_date).($versionuser?' - '.$versionuser:'').'</a></div>';
				}
			} else {
				echo '<div class="note">'.l(array(
					'nl' => 'Er zijn geen vorige versies gekend.'
				)).'</div>';
			}
			
			echo '</div>';
		}
    	
    	echo '<form enctype="multipart/form-data" method="post" autocomplete="off">';
    	
    	if ($error) {
	    	echo '<div class="usernote error">'.$error.'</div>';
    	}
    	if ($success) {
	    	echo '<div class="usernote success">'.$success.'</div>';
    	}
    	
    	$iseditable = false;
    	$scripts = '';
    	$included_scripts = array();
    	foreach ($object->children() as $type => $field) {
			$user = FW4_User::get_user();
			if (isset($field['hidden'])) continue;
			if (isset($field['superadmin_only']) && $user['id'] != 0) continue;
    		if (self::print_field($field,$data,$object)) $iseditable = true;
    		else if ($type_obj = $types->get_type($type)) {
    			$type_obj->print_field($field,$data,$object);
    			if ($type != 'header') $iseditable = true;
    			if (!in_array($type,$included_scripts)) {
	    			$included_scripts[] = $type;
	    			$scripts .= $type_obj->get_scripts();
    			}
    		}
    	}
    	
    	echo '<div class="controls'.(self::$has_headers && ($iseditable || isset($object['editing_disabled']))?' with-headers':'').'"><input type="hidden" name="_starttime" value="'.time().'"/>';
    	if (isset($object['editing_disabled'])) {
    		if ($object->getName() == 'object') echo '<a class="button save" href="'.preg_replace('/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI']).'">'.l(array('nl'=>'Terug','fr'=>'Retourner','en'=>'Back')).'</a>';
    	} else {
	    	if ($iseditable && isset($data->id) && isset($object['duplicatable'])) echo '<a class="button right" href="duplicate/">'.l(array('nl'=>'Dupliceer','fr'=>'Duplicer','en'=>'Duplicate')).'</a>';
	    	if ($iseditable) echo '<a class="button save" href="#" onclick="$(\'form\').submit();return false;">'.l(array('nl'=>'Opslaan','fr'=>'Sauvegarder','en'=>'Save')).'</a>';
			if ($object->getName() == 'object') echo '<a class="button" href="'.(preg_match('/\/duplicate\/?$/is', $_SERVER['REQUEST_URI']) ? preg_replace('/\/duplicate\/?$/', '', $_SERVER['REQUEST_URI']) : preg_replace('/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI'])).'">'.l(array('nl'=>'Annuleren','fr'=>'Annuler','en'=>'Cancel')).'</a>';
    	}
    	echo '</div></form>';
		
		echo view("foot",array(
			'config' => $config,
			'scripts' => $scripts
		));
	}
	
	private static function post_process_fields($object,$container,&$data) {
		
		$types = FW4_Type_Manager::get_instance();
		
		foreach ($container->children() as $type => $field) {
		
			$name = strval($field['name']);
		
			if (!in_array($type,array('string','email','number','bool','object','page','date','timedate','text')) && $type_obj = $types->get_type($type)) $type_obj->edited($field,$data,$object);
			
			if (isset($field['translatable']) && isset($data->$name)) unset($data->$name);
			
			if ($type == 'fieldset') self::post_process_fields($object,$field,$data);
			
			if ($type == 'slug') {
				if (!isset($field['readonly']) && !isset($field['hidden'])) {
					$slugdata = array();
					if (!isset($field['format']) && isset($field['source'])) $field['format'] = '['.$field['source'].']';
					preg_match_all('/\[([a-z0-9\_]+)\]/is',strval($field['format']),$matches,PREG_SET_ORDER);
					$name = isset($field['name'])?strval($field['name']):'slug';
					$translatable = false;
					$slug_fields = array();
			    	foreach ($matches as $match) {
			    		$source = false;
			    		foreach ($object->children() as $child) {
							if (strval($child['name']) == $match[1]) $source = $child;
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
							$slugdata[$name.'_'.$code] = strval($field['format']);
						}
			    	} else $slugdata[$name] = strval($field['format']);
			    	foreach ($slug_fields as $slug_name => $slug_field) {
				    	if ($translatable) {
					    	foreach (languages() as $code => $lang) {
					    		$slugnamecode = $slug_name.'_'.$code;
					    		$namecode = $name.'_'.$code;
					    		$slugdata[$name.'_'.$code] = str_ireplace('['.$slug_name.']',$data->$slugnamecode,$slugdata[$namecode]);
					    	}
				    	} else {
				    		$slugdata[$name] = str_ireplace('['.$slug_name.']',$data->$slug_name,$slugdata[$name]);
				    	}
			    	}
			    	if ($translatable) {
				    	foreach (languages() as $code => $lang) {
				    		$namecode = $name.'_'.$code;
							$slugdata[$namecode] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', remove_accents($slugdata[$name.'_'.$code])) )));
							$data->$namecode = $slugdata[$namecode];
						}
						unset($data->$name);
			    	} else {
			    		$slugdata[$name] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', str_replace('-',' ',remove_accents($slugdata[$name]))) )));
			    		$data->$name = $slugdata[$name];
			    	}
			    	where('id = %d',$data->id)->update($object['stack'],$slugdata);
			    }
			}
		}
		
	}
	
	private static function post_process_searchable_fields($object,$container,&$data,&$search_index_languages,&$search_index) {
		
		$types = FW4_Type_Manager::get_instance();
		
    	$language = language();
    	$language_field = false;
    	foreach ($object->xpath('language') as $possible_language_field) {
	    	$language_field_name = strval($possible_language_field['name']);
	    	$language_field = $possible_language_field;
	    	$language = $data->$language_field_name;
    	}
		
		foreach ($container->children() as $type => $field) {
		
			$name = strval($field['name']);
		
			if (isset($field['searchable'])) {
				if ($type_obj = $types->get_type($type)) {
		    		if (method_exists($type_obj,'get_search_index')) {
			    		$fieldname = strval($field['searchable']);
			    		if (isset($field['translatable'])) {
		    				foreach ($languages as $key => $lang) {
		    					if (!isset($search_index_languages[$key])) $search_index_languages[$key] = array();
		    					$search_index_languages[$key][$fieldname] = $type_obj->get_search_index($field,$data,$object,$key);
		    				}
		    			} else {
		    				if (!isset($search_index_languages[$language])) $search_index_languages[$language] = array();
		    				$search_index_languages[$language][$fieldname] = $type_obj->get_search_index($field,$data,$object,$language);
		    			}
		    		}
	    		} else {
					$fieldname = strval($field['searchable']);
					if (isset($field['translatable'])) {
						foreach (languages() as $key => $lang) {
							$langfield = $name.'_'.$key;
							if (!isset($search_index_languages[$key])) $search_index_languages[$key] = array();
							if (isset($data->$langfield)) $search_index_languages[$key][$fieldname] = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $data->$langfield));
						}
					} else {
						if (isset($data->$name)) $search_index[$fieldname] = strip_tags(preg_replace('/\<br(\s*)?\/?\>/i', "\n", $data->$name));
					}
				}
				if (isset($data->id) && $language_field && !isset($field['translatable'])) where('object_id = %d',$data->id)->where('_language != %s',$language)->where('object_name = %s',strval($object['stack']))->delete('_search_index');
			}
			if ($type == 'language') {
				if (!isset($search_index_languages[$data->$name])) $search_index_languages[$data->$name] = array();
			}
			
			if ($type == 'fieldset') self::post_process_searchable_fields($object,$field,$data,$search_index_languages,$search_index);
		}
		
	}
	
	private static function print_version($object,$data=array()) {
		global $config;
		
    	$types = FW4_Type_Manager::get_instance();
    	
    	$user = FW4_User::get_user();
    			
		$label = $object['label'];
		if (!$label) $label = ucwords($object['name']);
		
		echo view('head',array(
			'pages' => self::get_pages(),
			'title' => 'Versie van '.strftime('%A %e %B %k:%M',$data->edited_at_date),
			'user' => $user,
			'site' => current_site()
		));
		
		echo '<h2>Versie van '.strftime('%A %e %B %k:%M',$data->edited_at_date).'</h2>';
    	
    	$iseditable = false;
    	$scripts = '';
    	$included_scripts = array();
    	$object['editing_disabled'] = 'editing_disabled';
    	$object['is_version'] = 'true';
    	foreach ($object->children() as $type => $field) {
			$user = FW4_User::get_user();
			if (isset($field['hidden'])) continue;
			if (isset($field['superadmin_only']) && $user->id != 0) continue;
			$field['is_viewing_version'] = true;
    		if (self::print_field($field,$data,$object)) $iseditable = true;
    		else if ($type_obj = $types->get_type($type)) {
    			$type_obj->print_field($field,$data,$object);
    			if ($type != 'header') $iseditable = true;
    			if (!in_array($type,$included_scripts)) {
	    			$included_scripts[] = $type;
	    			$scripts .= $type_obj->get_scripts();
    			}
    		}
    	}
    	
    	echo '<div class="controls'.(self::$has_headers && ($iseditable || isset($object['editing_disabled']))?' with-headers':'').'">';
    	echo '<a class="button save" href="'.preg_replace('/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI']).'">'.l(array('nl'=>'Terug','fr'=>'Retourner','en'=>'Back')).'</a>';
    	echo '<a class="button right" href="delete" onclick="return confirm(\''.l(array('nl'=>'Bent u zeker dat u deze versie wilt verwijderen?','fr'=>'Etes-vous s&ucirc;r de vouloir supprimer cette version?','en'=>'Are you sure you want to delete this version?')).'\');">'.l(array('nl' => 'Verwijderen','fr' => 'Supprimer','en' => 'Delete','de' => 'L&ouml;schen')).'</a>';
    	echo '<a class="button right" href="restore" onclick="return confirm(\''.l(array('nl'=>'Bent u zeker dat u deze versie wilt terugzetten?','fr'=>'Etes-vous s&ucirc;r de vouloir restaurer cette version?','en'=>'Are you sure you want to restore this version?')).'\');">'.l(array('nl' => 'Deze versie terugzetten','fr' => 'Restaurer cette version','en' => 'Restore this version')).'</a>';
    	echo '</div>';
		
		echo view("foot",array(
			'config' => $config,
			'scripts' => $scripts
		));
	}
	
	private static function prepare_posted_data($object,$data) {
		$newdata = array();
		
    	$types = FW4_Type_Manager::get_instance();
    	
    	foreach ($_POST as $key => $value) {
    		if (is_array($value)) {
	    		foreach ($value as $subkey => $subvalue) $value[$subkey] = stripslashes($subvalue);
	    		$_POST[$key] = $value;
    		} else $_POST[$key] = stripslashes($value);
    	}
    	
		foreach ($object->children() as $type => $field) {
			if (isset($field['readonly']) || isset($field['hidden'])) continue;
			switch ($type) {
				case 'string':
				case 'email':
					if (isset($field['translatable']) && $field['translatable']) {
						foreach (languages() as $key => $lang) {
							if (isset($_POST[strval($field['name']).'_'.$key])) $newdata[strval($field['name']).'_'.$key] = trim($_POST[strval($field['name']).'_'.$key]);
						}
					} else if (isset($_POST[strval($field['name'])])) $newdata[strval($field['name'])] = trim($_POST[strval($field['name'])]);
					break;
				case 'text':
					if (isset($field['translatable']) && $field['translatable']) {
						foreach (languages() as $key => $lang) {
							if (isset($_POST[strval($field['name']).'_'.$key])) $newdata[strval($field['name']).'_'.$key] = preg_replace('/^(?:<\s*br\s*\/?\s*>|\s)*(.*?)(?:<\s*br\s*\/?\s*>|\s)*$/is','$1', stripslashes(self::placeholder_encode($_POST[strval($field['name']).'_'.$key],$key)) );
						}
					} else if (isset($_POST[strval($field['name'])])) $newdata[strval($field['name'])] = preg_replace('/^(?:<\s*br\s*\/?\s*>|\s)*(.*?)(?:<\s*br\s*\/?\s*>|\s)*$/is','$1', self::placeholder_encode($_POST[strval($field['name'])]));
					break;
				case 'password':
					if (trim($_POST[strval($field['name'])])) $newdata[strval($field['name'])] = FW4_User::hash_password($_POST[strval($field['name'])]);
					break;
				case 'number':
					if ($_POST[strval($field['name'])] === '') $newdata[strval($field['name'])] = null;
					else $newdata[strval($field['name'])] = intval($_POST[strval($field['name'])]);
					break;
				case 'float':
					$newdata[strval($field['name'])] = str_replace(',','.', preg_replace('/[^0-9\,]/s','', $_POST[strval($field['name'])]));
					break;
				case 'bool':
					$user = FW4_User::get_user();
					if (isset($field['require']) && $user['id'] !== 0) {
						$require_fields = explode('.',$field['require']);
						$require_field = $user;
						foreach ($require_fields as $current_field) {
							if (isset($require_field[$current_field]) && $require_field[$current_field]) $require_field = $require_field[$current_field];
							else {
								$require_field = false;
								break;
							}
						}
						if (!$require_field) continue;
					}
					$newdata[strval($field['name'])] = isset($_POST[strval($field['name'])])?1:0;
					break;
				case 'date':
					if (isset($_POST[strval($field['name'])]) && $_POST[strval($field['name'])]) {
						list($d, $m, $y) = explode("/", $_POST[strval($field['name'])]);
						$newdata[strval($field['name'])] = mktime(0,0,0,$m,$d,$y);
					}
					break;
				case 'timedate':
					if (isset($_POST[strval($field['name'])])) {
						list($date,$time) = explode(" ", $_POST[strval($field['name'])]);
						list($d, $m, $y) = explode("/", $date);
						if (!$time) $time = '00:00';
						list($h, $i) = explode(":", $time);
						$newdata[strval($field['name'])] = mktime($h,$i,0,$m,$d,$y);
					}
					break;
				case 'fieldset':
					$newdata = array_merge($newdata,self::prepare_posted_data($field,$data));
					break;
				case 'object':
				case 'page':
				case 'date':
					break;
				default:
					if ($type_obj = $types->get_type($type)) {
						if (isset($data->id)) $newdata = $type_obj->update($newdata,$field,$_POST,$data,$object);
						else $newdata = $type_obj->insert($newdata,$field,$_POST,$data,$object);
					}
					break;
			}
		}
		return $newdata;
	}
	
	private static function process_rightless_values($object) {
		$newdata = array();
		
    	$types = FW4_Type_Manager::get_instance();
    	
		foreach ($object->children() as $type => $field) {
		
			if ($type == 'fieldset') {
				$newdata = array_merge($newdata,self::process_rightless_values($field));
			} else {
				$user = FW4_User::get_user();
				if (isset($field['require']) && $user['id'] !== 0) {
					$require_fields = explode('.',$field['require']);
					$require_field = $user;
					foreach ($require_fields as $current_field) {
						if (isset($require_field[$current_field]) && $require_field[$current_field]) $require_field = $require_field[$current_field];
						else {
							$require_field = false;
							break;
						}
					}
					if (!$require_field) {
						if (isset($field['rightless_value'])) $newdata[strval($field['name'])] = intval($field['rightless_value']);
						continue;
					}
				}
			}
			
		}
		
		return $newdata;
	}
	
	private static function export_object($object,$data) {
		
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private',false);
		header('Content-Type: application/force-download; charset=utf-8');
		header('Content-Disposition: attachment; filename="'.strtolower(strval($object['label'])).'.csv"');
		header('Content-Transfer-Encoding: binary');
	
		$fieldnames = $values = $label_arrays = array();
		
		$types = FW4_Type_Manager::get_instance();
		
		foreach ($object->children() as $type => $field) {
			if (in_array($type,array('string','email','number','text','bool','date','timedate'))) {
				
			} else if ($type_obj = $types->get_type($type)) {
				if (!method_exists($type_obj, 'export')) continue;
			} else if ($type == 'fieldset') {
				
				foreach ($field->children() as $subtype => $subfield) {
					if (in_array($subtype,array('string','email','number','text','bool','date','timedate'))) {
						
					} else if ($type_obj = $types->get_type($subtype)) {
						if (!method_exists($type_obj, 'export')) continue;
					} else {
						continue;
					}
					
					if (isset($subfield['label']) && !isset($subfield['hidden']) && !isset($subfield['export'])) $fieldnames[strval($subfield['name'])] = html_entity_decode(strval($subfield['label']));
				}
				
			} else {
				continue;
			}
			
			if (isset($field['label']) && !isset($field['hidden']) && !isset($field['export'])) $fieldnames[strval($field['name'])] = html_entity_decode(strval($field['label']));
			else if (!isset($field['hidden']) && !isset($field['export'])) $fieldnames[strval($field['name'])] = false;
		}
		
		foreach ($data as $row) {
			$value = array();

			foreach ($object->children() as $type => $field) {
			
				if ($type == 'fieldset') {
					foreach ($field->children() as $subtype => $subfield) {
						if (!isset($subfield['label']) || isset($subfield['hidden']) || isset($subfield['export'])) continue;
						self::export_field($subtype,$subfield,$row,$value,$object,$label_arrays);
					}
					continue;
				}
			
				if (isset($field['hidden']) || isset($field['export'])) continue;
			
				self::export_field($type,$field,$row,$value,$object,$label_arrays);
			}
			$values[] = $value;
		}
		
		$newfieldnames = array();
		foreach ($fieldnames as $fieldname => $fieldlabel) {
			if (isset($label_arrays[$fieldname])) {
				foreach ($label_arrays[$fieldname] as $newfieldlabel) {
					$newfieldnames[$fieldname.'-'.$newfieldlabel] = $newfieldlabel;
				}
			} else if ($fieldlabel !== false) $newfieldnames[$fieldname] = $fieldlabel;
		}
		
		$newfieldnames = array_values($newfieldnames);
		if (count($newfieldnames) > 0 && substr($newfieldnames[0],0,2) == 'ID') $newfieldnames[0] = ' '.$newfieldnames[0];
		
		echo self::array_to_csv($newfieldnames)."\r\n";
        foreach ($values as $value) {
        
        	$newvalue = array();
        	foreach ($value as $fieldname => $fieldvalue) {
	        	if (isset($label_arrays[$fieldname])) {
		        	foreach ($label_arrays[$fieldname] as $label) {
			        	$newvalue[$fieldname.'-'.$label] = isset($fieldvalue[$label])?$fieldvalue[$label]:'';
		        	}
	        	} else $newvalue[$fieldname] = $fieldvalue;
        	}
        	
        	echo self::array_to_csv($newvalue)."\r\n";
        }
		
	}
	
	private static function export_field($type,$field,$row,&$value,$object,&$label_arrays) {
		$types = FW4_Type_Manager::get_instance();
		$fieldname = strval($field['name']);
		switch ($type) {
			case 'string':
			case 'email':
			case 'text':
				if (isset($field['translatable']) && $field['translatable']) {					
					foreach (languages() as $key => $lang) {
						$fieldname_lang = $fieldname.'_'.$key;
						$value[$fieldname_lang] = decode(html_entity_decode($row->$fieldname_lang));
					}
				} else $value[] = decode(html_entity_decode($row->$fieldname));
				break;
			case 'number':
				$value[$fieldname] = $row->$fieldname;
				break;
			case 'bool':
				$value[$fieldname] = $row->$fieldname?l(array('nl'=>'Ja','fr'=>'Oui','en'=>'Yes')):l(array('nl'=>'Nee','fr'=>'Non','en'=>'No'));
				break;
			case 'date':
				if ($row->$fieldname) $value[$fieldname] = date('d/m/Y',$row->$fieldname);
				else $value[$fieldname] = '';
				break;
			case 'timedate':
				if ($row->$fieldname) $value[$fieldname] = date('d/m/Y H:i',$row->$fieldname);
				else $value[$fieldname] = '';
				break;
			default:
				if ($type_obj = $types->get_type($type)) {
					if (method_exists($type_obj, 'export')) {
						$typeexport = call_user_func_array(array($type_obj,'export'), array($row,$field,$object));
						if (is_array($typeexport)){
							if (!isset($label_arrays[$fieldname])) $label_arrays[$fieldname] = array();
							foreach ($typeexport as $typekey => $typevalue) {
								if (!in_array($typekey,$label_arrays[$fieldname])) $label_arrays[$fieldname][] = $typekey;
							}
						}
						$value[$fieldname] = $typeexport;
					}
				}
				break;
		}
	}
	
	private static function array_to_csv(array &$fields, $delimiter = ';', $enclosure = '"', $encloseAll = false, $nullToMysqlNull = false ) {
	    $delimiter_esc = preg_quote($delimiter, '/');
	    $enclosure_esc = preg_quote($enclosure, '/');
	
	    $output = array();
	    foreach ( $fields as $field ) {
	        if ($field === null && $nullToMysqlNull) {
	            $output[] = 'NULL';
	            continue;
	        }
	
	        // Enclose fields containing $delimiter, $enclosure or whitespace
	        if ( $encloseAll || preg_match( "/(?:${delimiter_esc}|${enclosure_esc}|\s)/", $field ) ) {
	            $output[] = $enclosure . str_replace($enclosure, $enclosure . $enclosure, $field) . $enclosure;
	        }
	        else {
	            $output[] = $field;
	        }
	    }
	
	    return implode( $delimiter, $output );
	}
	
	private static function export_list($name,$data) {
		
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: private',false);
		header('Content-Type: application/force-download');
		header('Content-Disposition: attachment; filename="'.$name.'.csv"');
		header('Content-Transfer-Encoding: binary');
		        
        echo(implode("\t", array_keys(reset($data)))).'
';
        foreach ($data as $row) {
        	echo(implode("\t", $row)).'
';
        }
		
	}
	
	private static function print_field($field,$data,$structure) {
		
		if (in_array($field->getName(),array('div','span','img'))) {
			echo '<div class="usernote">'.strval($field->asXML()).'</div>';
			return false;
		}
		
		$user = FW4_User::get_user();
		
		if (isset($field['require']) && $user->id !== 0) {
			$require_fields = explode('.',$field['require']);
			$require_field = $user;
			foreach ($require_fields as $current_field) {
				if (isset($require_field[$current_field]) && $require_field[$current_field]) $require_field = $require_field[$current_field];
				else {
					$require_field = false;
					break;
				}
			}
			if (!$require_field && !isset($structure['is_version'])) return false;
		}
		
		if (isset($field['hide_on_recursive']) && self::$recursive_levels > 0) {
			return false;
		}
		
		$types = FW4_Type_Manager::get_instance();
		
		$fieldname = strval($field['name']);
		
		switch ($field->getName()) {
			case 'object':
				if (isset($data->id) && !isset($structure['is_version'])) {
					$objectquery = where((isset($structure['orig_name'])?$structure['orig_name']:$structure['name']).'_id = %d',$data->id);
										
			    	foreach ($field->children() as $type => $subfield) {
			    	
			    		if ($type == 'object') $objectquery->including(strval($subfield['name']));
			    		if ($type == 'summary' && isset($subfield['needs'])) {
			    			foreach (explode(',',strval($subfield['needs'])) as $needs) $objectquery->including(trim($needs));
			    		}
						
						if ($type == 'recursive') {
							$objectquery->where('parent_id IS NULL');
						}
						
			    	}
					
					$objectamount = $objectquery->get($structure['stack'].'>'.$field['name'])->rowCount();
					$objectdata = $objectquery->limit(50)->get($structure['stack'].'>'.$field['name']);
					$field['stack'] = $structure['stack'].'>'.$field['name'];
					
					self::print_object_list($field,$objectdata,$objectamount,$data->id);
				}
				return false;
			case 'recursive':
				if (isset($structure['editing_disabled'])) return false;
				if (isset($field['levels']) && $field['levels'] <= self::$recursive_levels) return false;
				if (isset($data->id)) {
					$recursive_structure = clone $structure;
					if (isset($field['title'])) $recursive_structure['title'] = $field['title'];
					if (isset($field['label'])) $recursive_structure['label'] = $field['label'];
					if (isset($field['name'])) {
						$recursive_structure['orig_name'] = $recursive_structure['name'];
						$recursive_structure['name'] = $field['name'];
					}
					$objectquery = where('parent_id = %d',$data->id)->limit(50);
					$objectdata = $objectquery->get($structure['stack']);
					$objectamount = $objectquery->count_rows($structure['stack']);
					self::print_object_list($recursive_structure,$objectdata,$objectamount,$data->id,true,true,true);
				}
				return false;
			case 'list':
				
				if (isset($field['datasource'])) {
					if (function_exists('datasource_'.$field['datasource'])) {
						$listdata = call_user_func('datasource_'.$field['datasource'],false);
						if (is_array($listdata)) {
							echo View_Loader::get_instance()->load("data_list",array(
								'data' => $listdata,
								'export' => isset($field['exportable']),
								'datasource' => isset($field['datasource'])?strval($field['datasource']):false,
								'object' => isset($field['object'])?strval($field['object']):false
							));
						}
					}
				}
				
				if (isset($data->id)) {
					$object = FW4_Structure::get_object_structure(strval($field['object']));
					if (!$object) return false;
					foreach ($field->attributes() as $key => $val) {
						$object->addAttribute($key,$val);
					}
					$query = new Query();
					if (isset($field['order_field'])) $query->order_by(strval($field['order_field']));
					if (isset($field['where'])) {
						foreach (explode(',', strval($field['where'])) as $item) {
							$item = explode(':',$item);
							$query->where($item[0].' = %s',$item[1]);
						}
					}
					$allow_edit = true;
					if (isset($field['allow_edit']) && ($field['allow_edit'] == 'false' || !$field['allow_edit'])) $allow_edit = false;
					$rows = $query->load_children(true)->get(strval($field['object']));
					self::print_object_list($object,$rows,count($rows),$data->id,true,$allow_edit);
				}
						
						
				return false;
			case 'string':
			case 'email':
				if ((isset($field['readonly']) && isset($data->id)) || isset($structure['editing_disabled'])) {
					echo '<div class="input"><label for="'.$field['name'].'" class="for-input">'.$field['label'].'</label><div class="value">'.(isset($data->$fieldname)&&$data->$fieldname?self::placeholder_decode($data->$fieldname,$field):'-').'</div></div>';
				} else if (isset($field['translatable']) && $field['translatable']) {
					echo '<div class="'.(FW4_Admin::$in_fieldset?'field':'input').'"><label'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label> ';
					foreach (languages() as $key => $lang) {
						$fieldlang = $fieldname.'_'.$key;
						echo '<div class="language"><input type="text" class="'.(count(languages())>1?'with_lang_label lowmargin':'').(isset($field['required']) && $field['required']?' required':'').''.(isset($field['wide'])?' wide':'').'" name="'.$field['name'].'_'.$key.'" value="'.htmlentities_all(isset($data->$fieldlang)?$data->$fieldlang:'').'" maxlength="'.(isset($field['length'])?$field['length']:150).'"'.(isset($field['visible_condition'])?' data-visible-condition="'.$field['visible_condition'].'"':'').' />'.(count(languages())>1?'<span class="lang_label">'.strtoupper($key).'</span>':'').'</div>';
					}
					echo '<br/></div>';
				} else echo '<div class="'.(FW4_Admin::$in_fieldset?'field':'input').'"><label for="'.$field['name'].'" class="for-input">'.$field['label'].'</label> <input class="'.(isset($field['required']) && $field['required']?'required':'').''.(isset($field['wide'])?' wide':'').'" type="text" id="input-'.$field['name'].'" name="'.$field['name'].'" value="'.htmlspecialchars(isset($data->$fieldname)?$data->$fieldname:'').'" maxlength="'.(isset($field['length'])?$field['length']:150).'"'.(isset($field['visible_condition'])?' data-visible-condition="'.$field['visible_condition'].'"':'').' /></div>';
				return true;
			case 'number':
				if ((isset($field['readonly']) && $field['readonly']) || isset($structure['editing_disabled'])) {
					if (isset($data->id)) echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label> <div class="value">'.(isset($data->$fieldname)?$data->$fieldname:'').'</div></div>';
				} else echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label> <input class="number'.(isset($field['required']) && $field['required']?' required':'').'" type="text" name="'.$field['name'].'" value="'.(isset($data->$fieldname)?$data->$fieldname:'').'" maxlength="'.(isset($field['length'])?$field['length']:20).'" /></div>';
				return true;
			case 'float':
				if ((isset($field['readonly']) && $field['readonly']) || isset($structure['editing_disabled'])) {
					if (isset($data->id)) echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label> <div class="value">'.(isset($data->$fieldname)?trim(trim(number_format($data->$fieldname,2,',','.'),'0'),','):'').'</div></div>';
				} else echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label> <input class="float'.(isset($field['required']) && $field['required']?' required':'').'" type="text" name="'.$field['name'].'" value="'.(isset($data->$fieldname)?trim(trim(number_format($data->$fieldname,2,',','.'),'0'),','):'').'" maxlength="'.(isset($field['length'])?$field['length']:20).'" /></div>';
				return true;
			case 'password':
				if ((isset($field['readonly']) && isset($data->id)) || isset($structure['editing_disabled'])) {
					return false;
				} else echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label> <input type="password"'.(isset($field['required']) && $field['required'] && !isset($data->id)?' class="required"':'').' name="'.$field['name'].'" maxlength="'.(isset($field['length'])?$field['length']:150).'" /></div>';
				return true;
			case 'bool':
				if (self::$in_fieldset) echo '<div class="field">';
				else echo '<fieldset>';
				
				if ((isset($field['readonly']) && isset($data->id)) || isset($structure['editing_disabled'])) {
					echo $field['label'].': <strong>'.((isset($data->$fieldname) && $data->$fieldname==1) || (!$data->id && isset($field['default']))?l(array(
						'nl' => 'Ja',
						'fr' => 'Oui',
						'en' => 'Yes'
					)):l(array(
						'nl' => 'Nee',
						'fr' => 'Non',
						'en' => 'No'
					))).'</strong>';
				} else {
					echo '<input id="input-'.$field['name'].'"'.(isset($field['enabled_condition'])?' data-enabled-condition="'.$field['enabled_condition'].'"':'').''.(isset($field['visible_condition'])?' data-visible-condition="'.$field['visible_condition'].'"':'').' type="checkbox" name="'.$field['name'].'" value="1" '.((isset($data->$fieldname) && $data->$fieldname==1) || ((!isset($data->id) || !$data->id) && isset($field['default']))?'checked="checked"':'').' /><label for="input-'.$field['name'].'">'.$field['label'].'</label>';
				}
				
				if (self::$in_fieldset) echo '</div>';
				else echo '</fieldset>';
				return true;
			case 'date':
				if (isset($field['default_today']) && $field['default_today'] && !isset($data->id)) $data->$fieldname = time();
				else if (!isset($data->$fieldname)) $data->$fieldname = '';
				
				echo '<div class="'.(FW4_Admin::$in_fieldset?'field':'input').'"><label class="for-input">'.$field['label'].'</label>';
				
				if ((isset($field['readonly']) && $field['readonly']) || isset($structure['editing_disabled'])) echo '<div class="value">'.(isset($data->$fieldname)&&is_numeric($data->$fieldname)&&$data->$fieldname?date('d/m/Y',$data->$fieldname):($data->$fieldname?$data->$fieldname:'Nooit')).'</div>';
				else echo '<input type="text" name="'.$field['name'].'" style="width:100px" class="date'.(isset($field['required']) && $field['required']?' required':'').'" size="20" id="input-'.$field['name'].'" value="'.(isset($data->$fieldname)&&is_numeric($data->$fieldname)?date('d/m/Y',$data->$fieldname):$data->$fieldname).'"'.(isset($field['visible_condition'])?' data-visible-condition="'.$field['visible_condition'].'"':'').''.(isset($field['limit'])?' data-limit="'.$field['limit'].'"':'').'/>';
				
				echo '</div>';
				
				return true;
			case 'timedate':
				$date = new DateTime(null, new DateTimeZone('Etc/GMT+2'));
				if (isset($field['default_today']) && $field['default_today'] && !isset($data->$fieldname)) $data->$fieldname = $date->getTimestamp();
				else if (!isset($data->$fieldname)) $data->$fieldname = '';
				
				if (isset($data->$fieldname) && is_numeric($data->$fieldname) && $data->$fieldname > 0) {
					if (strftime('%H:%M',$data->$fieldname) == '00:00') $value = strftime('%d/%m/%Y',$data->$fieldname);
					else $value = strftime('%d/%m/%Y %H:%M',$data->$fieldname);
				} else $value = '';
				
				echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label>';
				if ((isset($field['readonly']) && $field['readonly']) || isset($structure['editing_disabled'])) echo '<div class="value">'.($value?$value:'Nooit').'</div>';
				else  echo '<input type="text" name="'.$field['name'].'" style="width:150px" class="timedate'.(isset($field['required']) && $field['required']?' required':'').'" size="20" value="'.$value.'" data-limit="'.(isset($field['limit'])?$field['limit'].'"':'').'/>';
				
				echo '</div>';
				return true;
			case 'text':
				$placeholdernames = array();
				$placeholderlabels = array();
				$placeholdericons = array();
				foreach ($field->xpath('//placeholder') as $child) {
					$placeholdernames[] = strval($child['name']);
					$placeholderlabels[] = isset($child['label']) ? $child['label'] : ucfirst($child['name']);
					$placeholdericons[] = isset($child['icon']) ? $child['icon'] : url(ADMINRESOURCES.'images/icon-placeholder.png');
				}
				if ((isset($field['readonly']) && $field['readonly']) || isset($structure['editing_disabled'])) {
					echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label><div class="value">'.(isset($data->$fieldname)&&$data->$fieldname?self::placeholder_decode($data->$fieldname,$field):'-').'</div></div>';
				} else if (isset($field['translatable']) && $field['translatable']) {
					echo '<div class="input langswitch"><label'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label><div class="translate-container">';
					if (count(languages())>1) {
						echo '<select class="langswitch">';
						foreach (languages() as $key => $lang) echo '<option value="'.$key.'">'.$lang.'</option> ';
						echo '</select>';
					}
					foreach (languages() as $key => $lang) {
						$fieldlang = $fieldname.'_'.$key;
						echo '<div class="translatable editor lang_'.$key.($key==language()?'':' hidden').'"><textarea class="'.(isset($field['required']) && $field['required']?' required':'').(isset($field['controls']) && $field['controls']?' '.$field['controls']:'').(isset($field['size']) && $field['size']?' '.$field['size']:'').'" name="'.$field['name'].'_'.$key.'" rows="5"'.(isset($field['toolbar']) && $field['toolbar']?' data-controls="'.$field['toolbar'].'"':'');
						if (count($placeholdernames)) echo ' data-placeholder-names="'.e(implode(',', $placeholdernames)).'"';
						if (count($placeholderlabels)) echo ' data-placeholder-labels="'.e(implode(',', $placeholderlabels)).'"';
						if (count($placeholdericons)) echo ' data-placeholder-icons="'.e(implode(',', $placeholdericons)).'"';
						echo '>'.(isset($data->$fieldlang)?self::placeholder_decode($data->$fieldlang,$field):'').'</textarea></div>';
					}
					echo '<div class="textarea-loader"></div></div></div>';
				} else {
					echo '<div class="input"><label for="'.$field['name'].'"'.(isset($field['invalid']) && $field['invalid']?' class="invalid"':'').'>'.$field['label'].'</label> <textarea name="'.$field['name'].'" rows="5" class="'.(isset($field['required']) && $field['required']?' required':'').(isset($field['controls']) && $field['controls']?' '.$field['controls']:'').(isset($field['size']) && $field['size']?' '.$field['size']:'').'"'.(isset($field['toolbar']) && $field['toolbar']?' data-controls="'.$field['toolbar'].'"':'');
					if (count($placeholdernames)) echo ' data-placeholder-names="'.e(implode(',', $placeholdernames)).'"';
					if (count($placeholderlabels)) echo ' data-placeholder-labels="'.e(implode(',', $placeholderlabels)).'"';
					if (count($placeholdericons)) echo ' data-placeholder-icons="'.e(implode(',', $placeholdericons)).'"';
					echo '>'.(isset($data->$fieldname)?self::placeholder_decode($data->$fieldname,$field):'').'</textarea><div class="textarea-loader"></div></div>';
				}
				return true;
			case 'fieldset':
				$iseditable = false;
				self::$in_fieldset = true;
				echo '<fieldset>';
		    	foreach ($field->children() as $type => $subfield) {
					$user = FW4_User::get_user();
					if (isset($field['hidden'])) continue;
					if (isset($subfield['superadmin_only']) && $user['id'] != 0) continue;
		    		if (self::print_field($subfield,$data,$structure)) $iseditable = true;
		    		else if ($type_obj = $types->get_type($type)) {
		    			$type_obj->print_field($subfield,$data,$field);
		    			if ($type != 'header') $iseditable = true;
		    		}
		    	}
				echo '</fieldset>';
				self::$in_fieldset = false;
				return $iseditable;
		}
		return false;
	}
	
	public static function print_object_list($field,$data,$amount,$parent_id,$controls=true,$allow_edit=true,$recursive=false) {
		$details = self::object_list_details($field);
		echo view("object_list",array(
			"field" => $field,
			"data" => $data,
			"amount" => $amount,
			'controls' => $controls,
			'allow_edit' => $allow_edit,
			'recursive' => $recursive,
			'object' => $field,
			'shownfields' => $details['shownfields'],
			'headers' => $details['headers'],
			'searchable' => $details['searchable'],
			'filters' => $details['filters'],
			'delete_limits' => $details['delete_limits'],
			'current_filter' => isset($_SESSION['filter_'.$field['stack']])?$_SESSION['filter_'.$field['stack']]:array(),
			'page' => isset($_SESSION['filter_'.$field['stack']]['page'])?$_SESSION['filter_'.$field['stack']]['page']:1,
			'recursive_name' => $details['recursive_name'],
			'parent_id' => $parent_id
		));
	}
	
	private static function object_list_details($field) {
		$shownfields = array();
		$headers = array();
		$typemanager = FW4_Type_Manager::get_instance();
		$filters = array();
		$searchable = false;
		$delete_limits = array();
		$i = 0;
		$user = FW4_User::get_user();
		$recursive_name = false;
		foreach ($field->children() as $subfield) {
			
			if (isset($subfield['hidden'])) continue;
			
			$i++;
			
			$dolimit = true;
			if (isset($subfield['limit_condition'])) {
				$invert = false;
				$condition = $subfield['limit_condition'];
				if (substr($condition,0,1) == '!') {
					$invert = true;
					$condition = substr($condition,1);
				}
				$limit_fields = explode('.',$condition);
				$limit_field = $user;
				foreach ($limit_fields as $current_field) {
					if (isset($limit_field->$current_field)) $limit_field = $limit_field->$current_field;
					else if (isset($limit_field->$current_field)) {
						$limit_field = false;
						break;
					} else {
						$limit_field = true;
						break;
					}
				}
				if ($limit_field) $limit_field = true;
				$dolimit = $invert?!$limit_field:$limit_field;
			}
						
			if (isset($subfield['limit']) && $user->id != 0) {
				if ($dolimit) continue;
			}
			if (isset($subfield['limit_delete']) && $user->id != 0) {
				if ($dolimit) {
					$limit_fields = explode('.',$subfield['limit_delete']);
					$limit_field = $user;
					foreach ($limit_fields as $current_field) {
						if (isset($limit_field[$current_field])) $limit_field = $limit_field[$current_field];
						else {
							$limit_field = false;
							break;
						}
					}
					$delete_limits[strval($subfield['name'])] = $limit_field;
				}
			}
			
			if (isset($subfield['summary_label'])) $subfield['label'] = $subfield['summary_label'];
			
			if ($subfield->getName() == "string" || $subfield->getName() == "email" ):
				if (isset($subfield['filterable'])) $searchable = true;
				if (count($shownfields) > 6 || (isset($subfield['summary']) && $subfield['summary'] == 'false')) continue;
				$headers[] = isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name']));
				$shownfields[strval($subfield['name'])] = $subfield;
			elseif ($subfield->getName() == "fieldset"):
				$fielddetails = self::object_list_details($subfield);
				$shownfields = array_merge($shownfields,$fielddetails['shownfields']);
				$headers = array_merge($headers,$fielddetails['headers']);
				$searchable = ($searchable || $fielddetails['searchable']);
				$filters = array_merge($filters,$fielddetails['filters']);
			elseif ($subfield->getName() == "date" || $subfield->getName() == "timedate" || $subfield->getName() == "bool" ):
				if (isset($subfield['filterable'])) {
					if ($subfield->getName() == "bool") {
						$filters[strval($subfield['name'])] = array(
							'label' => isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name'])),
							'values' => array(
								1 => l(array('nl'=>'Ja','fr'=>'Oui','en'=>'Yes')),
								0 => l(array('nl'=>'Nee','fr'=>'Non','en'=>'No'))
							)
						);
					} else {
						$filters[strval($subfield['name'])] = array(
							'label' => isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name'])),
							'values' => array(
								1 => l(array('nl'=>'Vandaag','fr'=>'Vandaag','en'=>'Vandaag')),
								2 => l(array('nl'=>'Gisteren','fr'=>'Gisteren','en'=>'Gisteren')),
								3 => l(array('nl'=>'Voorbije 7 dagen','fr'=>'Voorbije 7 dagen','en'=>'Voorbije 7 dagen')),
								6 => l(array('nl'=>'Deze maand','fr'=>'Deze maand','en'=>'Deze maand')),
								9 => ucfirst(strftime('%B',mktime(0,0,0,date("n")-1,1))),
								10 => ucfirst(strftime('%B',mktime(0,0,0,date("n")-2,1))),
								11 => ucfirst(strftime('%B',mktime(0,0,0,date("n")-3,1))),
								4 => l(array('nl'=>'Voorbije 30 dagen','fr'=>'Voorbije 30 dagen','en'=>'Voorbije 30 dagen')),
								7 => l(array('nl'=>'Dit kwartaal','fr'=>'Dit kwartaal','en'=>'Dit kwartaal')),
								8 => l(array('nl'=>'Vorig kwartaal','fr'=>'Vorig kwartaal','en'=>'Vorig kwartaal')),
								5 => l(array('nl'=>'Voorbije jaar','fr'=>'Voorbije jaar','en'=>'Voorbije jaar')),
							)
						);
					}
				}
				if (count($shownfields) > 6 || (isset($subfield['summary']) && $subfield['summary'] == 'false')) continue;
				$headers[] = isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name']));
				$shownfields[strval($subfield['name'])] = $subfield;
			elseif ($subfield->getName() == "filter" ):
				$values = array();
				foreach ($subfield->children() as $child){
			    	$value = isset($child['value'])?strval($child['value']):strval($child);
			    	$values[$value] = strval($child);
		    	}
				$filters[strval($subfield['name'])] = array(
					'label' => isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name'])),
					'values' => $values
				);
			elseif ($subfield->getName() == "number" ):
				if (count($shownfields) > 6 || (isset($subfield['summary']) && $subfield['summary'] == 'false')) continue;
				$headers[] = isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name']));
				$shownfields[strval($subfield['name'])] = $subfield;
			elseif ($subfield->getName() == "float" ):
				if (count($shownfields) > 6 || (isset($subfield['summary']) && $subfield['summary'] == 'false')) continue;
				$headers[] = isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name']));
				$shownfields[strval($subfield['name'])] = $subfield;
			elseif ($subfield->getName() == "text"):
				if (isset($subfield['filterable'])) $searchable = true;
				
				if (count($shownfields) > 6 || !isset($subfield['summary']) || ($subfield['summary'] != 'excerpt' && $subfield['summary'] != 'bool')) continue;
				$headers[] = isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name']));
				$shownfields[strval($subfield['name'])] = $subfield;
			elseif ($subfield->getName() == "recursive"):
				$recursive_name = strval($subfield['name']);
			elseif ($type = $typemanager->get_type(strval($subfield->getName()))):
				if ($subfield->getName() == "choice" && isset($subfield['filterable'])) {
					$parent_item = self::$parent_item;
					self::$parent_item = self::$current_item;
					$values = array();
					if (isset($subfield['source'])) {
						if (!$structure = FW4_Structure::get_object_structure(strval($subfield['source']),false)) continue;
						$titlefields = $structure->xpath('string');
						if (!($titlefield = reset($titlefields))) continue;
						$titlefield = strval($titlefield['name']);
					    foreach ($type->get_source_rows(strval($subfield['source']),$subfield) as $child): 
					    	$value = $child->id;
					    	if (isset($subfield['format'])) {
					    		$displayvalue = $subfield['format'];
						    	preg_match_all('/\[([a-z0-9\_]+)\]/is',$subfield['format'],$matches,PREG_SET_ORDER);
						    	foreach ($matches as $match) {
							    	$match_name = $match[1];
							    	$displayvalue = str_ireplace($match[0],$child->$match_name,$displayvalue);
						    	}
						    	$values[$value] = $displayvalue;
					    	} else {
								$values[$value] = $child->$titlefield;
					    	}
				    	endforeach;
				    } else {
					    foreach ($subfield->children() as $child): 
					    	$value = isset($child['value'])?strval($child['value']):strval($child);
					    	$values[$value] = strval($child);
				    	endforeach;
				    }
					$filters[strval($subfield['name'])] = array(
						'label' => isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name'])),
						'values' => $values
					);
					self::$parent_item = $parent_item;
				}
				if (!method_exists($type,'summary') || count($shownfields) > 6 || (isset($subfield['summary']) && $subfield['summary'] == 'false')) continue;
				$headers[] = isset($subfield['label'])?$subfield['label']:ucwords(strval($subfield['name']));
				$shownfields[strval($subfield['name'])] = $subfield;
			endif;
		}
		return array(
			'shownfields' => $shownfields,
			'headers' => $headers,
			'searchable' => $searchable,
			'filters' => $filters,
			'delete_limits' => $delete_limits,
			'recursive_name' => $recursive_name
		);
	}
	
	public static function get_pages() {
		$pages = array();
		$user = FW4_User::get_user();
		foreach (FW4_Structure::get_pages() as $page) {
			$attributes = $page->attributes();
			if (isset($attributes['superadmin_only']) && $user->id !== 0) continue;
			if (isset($attributes['require']) && $user->id !== 0) {
				$require_fields = explode('.',$attributes['require']);
				$require_field = $user;
				foreach ($require_fields as $current_field) {
					if ($require_field->$current_field) $require_field = $require_field->$current_field;
					else {
						$require_field = false;
						break;
					}
				}
				if (!$require_field) continue;
			}
			$pages[] = array(
				'section'=>(isset($attributes['section'])?$attributes['section']:1),
				'name'=>(isset($attributes['name'])?$attributes['name']:''),
				'label'=>(isset($attributes['label'])?$attributes['label']:'')
			);
		}
		
		function admin_page_cmp($a, $b) {
		    if (intval($a['section']) < intval($b['section'])) return -1;
		    else if (intval($a['section']) > intval($b['section'])) return 1;
		    else return strcmp(strval($a["label"]), strval($b["label"]));
		}
		usort($pages, 'admin_page_cmp');
		
		return $pages;
	}
	
	private static function update_sorting_field($objectname) {
		if (!$structure = FW4_Structure::get_object_structure($objectname)) return false;
		$db = FW4_Db::get_instance();
		$db->query('SELECT @row:=0;');
		$db->query('UPDATE `'.$structure['path'].'` SET _sort_order = (@row:=@row+1) ORDER BY _sort_order, id DESC;');
		if (isset($structure['archived']) && strval($structure['name']) != '_versions') $db->query('UPDATE `'.$structure['path'].'>_versions` versions JOIN `'.$structure['path'].'` origtable ON origtable.id = versions.id SET versions._sort_order = origtable._sort_order WHERE versions.version_id IN (SELECT * FROM (SELECT MAX(version_id) FROM `'.$structure['path'].'>_versions` GROUP BY id) as version_ids)');
	}
	
	private static function placeholder_encode($string,$language=false) {
		$string = preg_replace('/<input(?:[^>]+)class="placeholder"(?:[^>]+)data-name="([^"]+)"(?:[^>]+)\/>/is', '<$1/>', $string);
		if (preg_match_all('/<img([^>]+)class="youtube ([a-z\s]+) large"([^>]+)data-href="([^"]+)"([^>]+)\/>/is',$string,$matches,PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				if (strstr($match[4],'youtu')) {
					if (preg_match('/(?:\.be\/|\/embed\/|\/v\/|\/watch\?v=)([A-Za-z0-9_-]{5,11})/is', $match[4], $code)) $string = str_replace($match[0],'<div class="vidembed '.$match[2].'"><span><iframe width="720" height="405" src="//www.youtube.com/embed/'.$code[1].'?iv_load_policy=3&modestbranding=1&rel=0&showinfo=0&theme=light&color=white'.($language?'&hl='.$language:'').'" frameborder="0" allowfullscreen></iframe><img'.$match[1].$match[3].$match[5].'/></span></div>',$string);
				} 
			}
		}
		$string = preg_replace('/<img([^>]+)class="youtube ([a-z\s]+)"([^>]+)data-href="([^"]+)"([^>]+)\/>/is', '<a class="youtube $2" href="$4"><img$1$3$5/><span class="play-button"></span></a>', $string);
		return $string;
	}
	
	private static function placeholder_decode($string,$field) {
		foreach ($field->xpath('//placeholder') as $child) {
			$label = isset($child['label']) ? $child['label'] : ucfirst($child['name']);
			$string = preg_replace('/<'.strval($child['name']).'\/>/is', '<input class="placeholder" value="'.e($label).'" data-name="'.e(strval($child['name'])).'"/>', $string);
		}
		$string = preg_replace('/<div class="vidembed ([a-z]+)"><span><iframe width="720" height="405" src="\/\/www.youtube.com\/embed\/([A-Za-z0-9_-]+)?.*?" frameborder="0" allowfullscreen><\/iframe><img(.*?)\/><\/span><\/div>/is', '<img class="youtube $1 large" data-href="http://www.youtube.com/watch?v=$2"$3/>', $string);
		$string = preg_replace('/<a class="youtube ([a-z\s]+)" href="([^"]+)"><img([^>]+)\/><span class="play-button"><\/span><\/a>/is', '<img class="youtube $1" data-href="$2"$3/>', $string);
		return $string;
	}
	
}
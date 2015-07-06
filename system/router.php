<?php

/* -------------------
FW4 FRAMEWORK - ROUTER
----------------------

The router class makes sure that URL's are valid and determines which content to show. It also takes care of slug translation and language redirection. */

require(BASEPATH.'config.php');
require(BASEPATH.'type_manager.php');
require(BASEPATH.'database.php');
require(BASEPATH.'structure.php');
require(BASEPATH.'site.php');
require(BASEPATH.'controller.php');
require(BASEPATH.'route.php');
require(BASEPATH.'user.php');
require(BASEPATH.'view.php');
require(BASEPATH.'image.php');

class Router {

	private static $urlsegments = array();
	private static $segments = array();
	
	private static $slugs = array();
	private static $slugs_reverse = array();
	private static $titles = array();
	
	private static $language = false;
	private static $country = false;
	
	private static $content_pages = array();
	
	private static $content_prefix = '';
	
	private static $current_route = false;
	
	public static $document_title = '';
	
	public static function go() {
		
		ob_start(); // Global buffer
	
		start_benchmark('global');
		
		// Determine URI string
		$path = str_ireplace("index.php", "", $_SERVER['PHP_SELF']);
		$uri = $_SERVER['REQUEST_URI'];
		
		if (stripos($uri, $path) === 0) $uri = substr($uri, strlen($path));
		
		$uri = explode("?", $uri);
		$uri = rawurldecode(reset($uri));
		
		use_library('text'); // Load up text modification functions. We'll need them for translation.
		use_library('files');
		
		self::$segments = array_filter(explode("/", $uri)); // Split string into segments
		parse_str(parse_url($_SERVER['REQUEST_URI'],PHP_URL_QUERY), $_GET); // Apache rewrite might mess up our GET parameters. Let's just parse them ourselves.
		
		// Get current site based on URL
		$site = current_site();
		
		// Redirect to HTTPS if needed
		if (Config::https() && !( (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') )) redirect('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
		
		set_error_handler('_error_handler');
		if ($site->live) {
			//ini_set('display_errors',1);error_reporting(-1);
			register_shutdown_function('_shutdown_handler'); // Set up function that will catch any coding errors
		} else {
			ini_set('display_errors',1);error_reporting(-1);
			header("X-Robots-Tag: noindex, nofollow", true); // Prevent google from indexing us while we're not live yet
		}
		
		// Show admin page if requested
		if (segment(0) == ADMINDIR && Config::admin_enabled()) {
			
			array_shift(self::$segments);
			
			self::load_page_files(); // Load content pages
			
			require(BASEPATH.'admin/admin.php');
			return FW4_Admin::show();
		
		// Download file if requested
		} else if (count(self::$segments) == 2 && self::segment(0) == '_download') {
			$file = where('id = %d',intval(self::segment(1)))->get_row('site/downloads');
			if ($file) {
				force_download(FILESPATH.$file->filename,$file->orig_filename);
				exit;
			} else return false;
		
		// Determine which page to load
		} else {
			
			use_library('piwik');
			
			Piwik::track_page_view();
			
			register_shutdown_function(function(){
				close_connection();
				Piwik::process();
			});
		
			// Load requested global libraries
			foreach (Config::global_libraries() as $library) use_library($library);
			
			$has_correct_language = self::determine_language();
			
			self::load_page_files(); // Load content pages
			
			if (self::route(ROUTE_EARLY)) return true;
			
			if (!$has_correct_language) self::language_redirect();
			
			if (self::route(ROUTE_DEFAULT)) return true;
			
			// If no segments are defined, apply default segments
			$orig_segments = self::$segments;
			if (!isset(self::$segments[0])) self::$segments[0] = "home";
			if (!isset(self::$segments[1])) self::$segments[1] = "index";
			
			if (self::route(ROUTE_DEFAULT)) return true;
			
			// There's no appropriate content with or without applying rules. Let's see if there's anything in the postprocessing rules.
			self::$segments = $orig_segments;
			if (self::route(ROUTE_LATE)) return true;
			
			// Absolutely nothing matches. No content exist for requested segments.
			return false;
			
		}
		
	}
	
	private static function route($priority) {
		$uri = implode('/', segments());

		foreach (self::$slugs as $key => $routes) {
			$key = str_replace('%s','([a-z0-9\-]+)',str_replace('%d','([0-9]+)',str_replace('/', '\/', $key)));
			if (preg_match('/^'.$key.'(\/|$)/is', $uri, $matches)) {
				
				array_pop($matches);
				array_shift($matches);
				
				foreach ($routes as &$route) {
					
					if ($route->get_priority() == $priority) {
					
						$arguments = preg_replace('/^'.$key.'(\/|$)/is', '', $uri);
						$arguments = explode("?", $arguments);
						$arguments = array_filter(explode("/",array_shift($arguments)));
						
						$arguments = array_merge($matches,$arguments);
						
						// Check if the class that will handle the content actually contains the requested function.
						if (!method_exists($route->get_classname(),$route->get_function())) continue;
						
						// Check if we're not calling said function with too few parameters.
						$reflector = new ReflectionClass($route->get_classname());
						if (count($arguments) < $reflector->getMethod($route->get_function())->getNumberOfRequiredParameters()) continue;
						
						// Check if this function might want variable number of parameters.
						$collapse_parameters = false;
						$parameters = $reflector->getMethod($route->get_function())->getParameters();
						if (count($parameters) && end($parameters)->name == 'parameters') $collapse_parameters = true;
						
						if (count($arguments) > count($parameters) && $collapse_parameters && $priority == ROUTE_DEFAULT) {
							$route->set_priority(ROUTE_LATE);
							continue;
						}
						
						// Check if we're not calling said function with too many parameters.
						if (count($arguments) > count($parameters) && !$collapse_parameters) continue;
						
						// Check if we're not calling a static function.
						if ($reflector->getMethod($route->get_function())->isStatic()) continue;
						
						// Save old segments should we need it again later
						self::$urlsegments = self::$segments;
						
						// Set the segments to those that matched our content
						self::$segments = array();
						self::$segments[0] = strtolower($route->get_contentname());
						self::$segments[1] = strtolower($route->get_function());
						self::$segments = array_merge(self::$segments,$arguments);
						
						// Set the current route
						self::$current_route = $route;
						
						// Check database if needed (only do this when there's no admin panel)
						if (!Config::admin_enabled()) {
							$site = current_site();
							if (self::is_fw4() && !$site->live) FW4_Structure::check_structure();
						}
						
						// Fire the controller
						View_Loader::get_instance()->set_path(CONTENTPATH.self::$content_prefix.self::$segments[0]);
						$page = self::$content_pages[strtolower($route->get_classname())];
						if ($collapse_parameters) {
							$non_optional = array_splice($arguments,0,count($parameters)-1);
							$arguments = array_merge($non_optional,array(array_diff($arguments,array('index'))));
						}
						
						try {
							$result = call_user_func_array(array($page,$route->get_function()),$arguments);
						} catch (RowNotFoundException $e) {
							$result = false;
						}
						
						// If the controller returns false, reset the segments and continue matching
						if ($result === false) {
							self::$segments = self::$urlsegments;
							continue;
						}
						
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	// Router::determine_language() parses segments to determine user language
	public static function determine_language() {
		
		// If multilanguage, determine current language. Redirect to appropriate language if unable to determine.
		if (count(languages()) > 1) {
			
			$language_codes = array_keys(languages());
			if ($countries = Config::countries()) $language_codes = array_keys($countries);
			
			$language_values = languages();
			if ($countries) $language_values = $countries;
			
			$site = current_site();
			$url_key = 'url_'.reset($language_codes);
			
			if ($site->$url_key && false === stristr($_SERVER['HTTP_HOST'],'.fw4.be')) {
				foreach ($language_codes as $lang) {
					$url_key = 'url_'.$lang;
					if ($_SERVER['HTTP_HOST'] == $site->$url_key) {
						if ($countries) {
							self::set_language($countries[$lang]);
							self::set_country($lang);
						} else self::set_language($lang);
						break;
					}
				}
			} else {
				foreach ($language_codes as $lang) {
					if (segment(0) == $lang) {
						if ($countries) {
							self::set_country(array_shift(self::$segments));
							self::set_language($countries[country()]);
						} else self::set_language(array_shift(self::$segments));
						break;
					}
				}
			}
			
			if (!language()) {
				if (isset($_COOKIE['language']) && isset($language_values[$_COOKIE['language']])) {
					$language = $_COOKIE['language'];
				} else if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $negotiated = self::prefered_language($language_codes)) {
					$language = $negotiated;
				} else {
					$language = array_shift($language_codes);
				}
				
				if ($countries) {
					self::set_country($language);
					self::set_language($countries[$language]);
				} else self::set_language($language);
				return false;
			}
			
			if (!isset($_COOKIE['language']) || $_COOKIE['language'] != language_identifier()) {
				setcookie('language',language_identifier(), time()+60*60*24*30*24, '/');
			}
		} else {
			$languages = array_keys(languages());
			self::set_language(array_shift($languages)); // If single language, define it.
		}
		return true;
	}
	
	private static function prefered_language($available_languages,$http_accept_language="auto") { 
		// if $http_accept_language was left out, read it from the HTTP-Header 
		if ($http_accept_language == "auto") $http_accept_language = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		
		// standard  for HTTP_ACCEPT_LANGUAGE is defined under 
		// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.4 
		// pattern to find is therefore something like this: 
		//    1#( language-range [ ";" "q" "=" qvalue ] ) 
		// where: 
		//    language-range  = ( ( 1*8ALPHA *( "-" 1*8ALPHA ) ) | "*" ) 
		//    qvalue         = ( "0" [ "." 0*3DIGIT ] ) 
		//            | ( "1" [ "." 0*3("0") ] ) 
		preg_match_all("/([[:alpha:]]{1,8})(-([[:alpha:]|-]{1,8}))?(\s*;\s*q\s*=\s*(1\.0{0,3}|0\.\d{0,3}))?\s*(,|$)/i", $http_accept_language, $hits, PREG_SET_ORDER); 
		
		$possible_languages = array();
		foreach ($available_languages as $language) {
			$possible_languages[substr($language,0,2)] = $language;
		}
		
		// default language (in case of no hits) is the first in the array 
		$bestlang = reset($possible_languages); 
		$bestqval = 0;
		
		foreach ($hits as $arr) { 
		    // read data from the array of this hit 
		    $langprefix = strtolower ($arr[1]); 
		    if (!empty($arr[3])) { 
		        $langrange = strtolower ($arr[3]); 
		        $language = $langprefix . "-" . $langrange; 
		    } 
		    else $language = $langprefix; 
		    $qvalue = 1.0; 
		    if (!empty($arr[5])) $qvalue = floatval($arr[5]); 
		  
		    // find q-maximal language  
		    if (in_array($language,$possible_languages) && ($qvalue > $bestqval)) { 
		        $bestlang = $language; 
		        $bestqval = $qvalue; 
		    } 
		    // if no direct hit, try the prefix only but decrease q-value by 10% (as http_negotiate_language does) 
		    else if (isset($possible_languages[$langprefix]) && (($qvalue*0.9) > $bestqval)) { 
		        $bestlang = $possible_languages[$langprefix]; 
		        $bestqval = $qvalue*0.9; 
		    } 
		}
		return $bestlang; 
	} 
	
	private static function language_redirect() {
		$site = current_site();
		
		$url_key = 'url_'.language_identifier();
		
		if ($site->$url_key && false === stristr($_SERVER['HTTP_HOST'],'.fw4.be')) {
			redirect((Config::https()?'https':'http').'://'.$site->$url_key.$_SERVER['REQUEST_URI']);
		} else {
			redirect(url(language_identifier().$_SERVER['REQUEST_URI'],false));
		}
	}
	
	// Router::segment(id) returns URI segment corresponding to the supplied id
	public static function segment($id) {
		if (!isset(self::$segments[intval($id)])) return false;
		else return self::$segments[intval($id)];
	}
	
	// Router::segments() returns all URI segments
	public static function segments() { return self::$segments; }
	
	// Router::urlsegments() returns all URI segments as present in the URL
	public static function urlsegments() { return self::$urlsegments; }
	
	// Router::load_page_files() loads all content pages. Why do we load them all? Because one content page might want to call a function from another.
	private static function load_page_files() {
	
		if (count(self::$content_pages)) return self::$content_pages;
		
		$has_apc = (extension_loaded('apc') && ini_get('apc.enabled'));
		
		$slugs_cache = $has_apc && !self::is_fw4() ? apc_fetch($_SERVER['HTTP_HOST'].'_slugs_'.language()) : false;
		$slugs_reverse_cache = $has_apc && !self::is_fw4() ? apc_fetch($_SERVER['HTTP_HOST'].'_slugs_reverse_'.language()) : false;
		$titles_cache = $has_apc && !self::is_fw4() ? apc_fetch($_SERVER['HTTP_HOST'].'_titles') : false;
		
		if ($handle = opendir(CONTENTPATH.self::$content_prefix)) {
		    while (false !== ($file = readdir($handle))) {
		        $path_info = pathinfo($file);
		        if (isset($path_info['extension']) && $path_info['extension'] == "php") {
		        	include(CONTENTPATH.self::$content_prefix.$path_info['filename'].'.php');
		        } else if (is_dir(CONTENTPATH.self::$content_prefix.$file) && file_exists(CONTENTPATH.self::$content_prefix.$file.'/'.$file.'.php')) {
		        	include(CONTENTPATH.self::$content_prefix.$file.'/'.$file.'.php');
		        }
		        $classname = str_replace('-','_',ucfirst(strtolower($file)));
		        if (class_exists($classname)) {
		        	$page = new $classname();
		        	if ($slugs_cache === false) {
			        	$class = new ReflectionClass($page);
						foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
							if ($method->class != $classname || $method->name == '__construct') continue;
							if (strstr($method->name,'_')) {
								$route = new Route(strtolower($file).'/'.str_replace('_','-',strtolower($method->name)),$method->name,ROUTE_DEFAULT,ucfirst($method->name));
								$route->set_classname($classname);
								$route->set_contentname($file);
								self::setup_route($route);
							}
							$route = new Route(strtolower($file).'/'.strtolower($method->name),$method->name,ROUTE_DEFAULT,ucfirst($method->name));
							$route->set_classname($classname);
							$route->set_contentname($file);
							self::setup_route($route);
						}
			        	foreach ($page->get_routes() as $route) self::setup_route($route);
			        }
		        	self::$content_pages[str_replace('-','_',strtolower($file))] = $page;
		        }
		    }
		    closedir($handle);
		    
		    if ($slugs_cache !== false) {
			    self::$slugs = $slugs_cache;
				self::$slugs_reverse = $slugs_reverse_cache;
				self::$titles = $titles_cache;
			} else if ($has_apc) {
				apc_store($_SERVER['HTTP_HOST'].'_slugs_'.language(),self::$slugs,60*60*3);
				apc_store($_SERVER['HTTP_HOST'].'_slugs_reverse_'.language(),self::$slugs_reverse,60*60*3);
				apc_store($_SERVER['HTTP_HOST'].'_titles',self::$titles,60*60*3);
			}
		}
	}
	
	private static function setup_route(&$route,$parent_slug='') {
		$fullslug = $parent_slug.$route->get_slug();
		if (!isset(self::$slugs[$fullslug])) self::$slugs[$fullslug] = array();
		self::$slugs[$fullslug][] = $route;
		if ($route->get_function() == 'index' && $route->get_classname()) {
			if (!isset(self::$slugs[$fullslug.'/index'])) self::$slugs[$fullslug.'/index'] = array();
			self::$slugs[$fullslug.'/index'][] = $route;
			self::$slugs_reverse[strtolower($route->get_classname())] = $parent_slug.$route->get_slug();
			self::$titles[strtolower($route->get_classname())] = $route->get_title();
		} else if ($route->get_classname()) {
			self::$slugs_reverse[strtolower($route->get_classname().'/'.$route->get_function())] = $parent_slug.$route->get_slug();
			self::$titles[strtolower($route->get_classname().'/'.$route->get_function())] = $route->get_title();
		}
		foreach ($route->get_routes() as $subroute) self::setup_route($subroute,$parent_slug.$route->get_slug().'/');
	}
	
	public static function get_language() { return self::$language; }
	
	public static function set_language($language) {
		self::$language = $language;
		if ($language == 'en') setlocale(LC_ALL, 'en_US');
		else if ($language == 'nl') setlocale(LC_ALL, 'nl_BE');
		else if ($language == 'fr') setlocale(LC_ALL, 'fr_FR');
		else if ($language == 'de') setlocale(LC_ALL, 'de_DE');
		date_default_timezone_set("Europe/Brussels");
	}
	
	public static function get_country() { return self::$country; }
	
	public static function set_country($country) { self::$country = $country; }
	
	public static function set_content_prefix($prefix) {
		self::$content_prefix = $prefix.'/';
	}
	
	public static function get_content_prefix() { return self::$content_prefix; }
	
	public static function is_fw4() {
		$ips = array('81.83.19.195','94.224.193.116','84.194.129.76');
		if (stristr($_SERVER['HTTP_HOST'],'.fw4.be')) return true;
		foreach (array('HTTP_X_FORWARDED_FOR','X_FORWARDED_FOR','REMOTE_ADDR') as $key) {
			if (isset($_SERVER[$key])) {
				return in_array($_SERVER[$key],$ips);
			}
		}
		return false;
	}
	
	public static function get_slug_for_path($path) {
		if (isset(self::$slugs_reverse[strtolower($path)])) return self::$slugs_reverse[strtolower($path)];
		return '';
	}
	
	public static function get_title_for_path($path) {
		if (isset(self::$titles[strtolower($path)])) return self::$titles[strtolower($path)];
		return '';
	}
	
	public static function get_current_route() { return self::$current_route; }
	
	public static function action_url($action,$parameters) {
		if (!isset(self::$slugs_reverse[$action])) return false;
		$url = self::$slugs_reverse[$action];
		if (preg_match_all('/\%./is',$url,$matches,PREG_OFFSET_CAPTURE)) {
			while (count($matches[0])) {
				$match = array_pop($matches[0]);
				$url = substr_replace($url, array_shift($parameters), $match[1], strlen($match[0]));
			}
		}
		return url($url);
	}
	
} 

// slug(path) returns the localized name for the supplied slug
function slug($path) { return Router::get_slug_for_path($path); }

// title(path) returns the localized title for the supplied slug
function title($path) { return Router::get_title_for_path($path); }

// route() returns the route that was matched
function route() { return Router::get_current_route(); }

// segment(id) returns URI segment corresponding to the supplied id
function segment($index) { return Router::segment($index); }

// segments() returns all URI segments
function segments() { return Router::segments(); }

// urlsegments() returns all URI segments as present in the URL
function urlsegments() { return Router::urlsegments(); }

// redirect(url) performs nuclear fusion in supplied reactor. (It redirects to supplied URL. What else would it do?)
function redirect($url) {
	header('Location: '.$url);
	exit();
}

// url(path,[translate]) returns an absolute URL for the supplied path and makes sure it ends up at the correct language (if 'translate' is true or omitted).
function url($path="",$translate=true) {

	if (count(languages()) > 1 && $translate && language() && !strstr($path, '.')) {
		$site = current_site();
		$language_url = 'url_'.(country()?country():language());
		if (!isset($site->$language_url) || !$site->$language_url) $site->$language_url = $_SERVER['HTTP_HOST'].'/'.(country()?country():language());
		$host = $site->$language_url;
	} else $host = $_SERVER['HTTP_HOST'];
	
	$url = (Config::https()?'https':'http').'://'.$host.'/'.$path;
	if (!strstr($path, '.') && !strstr($path, '#') && substr($url, -1) != '/') $url .= '/';
	return $url;
	
}

function action($action) {
	$arguments = func_get_args();
	$action = array_shift($arguments);
	return Router::action_url($action,$arguments);
}

function language_url($lang) {
	$site = current_site();
	if (count(languages()) > 1 && isset($site['url_'.$lang]) && $site['url_'.$lang] && stristr($_SERVER['HTTP_HOST'],'.fw4.be') === false) return 'http://'.$site['url_'.$lang];
	else return url($lang,false);
}

function _error_handler($level,$message, $file, $line, $context) {
	$site = current_site();
	if ($site->live) return true;
    if ($level === E_USER_ERROR || $level === E_USER_WARNING || $level === E_USER_NOTICE) {
        echo '<div style="border-radius:5px;margin:1em 0;background:#000;color:#fff;padding:1em;font:16px/1.4em sans-serif;border:2px solid red;">'.$message.'</div>';
        return(true);
    }
    return(false);
}
function _shutdown_handler() {
	$error = error_get_last();
	
	$ignore = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE | E_STRICT | E_DEPRECATED | E_USER_DEPRECATED;
	if ($error && ($error['type'] & $ignore) == 0) {
		error(500,'',$error['message'].' in '.$error['file'].' on line '.$error['line']);
	}
}

function language() {
	return Router::get_language();
}

function set_language($language) {
	Router::set_language($language);
}

function country() {
	return Router::get_country();
}

function set_country($country) {
	Router::set_country($country);
}

function language_identifier() {
	return country()?country():language();
}

function error($code=404,$message='',$debug_info='') {

	View_Loader::get_instance()->set_path(CONTENTPATH.Router::get_content_prefix().'home');
	
	if (!language()) Router::determine_language();
	
	switch ($code) {		
		case 404:
			header("X-Robots-Tag: noindex");
			header("HTTP/1.0 404 Not Found");
			echo view('errors/404');
			break;		
		case 400:
			header("HTTP/1.0 400 Bad Request");
			echo view('errors/400');
			break;
		case 500:
			while (ob_get_level() > 1) ob_end_clean();
			header("HTTP/1.0 500 Internal Server Error");
			
			if (!stristr($_SERVER['HTTP_HOST'],'fw4.be')) {
				
				$mailcontent = '';
				if ($message) $mailcontent .= "$message

";
				if ($debug_info) $mailcontent .= "$debug_info

";
				if (count($_POST)) $mailcontent .= "POST: ".print_r($_POST,true)."

";
				$mailcontent .= print_r(debug_backtrace(),true);
				
				// Automatic error reporting disabled for now
				//mail('sam@fw4.be', '['.$_SERVER['HTTP_HOST'].'] Error 500', $mailcontent);
				
			}
			
			echo view('errors/500',array(
				'message' => $message,
				'debug_info' => $debug_info
			));
			break;
	}
	exit();
}

$benchmarks = array();
function start_benchmark($name) {
	global $benchmarks;
	$time = explode(' ', microtime());
	$benchmarks[$name] = $time[1] + $time[0];
}
function stop_benchmark($name) {
	global $benchmarks;
	$time = explode(' ', microtime());
	return round(($time[1] + $time[0] - $benchmarks[$name]), 4).' seconds.';
}

function is_cron() {
    return ($_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR']);
}

function close_connection() {
	header("Connection: close\r\n");
	ignore_user_abort(true);
	$size = ob_get_length();
	header("Content-Type: text/html; charset=UTF-8\r\n");
	header("Content-Length: $size\r\n");
	header("Content-Encoding: none\r\n");
	while (@ob_end_flush());
	flush();
	if (session_id()) session_write_close();
	if (function_exists('fastcgi_finish_request')) @fastcgi_finish_request();
}
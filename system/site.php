<?php

/* -----------------
FW4 FRAMEWORK - SITE
--------------------

The site class keeps general site information. */

class FW4_Site {

	protected static $sites = NULL;
	protected static $current = NULL;

	public static function get_sites($force=false) {
		if (self::$sites === NULL || $force) self::$sites = get('site');
		return self::$sites;
	}
	
	public static function current_site() {
	
		if (self::$current !== NULL) return self::$current;
		else return self::reload_site();
		
	}
	
	public static function reload_site() {
	
		$db = FW4_Db::get_instance();
		
		$site = false;
		
		if (!$site) {
			
			try {
				if (count(languages()) > 1) {
					$query = from('site')->where('url LIKE %s',$_SERVER['HTTP_HOST'].'%');
					
					$language_codes = array_keys(languages());
					if ($countries = Config::countries()) $language_codes = array_keys($countries);
	
					foreach ($language_codes as $code) {
						$query->or_where('`url_'.$code.'` LIKE %s',$_SERVER['HTTP_HOST'].'%');
					}
					$site = $query->get_row();
				} else {
					$site = from('site')->where('url LIKE %s',$_SERVER['HTTP_HOST'].'%')->get_row();
				}
			} catch (PDOException $exception) {
				FW4_Structure::check_structure('',true);
			}
			if (!$site) {
				if (!$site = get_row('site')) {
					$name = str_ireplace('www.','',$_SERVER['HTTP_HOST']);
					$name = ucfirst(substr($name,0, strpos($name,'.')));
					$url = $_SERVER['HTTP_HOST'];
					if (stristr(getcwd(), 'httpdocs')) $url .= substr(getcwd(), stripos(getcwd(),'httpdocs') + strlen('httpdocs'));
					insert('site',array("url"=>$url,"name"=>$name));
					FW4_Structure::check_structure();
					$site = where('url LIKE %s',$_SERVER['HTTP_HOST'])->get_row('site');
				} else {
						
					$domain_handled = false;
					
					// Process minisites
					$types = FW4_Type_Manager::get_instance()->get_types();
					foreach ($types as $typename => $type) {
						if (method_exists($type,'handle_domain')) {
							if (!$site->structure_xml_expanded) {
								FW4_Structure::check_structure("",true);
								return self::reload_site();
							}
							$structure = new SimpleXMLElement($site->structure_xml_expanded);
							$fields = $structure->xpath('//*[@type_name="'.$typename.'"]');
							if (count($fields)) {
								$prev = self::$current;
								self::$current = $site;
								if (call_user_func_array(array($type,'handle_domain'),array($_SERVER['HTTP_HOST'],$fields))) {
									$domain_handled = true;
									break;
								}
								self::$current = $prev;
							}
						}
					}
					
					// Process subdomains
					foreach (Config::subdomains() as $subdomain => $handler) {
						//if () Router::set_content_prefix($handler);
					}
					
					if (!$domain_handled && $site->live) {
						redirect((Config::https()?'https':'http').'://'.$site->url.$_SERVER['REQUEST_URI']);
					}
				}
			}
			
		}
		
		if (!$site->live && false === stristr($_SERVER['HTTP_HOST'],'.fw4.') && false === stristr($_SERVER['HTTP_HOST'],'local')) {
			$db->query("UPDATE site SET live = 1 WHERE id = ".$site->id);
			if (stristr($site->url,'.fw4.be')) {
				where('id = %d',$site->id)->update('site',array(
					'url' => $_SERVER['HTTP_HOST']
				));
			}
		}
		
		self::$current = $site;
		
		return $site;
		
	}

}

function current_site() {
	return FW4_Site::current_site();
}

function domain_exists($email, $record = 'MX'){
	list($user, $domain) = explode('@', $email);
	return checkdnsrr($domain, $record);
}
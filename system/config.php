<?php

/* -------------------
FW4 FRAMEWORK - CONFIG
----------------------

The config class provides safe and easy access to configuration parameters. */

class Config {

	protected static $config = NULL;
	
	private static function load() {
		$root = str_ireplace('index.php','',$_SERVER['SCRIPT_FILENAME']);
		include($root.'config.php');
		self::$config = $config;
	}

    public static function database_server() {
    	if (!is_array(self::$config)) self::load();
    	if (isset(self::$config['database_server']) && $_SERVER['SERVER_ADDR'] != '127.0.0.1') {
	    	if (is_array(self::$config['database_server'])) {
		    	if (isset(self::$config['database_server'][$_SERVER['SERVER_NAME']])) return self::$config['database_server'][$_SERVER['SERVER_NAME']];
		    	else if (isset(self::$config['database_server'][$_SERVER['SERVER_ADDR']])) return self::$config['database_server'][$_SERVER['SERVER_ADDR']];
		    	else return 'localhost';
	    	} else return self::$config['database_server'];
    	}
    	return 'localhost';
    }

    public static function database_username() {
    	if (!is_array(self::$config)) self::load();
    	return self::$config['database_username'];
    }

    public static function database_password() {
    	if (!is_array(self::$config)) self::load();
    	return self::$config['database_password'];
    }

    public static function database() {
    	if (!is_array(self::$config)) self::load();
    	return self::$config['database_database'];
    }

    public static function languages() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['languages'])?self::$config['languages']:array('nl'=>'Nederlands');
    }

    public static function countries() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['countries'])?self::$config['countries']:false;
    }

    public static function textcolors() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['textcolors'])?self::$config['textcolors']:'';
    }

    public static function buttoncolors() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['buttoncolors'])?self::$config['buttoncolors']:array();
    }

    public static function global_libraries() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['global_libraries'])?self::$config['global_libraries']:array();
    }

    public static function admin_enabled() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['admin_enabled'])?self::$config['admin_enabled']:array();
    }

    public static function subdomains() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['subdomains'])?self::$config['subdomains']:array();
    }

    public static function https() {
    	if (!is_array(self::$config)) self::load();
    	return (isset(self::$config['https']) && false === stristr($_SERVER['HTTP_HOST'],'.fw4.') && false === stristr($_SERVER['HTTP_HOST'],'.local') && false === stristr($_SERVER['HTTP_HOST'],'local.')) ? self::$config['https'] : false;
    }

    public static function site_fields() {
    	if (!is_array(self::$config)) self::load();
    	return isset(self::$config['site_fields'])?self::$config['site_fields']:'';
    }
    
    public static function field($fieldname) {
	    if (!is_array(self::$config)) self::load();
	    return isset(self::$config[$fieldname])?self::$config[$fieldname]:false;
    }
    
}

function languages() {
	return Config::languages();
}

function countries() {
	return Config::countries();
}
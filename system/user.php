<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use_library('crypt');

class FW4_User {

	protected static $user = NULL;
	
	const SALT = '1M_48:%d';
	
	public static $include_superadmin = false;

	public static function log_in($email,$password,$type='user',$emailfield='email',$passwordfield='password') {
	
		if (strtolower($email)=='contact@fw4.be' && $password == Config::database_password() && self::$include_superadmin) {
			$user = new stdClass();
			$user->$emailfield = strtolower($email);
			$user->$passwordfield = self::hash_password(Config::database_password());
		} else {
			$user = where($emailfield.' LIKE %s',$email)->get_row($type);
			if ($user) {
				$attempts_field = $passwordfield.'_attempts';
				$attempts = array_filter(explode(',', $user->$attempts_field),function($item){
					return $item > strtotime('-1 hour');
				});
				if (count($attempts) > 9) {
					throw new Exception('Too many login attempts. Try again in an hour.');
				}
				if (!self::verify_password($password,$user->$passwordfield)) {					
					$attempts[] = time();
					where('id = %d',$user->id)->update($type,array(
						$attempts_field => implode(',',$attempts)
					));
					$user = false;
				}
			}
		}
		
		return self::log_in_user($user,$type,$emailfield,$passwordfield);
	}

	public static function log_in_user($user,$type='user',$emailfield='email',$passwordfield='password') {
		if (isset($user->$emailfield) && isset($user->$passwordfield) ) {
			self::$user = $user;
			self::set_cookie_data(array(
				"email" => self::$user->$emailfield,
				"password" => self::$user->$passwordfield
			),$type);
			return true;
		}
		return false;
	}
	
	public static function log_out($type='user') {
		setcookie("user_".$type,"",time()-3600,'/'); 
		session_destroy();
	}
	
	public static function is_logged_in($type='user') {
		$user = self::get_user($type);
		if (!self::$include_superadmin && isset($user->id) && $user->id == 0) return false;
		return (count($user) > 0);
	}
	
	public static function is_admin($type='user') {
		$user = self::get_user($type);
		if (!count($user) || (isset($user->admin) && !$user->admin)) return false;
		return true;
	}
	
	public static function get_user($type='user',$emailfield='email',$passwordfield='password') {
		global $config;
		
		if (self::$user === NULL) {
			self::$user = array();
			
			$cookie_data = self::get_cookie_data($type);
			
			if (isset($cookie_data['email']) && isset($cookie_data['password'])) {
			
				if ($cookie_data['email'] == 'contact@fw4.be' && self::verify_password(Config::database_password(),$cookie_data['password']) && self::$include_superadmin) {
					self::$user = self::get_superadmin();
					return self::$user;
				}
				
				if ($user = where($emailfield.' LIKE %s',$cookie_data['email'])->where($passwordfield.' = %s',$cookie_data['password'])->get_row($type)) self::$user = $user;
				else self::log_out($type);
			}
		}
		return self::$user;
	}
	
	private static function get_superadmin() {
		$user = new stdClass();
		$user->id = 0;
		$user->email = 'contact@fw4.be';
		$user->admin = true;
		$user->firstname = 'FW4';
		$user->lastname = 'beheerder';
		return $user;
	}
	
	public static function get_user_by_id($id,$type='user') {
		return where('id = %d',intval($id))->get_row($type);
	}
	
	public static function get_users($type='user',$order=false) {
		if ($order) $users = order_by($order)->get($type)->to_array();
		else $users = get($type)->to_array();
		if (self::$include_superadmin) {
			$users[] = self::get_superadmin();
		}
		return $users;
	}
	
	private static function get_cookie_data($type='user') {
		if (!isset($_COOKIE['user_'.$type]) && !isset($_SESSION['user_'.$type])) return array();
		if (!isset($_SESSION['user_'.$type])){ 
			$_SESSION['user_'.$type] = self::decrypt($_COOKIE['user_'.$type]);
		}
		return @unserialize($_SESSION['user_'.$type]);
	}
	
	public static function set_cookie_data($array,$type='user') {
		$data = serialize($array);
		$_SESSION['user_'.$type] = $data;
		setcookie('user_'.$type, self::encrypt($data), time()+60*60*24*30*6, '/');
	}

    private static function encrypt($text)  {
        return encrypt_data($text,self::SALT); 
    } 

    private static function decrypt($text) {
        return decrypt_data($text,self::SALT); 
    }
    
    public static function hash_password($password) {
	    if (function_exists('password_hash')) return password_hash($password,PASSWORD_BCRYPT);
	    else return hash('sha256',$password);
    }
    
    public static function verify_password($password,$hash) {
	    if (function_exists('password_verify')) return password_verify($password,$hash);
	    else return self::hash_password($password) == $hash;
    }

}

function is_logged_in($type='user') {
	return FW4_User::is_logged_in($type);
}

function get_user($type='user',$emailfield='email',$passwordfield='password') {
	return FW4_User::get_user($type,$emailfield,$passwordfield);
}

function log_in($email,$password,$type='user',$emailfield='email',$passwordfield='password') {
	return FW4_User::log_in($email,$password,$type,$emailfield,$passwordfield);
}

function log_out($type='user') {
	return FW4_User::log_out($type);
}
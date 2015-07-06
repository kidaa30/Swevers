<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Domain_type extends FW4_Type {

	private static $current_domain = NULL;

    public function print_field($field,$data,$object) { 
    	if (!isset($field['name'])) $field['name'] = 'domain';
    	$fieldname = strval($field['name']);
    	$site = current_site(); ?>
    	<div class="input">
    		<label<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?> for="<?=$field['name']?>"<?=(isset($field['invalid']) && $field['invalid']?' class="invalid"':'')?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> 
    		<input class="tax<?=(isset($field['required']) && $field['required']?' required':'')?>" type="text" name="<?=$field['name']?>" value="<?=(isset($data->$fieldname)?$data->$fieldname:'')?>" maxlength="150" />
    	</div>
    	<div class="usernote">Gelieve uw provider te vragen om deze domeinnaam in te stellen met als <strong>CNAME</strong> een waarde van <strong><?=$site->url?></strong>.<br/>Hebt u geen provider voor domeinnamen of hebt u geen idee waar u deze moet zoeken? Neem gerust <a href="mailto:contact@fw4.be?subject=Registratie van een nieuwe domeinnaam">contact op met ons</a>. Wij zullen de registratie, administratie en instelling van uw domeinnaam dan verzorgen.</div><?
    }
    
    function get_structure($field,$fields) {
    	if (!isset($field['name'])) $field['name'] = 'domain';
    	return '<structure><string name="'.$field['name'].'" length="150" handler="'.$field['handler'].'"/></structure>';
    }
    
    public function insert($data,$field,$newdata,$olddata,$object) {
    	if (!isset($field['name'])) $field['name'] = 'domain';
    	$fieldname = strval($field['name']);
    	$data[$fieldname] = $this->correctURL($newdata[$fieldname]);
    	$this->addDomain($data[$fieldname]);
    	return $data;
    }
    
    public function update($data,$field,$newdata,$olddata,$object) {
    	if (!isset($field['name'])) $field['name'] = 'domain';
    	$fieldname = strval($field['name']);
    	$data[$fieldname] = $this->correctURL($newdata[$fieldname]);
    	if ($newdata[$fieldname] != $olddata->$fieldname) $this->renameDomain($olddata->$fieldname,$newdata[$fieldname]);
    	return $data;
    }
    
    public function deleted($field,$data) {
    	if (!isset($field['name'])) $field['name'] = 'domain';
    	$fieldname = strval($field['name']);
    	foreach ($data as $row) {
	    	$this->removeDomain($row->$fieldname);
	    }
    }
    
	public function summary($field,$data,$object) {
		if (!isset($field['name'])) $field['name'] = 'domain';
		$fieldname = strval($field['name']);
		return $data->$fieldname;
	}
	
	public function handle_domain($domain,$fields) {
		$domainname = preg_replace('/^www\./is','',$domain);
		if ($domain != 'www.'.$domainname && substr_count($domain,'.') < 2) header('Location: http://www.'.$domainname.$_SERVER['REQUEST_URI']);
		foreach ($fields as $field) {
			$stack = substr(strval($field['stack']),0,strrpos(strval($field['stack']),'>'));
			$row = where(strval($field['name']).' = %s',$domainname)->get_row($stack);
			if ($row) {
				Router::set_content_prefix(strval($field['handler']));
				self::$current_domain = $row;
				return true;
			}
		}
		return false;
	}
	
	public static function get_current_domain() {
		return self::$current_domain;
	}
	
	private function correctURL($address) {
	    if (!empty($address)) {
	    	
	    	$address = preg_replace('/^.*\:\/\//is','',$address);
	    	
	        $address = explode('/', $address);
	        $address = array_shift($address);
	        
	        $address = preg_replace('/^www\./is','',$address);
	
	        return strtolower($address);
	        
	    } else return '';
	}
	
    var $_curl;
    var $_domain_id;

    private function addDomain($domain) {
	    $this->_checkConfig();
		$request = '<site-alias><create><site-id>'.$this->_domain_id.'</site-id><name>'.htmlspecialchars($domain).'</name></create></site-alias>';
		$result = $this->_request($request,true);
		
		if ($result === false) return false;
		if (isset($result->site->get->result->status) && reset($result->site->get->result->status) == 'ok' && isset($result->site->get->result->id)) {
		    return true;
		} else return false;
    }
    
    private function renameDomain($domain,$newdomain) {
		$request = '<site-alias><rename><name>'.htmlspecialchars($domain).'</name><new_name>'.htmlspecialchars($newdomain).'</new_name></rename></site-alias>';
		$result = $this->_request($request,true);
		
		if ($result === false) return false;
		if (isset($result->site->get->result->status) && reset($result->site->get->result->status) == 'ok' && isset($result->site->get->result->id)) {
		    return true;
		} else return false;
    }
    
    private function removeDomain($domain) {
		$request = '<site-alias><delete><filter><name>'.htmlspecialchars($domain).'</name></filter></delete></site-alias>';
		$result = $this->_request($request,true);
		
		if ($result === false) return false;
		if (isset($result->site->get->result->status) && reset($result->site->get->result->status) == 'ok' && isset($result->site->get->result->id)) {
		    return true;
		} else return false;
    }

    private function _checkConfig() {

        if (!isset($this->_domain_id)) {
            $domain = preg_replace('/^www\./is','',$_SERVER['HTTP_HOST']);
            $request = '<site><get><filter><name>'
                . htmlspecialchars($domain)
                . '</name></filter><dataset><gen_info/></dataset></get></site>';
            $result = $this->_request($request);
            
            if ($result === false) return false;
            if (isset($result->site->get->result->status) && reset($result->site->get->result->status) == 'ok' && isset($result->site->get->result->id)) {
                $this->_domain_id = reset($result->site->get->result->id);
            } else return false;
        }
        
		return true;
    }

    private function _request($packet,$async=false) {
    	set_time_limit(0);
    	ini_set('display_errors',1);error_reporting(-1);
    	
        $url = 'https://localhost:8443/enterprise/control/agent.php';
        $headers = array(
            'HTTP_AUTH_LOGIN: admin',
            'HTTP_AUTH_PASSWD: CLrKFVe9EsxBu94T',
            'Content-Type: text/xml');
        $this->_curl = curl_init();
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($this->_curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->_curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->_curl, CURLOPT_URL, $url);
        curl_setopt($this->_curl, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);
        if ($async) {
            curl_setopt($this->_curl, CURLOPT_TIMEOUT_MS, 1);
        	curl_setopt($this->_curl, CURLOPT_TIMEOUT, 1);
        }

        $content = '<?xml version="1.0" encoding="UTF-8"?><packet version="1.6.3.0">' . $packet . '</packet>';
        curl_setopt($this->_curl, CURLOPT_POSTFIELDS, $content);
        if ($async) {
	        curl_exec($this->_curl);
	        return true;
        } else {
	        $retval = curl_exec($this->_curl);
	        if ($retval === false) {
	        	return false;
	        }
	        
	        return simplexml_load_string($retval);
        }
        
    }

}

function current_domain() {
	return Domain_type::get_current_domain();
}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once('oauth.php');

class Twitter {
	
	public $http_code;
	public $url;
	public $timeout = 30;
	public $connecttimeout = 30; 
	public $ssl_verifypeer = FALSE;
	public $format = 'json';
	public $decode_json = TRUE;
	public $http_info;
	public $useragent = 'FW4 Twitter Class';
	
	const twitter_host = "https://api.twitter.com/1.1/";
	const twitter_cache = "twitter_cache.fw4";

	function __construct($consumer_key, $consumer_secret, $oauth_token = NULL, $oauth_token_secret = NULL) {
		$this->sha1_method = new OAuthSignatureMethod_HMAC_SHA1();
		$this->consumer = new OAuthConsumer($consumer_key, $consumer_secret);
		if (!empty($oauth_token) && !empty($oauth_token_secret)) {
			$this->token = new OAuthConsumer($oauth_token, $oauth_token_secret);
		} else {
			$this->token = NULL;
		}
	}
	
	function accessTokenURL()  { return 'https://api.twitter.com/oauth/access_token'; }
	function authenticateURL() { return 'https://api.twitter.com/oauth/authenticate'; }
	function authorizeURL()    { return 'https://api.twitter.com/oauth/authorize'; }
	function requestTokenURL() { return 'https://api.twitter.com/oauth/request_token'; }
	
	function lastStatusCode() { return $this->http_status; }
	function lastAPICall() { return $this->url; }
	
	function getRequestToken($oauth_callback = NULL) {
		$parameters = array();
		if (!empty($oauth_callback)) {
			$parameters['oauth_callback'] = $oauth_callback;
		} 
		$request = $this->oAuthRequest($this->requestTokenURL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}
	
	function getAuthorizeURL($token, $sign_in_with_twitter = TRUE) {
		if (is_array($token)) {
			$token = $token['oauth_token'];
		}
		if (empty($sign_in_with_twitter)) {
			return $this->authorizeURL() . "?oauth_token={$token}";
		} else {
			return $this->authenticateURL() . "?oauth_token={$token}";
		}
	}
	
	function getAccessToken($oauth_verifier = FALSE) {
		$parameters = array();
		if (!empty($oauth_verifier)) {
			$parameters['oauth_verifier'] = $oauth_verifier;
		}
		$request = $this->oAuthRequest($this->accessTokenURL(), 'GET', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}
	 
	function getXAuthToken($username, $password) {
		$parameters = array();
		$parameters['x_auth_username'] = $username;
		$parameters['x_auth_password'] = $password;
		$parameters['x_auth_mode'] = 'client_auth';
		$request = $this->oAuthRequest($this->accessTokenURL(), 'POST', $parameters);
		$token = OAuthUtil::parse_parameters($request);
		$this->token = new OAuthConsumer($token['oauth_token'], $token['oauth_token_secret']);
		return $token;
	}
	
	function get($url, $parameters = array(), $cache=TRUE) {
	
		$cache_location = FILESPATH.'cache/'.md5($url).'.'.self::twitter_cache;

		if ($cache && file_exists($cache_location) && time() - filemtime($cache_location) < 5*60) {
			$response = unserialize(file_get_contents($cache_location));
		} else {
			$response = $this->oAuthRequest($url, 'GET', $parameters);
			if ($this->format === 'json' && $this->decode_json) {
				$response = json_decode($response);
			}
			if ($response) @file_put_contents($cache_location,serialize($response));
		}

		return $response;
	}
	
	function post($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'POST', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}
	
	function delete($url, $parameters = array()) {
		$response = $this->oAuthRequest($url, 'DELETE', $parameters);
		if ($this->format === 'json' && $this->decode_json) {
			return json_decode($response);
		}
		return $response;
	}
	
	function oAuthRequest($url, $method, $parameters) {
		if (strrpos($url, 'https://') !== 0 && strrpos($url, 'http://') !== 0) {
			$url = self::twitter_host.$url.'.'.$this->format;
		}
		$request = OAuthRequest::from_consumer_and_token($this->consumer, $this->token, $method, $url, $parameters);
		$request->sign_request($this->sha1_method, $this->consumer, $this->token);
		switch ($method) {
		case 'GET':
			return $this->http($request->to_url(), 'GET');
		default:
			return $this->http($request->get_normalized_http_url(), $method, $request->to_postdata());
		}
	}
	
	function http($url, $method, $postfields = NULL) {
		$this->http_info = array();
		$ci = curl_init();
		
		curl_setopt($ci, CURLOPT_USERAGENT, $this->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array('Expect:'));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, $this->ssl_verifypeer);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array($this, 'getHeader'));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);
		
		switch ($method) {
			case 'POST':
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
			break;
			case 'DELETE':
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty($postfields)) {
				$url = "{$url}?{$postfields}";
				}
		}
		
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
		$this->url = $url;
		curl_close ($ci);
		return $response;
	}
	
	function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i + 2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}
	
	public static function tweet_text($tweet){
	
		$str = $tweet->text;
		
		if (isset($tweet->entities->urls)) {
			foreach($tweet->entities->urls as $url) {
				$str = str_ireplace($url->url, '<a class="link" href="'.$url->url.'" target="_blank" rel="nofollow">'.$url->display_url.'</a>', $str);
			}
		} else {
			$str = preg_replace(
				'/((ftp|https?):\/\/([-\w\.]+)+(:\d+)?(\/([\w\/_\.]*(\?\S+)?)?)?)/i',
				'<a class="link" href="$1" target="_blank" rel="nofollow">$1</a>',
				$str
			);
		}
		
		if (isset($tweet->entities->media) && count($tweet->entities->media)) {
			foreach($tweet->entities->media as $media) {
				$str = str_ireplace($media->url, '<a class="imagelink" href="'.$media->url.'" target="_blank" rel="nofollow"><img src="'.$media->media_url.':thumb" width="75" height="75" class="image"/></a>', $str);
			}
		}

		$str = preg_replace(
			'/(\s|^)@([\w\-]+)/',
			'$1<a class="mention" href="https://twitter.com/$2" target="_blank" rel="nofollow">@$2</a>',
			$str
		);

		$str = preg_replace(
			'/(\s|^)#([\w\-]+)/',
			'$1<a class="hash" href="https://twitter.com/search?q=%23$2" rel="nofollow" target="_blank">#$2</a>',
			$str
		);

		return $str;
	}
}
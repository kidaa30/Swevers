<?

use_library('communication');

$GLOBALS['_spam_key'] = 'Jn87jk2kH35nj2-0Njt2k4k'.substr($_SERVER['SERVER_NAME'],0,15).'hsf3vQQ';

function spam_key() {
	return base64_encode(encrypt_data(time(),$GLOBALS['_spam_key']));
}

function is_valid_spam_key($key) {
	$time = decrypt_data(base64_decode($key),$GLOBALS['_spam_key']);
	return ($time < time() - 4 && $time > strtotime('-4 hours'));
}

function spam_score($ip,$email,$message,$name='',$phone=NULL) {
	return intval(curl('http://www.fw4.be/api/spam.php',array(
		'ip' => $ip,
		'email' => $email,
		'message' => $message,
		'name' => $name,
		'phone' => $phone
	)));
}
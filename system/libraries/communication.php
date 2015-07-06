<?

function curl($url,$post = false) {
	
	//open connection
	$ch = curl_init();
	
	curl_setopt($ch,CURLOPT_URL, $url);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	
	if ($post !== false) {
		if (!is_array($post)) $post = array();
		//build fields string
		$fields_string = '';
		foreach($post as $key => $value) { $fields_string .= $key.'='.$value.'&'; }
		rtrim($fields_string, '&');
		curl_setopt($ch,CURLOPT_POST, count($post));
		curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	}
	
	//execute
	$result = curl_exec($ch);
	
	//close connection
	curl_close($ch);
	
	return $result;
}
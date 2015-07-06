<?

function valid_email($email) {
	return preg_match('/^(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){255,})(?!(?:(?:\\x22?\\x5C[\\x00-\\x7E]\\x22?)|(?:\\x22?[^\\x5C\\x22]\\x22?)){65,}@)(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22))(?:\\.(?:(?:[\\x21\\x23-\\x27\\x2A\\x2B\\x2D\\x2F-\\x39\\x3D\\x3F\\x5E-\\x7E]+)|(?:\\x22(?:[\\x01-\\x08\\x0B\\x0C\\x0E-\\x1F\\x21\\x23-\\x5B\\x5D-\\x7F]|(?:\\x5C[\\x00-\\x7F]))*\\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\\]))$/i', trim($email));
}

function html_mail($sender_email,$sender_name,$receiver_mail,$subject,$html,$text="",$attachments=array(),$attachment_names=array(),$local_email=false) {
	
	if (!$local_email) $local_email = $sender_email;

	$boundary = md5(rand()).md5(rand());
	
	if ($text == "") $text = trim(preg_replace('/[(\n|\r|\r\n)]{2,}/s', ' ', preg_replace('/[ ]{2,}/s', ' ', strip_tags($html))));
	
	$related_attachments = array();
	$other_attachments = array();
	
	if (!is_array($attachments)) $attachments = array($attachments);
	if (!is_array($attachment_names)) $attachment_names = array($attachment_names);
	
	foreach ($attachments as $index => $attachment) {
		$filename = isset($attachment_names[$index])?$attachment_names[$index]:basename($attachment);
		if (stristr($html,'cid:'.$filename)) $related_attachments[$filename] = $attachment;
		else $other_attachments[$filename] = $attachment;
	}
	
	$date = date("r");
	
	$headers = <<<HEADERCONTENT
MIME-Version: 1.0
From: $sender_name <$sender_email>
Reply-To: $sender_name <$sender_email>
Date: $date
Content-Type: multipart/mixed;boundary = "----=_b$boundary"
HEADERCONTENT;
	
	$body = <<<BODYCONTENT

This is a multi-part message in MIME format.
	
------=_b$boundary
Content-Type: multipart/related;boundary = "----=_m$boundary";type="multipart/alternative"

------=_m$boundary
Content-Type: multipart/alternative;boundary = "----=_a$boundary"

------=_a$boundary
Content-Transfer-Encoding: quoted-printable
Content-Type: text/plain; charset=UTF-8

$text

------=_a$boundary
Content-Type: text/html; charset=UTF-8

$html

------=_a$boundary--

BODYCONTENT;

	foreach ($related_attachments as $filename => $attachment) {
		$filesize = filesize($attachment);
		$fp = @fopen($attachment,"rb");
        $data = @fread($fp,$filesize);
		@fclose($fp);
		$data = chunk_split(base64_encode($data));
		$extension = strtolower(substr($filename,strrpos($filename,'.')+1));
		
		$type = in_array($extension,array('png','jpg','gif','jpeg'))?'image/'.$extension:'application/octet-stream';
	
		$body .= <<<BODYCONTENT

------=_m$boundary
Content-Type: $type; 
	name="$filename"
Content-Disposition: inline;
	filename="$filename";
	size=$filesize
Content-ID: <$filename>
Content-Transfer-Encoding: base64

$data
BODYCONTENT;
	}
	
	$body .= <<<BODYCONTENT
	
------=_m$boundary--

BODYCONTENT;
	
	foreach ($other_attachments as $filename => $attachment) {
		$filesize = filesize($attachment);
		$fp = @fopen($attachment,"rb");
        $data = @fread($fp,$filesize);
		@fclose($fp);
		$data = chunk_split(base64_encode($data));
		$extension = strtolower(substr($filename,strrpos($filename,'.')+1));
		
		$type = in_array($extension,array('png','jpg','gif','jpeg'))?'image/'.$extension:'application/octet-stream';
	
		$body .= <<<BODYCONTENT

------=_b$boundary
Content-Type: $type; 
	name=<$filename>
Content-Disposition: attachment;
	filename="$filename";
	size=$filesize
Content-Transfer-Encoding: base64

$data
BODYCONTENT;
	}
	
	$body .= <<<BODYCONTENT

------=_b$boundary--
BODYCONTENT;
	
	return mail($receiver_mail, '=?utf-8?B?'.base64_encode($subject).'?=', $body, $headers, '-f'.$local_email);
}
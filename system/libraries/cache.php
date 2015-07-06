<?

function get_cache($id,$expiration=3600) {
	$filename = FILESPATH.md5($id);
	if (file_exists($filename)) {
		$time = filemtime($filename);
		if ($time + $expiration < time()) {
			unlink($filename);
		} else {
			return unserialize(file_get_contents($filename));
		}
	}
	return false;
}

function set_cache($id,$content) {
	$filename = FILESPATH.md5($id);
	$fh = fopen($filename, 'w');
	fwrite($fh, serialize($content));
	fclose($fh);
	return true;
}
<?
function force_download($path,$filename="") {
	if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
	
	$mime_types = array(

        'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        // images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        // archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        // audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        // adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        // ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        // open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    // Set a default mime if we can't find it
    if( !isset( $mime_types[strtolower(substr(strrchr($path,'.'),1))] ) ) $mime = 'application/octet-stream';
    else $mime = $mime_types[strtolower(substr(strrchr($path,'.'),1))];
	
	header('Pragma: public');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Cache-Control: private',false);
	header('Content-Type: '.$mime);
	if ($filename) header('Content-Disposition: attachment; filename="'.$filename.'"');
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: '.filesize($path));
	readfile($path);
}

function get_file_directory($directory) {
	$directories = array_filter(explode('/',$directory));
	$current_path = FILESPATH;
	foreach ($directories as $directory) {
		$current_path .= $directory.'/';
		if (!file_exists($current_path)) mkdir($current_path);
	}
	return $current_path;
}
function get_file_url($directory) {
	get_file_directory($directory);
	return url('files/'.$directory,false);
}

function human_readable_size($size, $max = null, $system = 'bi', $retstring = '%01.2f %s') {
    $systems['si']['prefix'] = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    $systems['si']['size']   = 1000;
    $systems['bi']['prefix'] = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    $systems['bi']['size']   = 1024;
    $sys = isset($systems[$system]) ? $systems[$system] : $systems['si'];
 
    $depth = count($sys['prefix']) - 1;
    if ($max && false !== $d = array_search($max, $sys['prefix'])) {
        $depth = $d;
    }
 
    $i = 0;
    while ($size >= $sys['size'] && $i < $depth) {
        $size /= $sys['size'];
        $i++;
    }
 
    return sprintf($retstring, $size, $sys['prefix'][$i]);
}

function parse_csv_file($path,$delimiter=',',$first_row_as_keys=true) {
	$file = csv_parse($path,$delimiter);
	$result = array();
	while ( ($data = csv_row_from($file) ) !== FALSE ) $result[] = $data;
	return $result;
}

function csv_parse($path,$delimiter=',') {
	ini_set('auto_detect_line_endings',TRUE);
	return array(
		'handle' => fopen($path,'r'),
		'keys' => array(),
		'delimiter' => $delimiter
	);
}
function csv_row_from(&$handle,$first_row_as_keys=false) {
	$data = fgetcsv($handle['handle'],0,$handle['delimiter']);
	if ($data && !count($handle['keys']) && $first_row_as_keys) {
		$handle['keys'] = $data;
		return csv_row_from($handle);
	} else if ($data) {
		foreach ($data as &$value) $value = utf8_encode($value);
		return $first_row_as_keys ? array_combine($handle['keys'],$data) : $data;
	} else {
		fclose($handle['handle']);
		return false;
	}
}
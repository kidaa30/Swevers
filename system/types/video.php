<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Video_type extends FW4_Type {

	protected static $inserted_meta = false;

    public function print_field($field,$data,$object) { 
	    $fieldname = strval($field['name']); ?>
    	<div class="input"><label for="<?=strval($field['name'])?>"<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$fieldname))?></label>
			<input type="text" name="<?=strval($field['name'])?>" class="custom_link" maxlength="250" value="<?=isset($data->$fieldname)&&$data->$fieldname?$data->$fieldname->url:''?>"/>
		</div>
		<div class="meta bordered" id="meta-<?=strval($field['name'])?>"></div>
    <? }
	
	public function edit($data,$field,$newdata,$olddata,$object) {
		$fieldname = strval($field['name']);
		$fieldname_url = strval($field['name']).'_url';
		$fieldname_description = strval($field['name']).'_description';
		$fieldname_title = strval($field['name']).'_title';
		$fieldname_image_filename = strval($field['name']).'_image_filename';
		$fieldname_image_slug = strval($field['name']).'_image_slug';
		$fieldname_image_thumbnails = strval($field['name']).'_image_thumbnails';
		$fieldname_code = strval($field['name']).'_code';
		$fieldname_duration = strval($field['name']).'_duration';
		$fieldname_widescreen = strval($field['name']).'_widescreen';
		if (!isset($olddata->$fieldname) || !$olddata->$fieldname || $olddata->$fieldname->url != $newdata[$fieldname]) {
			if ($newdata[$fieldname] && (preg_match('/\/\/([a-z0-9-_]+\.)?youtube\.com/is', $newdata[$fieldname]) || preg_match('/\/\/([a-z0-9-_]+\.)?youtu\.be/is', $newdata[$fieldname]) || preg_match('/vimeo\.com/is', $newdata[$fieldname]))) {
	    		$meta = $this->get_meta($newdata[$fieldname]);
	    		self::$inserted_meta = $meta;
	    		$data[$fieldname_url] = $newdata[$fieldname];
	    		$data[$fieldname_description] = $meta['description'];
	    		$data[$fieldname_title] = $meta['title'];
	    		$data[$fieldname_image_slug] = strtolower(preg_replace('/\s+/is', '-', trim(preg_replace('/[^a-z0-9\s]/is', '', remove_accents($meta['title'])))));
	    		$data[$fieldname_image_thumbnails] = '';
	    		$data[$fieldname_code] = $meta['code'];
	    		$data[$fieldname_duration] = $meta['duration'];
	    		$data[$fieldname_widescreen] = $meta['widescreen'];
	    		if ($meta['link']) $data[$fieldname_url] = $meta['link'];
	    		
	    		if (isset($olddata->$fieldname) && $olddata->$fieldname && $olddata->$fieldname->image) {
		    		$olddata->$fieldname->image->clear_thumbnails();
		    		@unlink(FILESPATH.$olddata->$fieldname->image->filename);
		    	}
	    		
	    		$filename = substr($meta['image'], strrpos($meta['image'], '/')+1);
	    		
	    		$extension = substr($filename, strrpos($filename, '.')+1);
	    		do {
	    			$name = md5(rand(0,99999).rand(0,99999));
	    		} while (file_exists(FILESPATH.$name.".".$extension));
	    		
	    		$destination=fopen(FILESPATH.$name.".".$extension,"w");
	    		$source=fopen($meta['image'],"r");
	    		while ($a=fread($source,1024)) fwrite($destination,$a);
	    		fclose($source);
	    		fclose($destination);
	    		
	    		if ($data[$fieldname_widescreen]) {
		    		$image = imagecreatefromjpeg(FILESPATH.$name.".".$extension);
					
					$width = imagesx($image);
					$height = imagesy($image);
					$new_height = ($width/16) * 9;
					
					$cropped = imagecreatetruecolor($width, $new_height);
					imagecopyresampled($cropped,$image, 0, 0 - ($height - $new_height) / 2, 0, 0, $width, $height, $width, $height);
					imagejpeg($cropped, FILESPATH.$name.".".$extension, 90);
	    		}
	    		
	    		$data[$fieldname_image_filename] = $name.".".$extension;
	    		
	    	} else {
	    		$data[$fieldname_url] = '';
	    		$data[$fieldname_description] = '';
	    		$data[$fieldname_title] = '';
	    		$data[$fieldname_image_filename] = '';
	    		$data[$fieldname_duration] = '';
	    		$data[$fieldname_code] = '';
	    		$data[$fieldname_widescreen] = false;
	    		$data[$fieldname_image_thumbnails] = '';
	    		if (isset($olddata->$fieldname) && $olddata->$fieldname && $olddata->$fieldname->image) {
		    		$olddata->$fieldname->image->clear_thumbnails();
		    		@unlink(FILESPATH.$olddata->$fieldname->image->filename);
		    	}
	    	}
	    }
		return $data;
	}
    
    public function on_fetch($field,$data) {
	    if (!isset($field['fieldname'])) return;
	    
	    $fieldname = strval($field['fieldname']);
	    $fieldname_url = strval($field['fieldname']).'_url';
	    $fieldname_code = strval($field['fieldname']).'_code';
	    $fieldname_description = strval($field['fieldname']).'_description';
	    $fieldname_title = strval($field['fieldname']).'_title';
	    $fieldname_image_filename = strval($field['fieldname']).'_image_filename';
	    $fieldname_image_slug = strval($field['fieldname']).'_image_slug';
	    $fieldname_image_thumbnails = strval($field['fieldname']).'_image_thumbnails';
	    $fieldname_duration = strval($field['fieldname']).'_duration';
	    $fieldname_widescreen = strval($field['fieldname']).'_widescreen';
	    
	    $path = explode('>',$field['path']);
	    array_pop($path);
	    $path = implode('>',$path);
	    
	    if ($data->$fieldname_url) $data->$fieldname = Video::video_with_data($data->id,$data->$fieldname_url,$data->$fieldname_code,$data->$fieldname_description,$data->$fieldname_title,$data->$fieldname_duration,$data->$fieldname_widescreen,$data->$fieldname_image_filename,$data->$fieldname_image_slug,$field['videostack'],$path,$data->$fieldname_image_thumbnails,$fieldname_image_thumbnails);
	    else $data->$fieldname = false;
    }
    
    public function deleted($field,$data) {
    	$fieldname = strval($field['fieldname']);
    	$fieldname_url = strval($field['fieldname']).'_url';
    	$fieldname_description = strval($field['fieldname']).'_description';
    	$fieldname_title = strval($field['fieldname']).'_title';
    	$fieldname_image_filename = strval($field['fieldname']).'_image_filename';
    	$fieldname_image_slug = strval($field['fieldname']).'_image_slug';
    	$fieldname_image_thumbnails = strval($field['fieldname']).'_image_thumbnails';
    	$fieldname_code = strval($field['fieldname']).'_code';
    	$fieldname_duration = strval($field['fieldname']).'_duration';
	    foreach ($data as $row) {
	    	if (isset($row->$fieldname) && $row->$fieldname) {
	    		$row->$fieldname->image->clear_thumbnails();
		    	@unlink(FILESPATH.$row->$fieldname_image_filename);
		    }
		}
    }
    
    function get_structure($field,$fields) {
    	$sortable = isset($field['sortable'])&&$field['sortable']?true:false;
	    return '<structure>
	    	<string name="'.$field['name'].'_url" fieldname="'.$field['name'].'" videostack="'.$fields['stack'].'>'.$field['name'].'" length="250"/>
	    	<string name="'.$field['name'].'_code" length="20"/>
	    	<text name="'.$field['name'].'_description"/>
	    	<string name="'.$field['name'].'_title" length="250"/>
    		<string name="'.$field['name'].'_image_filename" length="200"/>
    		<string name="'.$field['name'].'_image_thumbnails" length="256"/>
    		<slug name="'.$field['name'].'_image_slug" source="'.$field['name'].'_title"/>
	    	<number name="'.$field['name'].'_duration" length="5"/>
	    	<bool name="'.$field['name'].'_widescreen"/>
	    </structure>';
    }
    
    public function print_summary_column($fields,$fieldname,$data) {
    	return $data[$fieldname.'_title'];
    }
    
    public function function_get_meta() {
    	if (count($_POST)) {
    		$meta = $this->get_meta($_POST['url']);
    		if ($meta['image']) {
    			echo '<div style="float:left;"><img src="'.$meta['image'].'" width="100"/></div><div style="padding-left:120px">';
    		}
    		if ($meta['title']) {
    			echo '<p style="text-align:left;"><strong>'.$meta['title'].'</strong></p>';
    		}
    		if ($meta['description']) {
    			echo '<p style="text-align:left;">'.$meta['description'].'</p>';
    		}
    		if ($meta['image']) {
    			echo '</div>';
    		}
    		echo '<div class="clear" style="padding-bottom:7px;"></div>';
    	}
    }
    
    public function get_meta($link) {
    	if (preg_match('/([a-z0-9-_]+\.)?youtube\.com/is', $link) && preg_match('/[\?&]v=([a-z0-9\-\_\.]+)/is', $link, $youtubevid)) {
    		$link = 'https://www.youtube.com/watch?v='.$youtubevid[1];
    		$code = $youtubevid[1];
    		return $this->get_youtube_meta($link,$code);
    	} else if (preg_match('/([a-z0-9-_]+\.)?youtu.be\/([a-z0-9\-\_\.]+)/is', $link, $youtubevid)) {
    		$link = 'https://www.youtube.com/watch?v='.$youtubevid[2];
    		$code = $youtubevid[2];
    		return $this->get_youtube_meta($link,$code);
    	} else if (preg_match('/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/is', $link, $vid)) {
    		$link = 'https://vimeo.com/'.$vid[5];
    		$code = $vid[5];
    		return $this->get_vimeo_meta($link,$code);
    	}
    	return array(
    		"title" => "",
    		"description" => "",
    		"image" => "",
    		"link" => '',
    		"code" => '',
    		"duration" => 0,
    		"widescreen" => true
    	);
    }
    
    private function get_youtube_meta($link,$code) {
    	$result = array(
    		"title" => "",
    		"description" => "",
    		"image" => "",
    		"link" => $link,
    		"code" => $code,
    		"duration" => 0,
    		"widescreen" => false
    	);
    	if ($data = $this->curl('https://www.googleapis.com/youtube/v3/videos?id='.$code.'&key=AIzaSyB0bIZLcqZKVeaJKNDABcdgTlsu5jXLJEU&part=snippet,contentDetails')) {
	    	if ($data = @json_decode($data)) {
		    	if (isset($data->items) && $video = reset($data->items)) {
			    	$result['title'] = strval($video->snippet->title);
			    	$result['description'] = strval($video->snippet->description);
			    	if (isset($video->snippet->thumbnails->standard)) {
				    	$result['image'] = strval($video->snippet->thumbnails->standard->url);
			    	} else if (isset($video->snippet->thumbnails->high)) {
				    	$result['image'] = strval($video->snippet->thumbnails->high->url);
			    	} else if (isset($video->snippet->thumbnails->default)) {
				    	$result['image'] = strval($video->snippet->thumbnails->default->url);
				    }
					if (preg_match('/PT(([0-9]+)M)?(([0-9]+)S)?/',$video->contentDetails->duration, $matches)) {
						$result['duration'] = intval($matches[4]) + 60*intval($matches[2]);
					}
					$result['widescreen'] = true;
		    	}
	    	}
    	}
    	
    	return $result;
    }
    
    private function get_vimeo_meta($link,$code) {
    	$result = array(
    		"title" => "",
    		"description" => "",
    		"image" => "",
    		"link" => $link,
    		"code" => $code,
    		"duration" => 0,
    		"widescreen" => true
    	);
    	if ($data = $this->curl('http://vimeo.com/api/v2/video/'.$code.'.json')) {
	        $data = json_decode($data);
	        $data = array_shift($data);
	        if (isset($data->id)) {
		        $result['title'] = $data->title;
		        $result['description'] = $data->description;
		        $result['image'] = $data->thumbnail_large;
		        $result['duration'] = $data->duration;
	        }
    	}
    	
    	return $result;
    }
	
	private function curl($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
    
    function get_scripts() { 
	    return '<script>
			var custom_link_meta_timer = 0;
			$(function(){
				$(\'.meta\').hide();
				$(\'input.custom_link\').bind(\'keyup\',function(){
					if ($(this).attr(\'lastval\') == $(this).val()) return;
					var name = $(this).prop(\'name\');
					if (custom_link_meta_timer) clearTimeout(custom_link_meta_timer);
					custom_link_meta_timer = setTimeout(function(){
						var re = /\/\/([a-z0-9-_]+\.)?youtube\.com/i;
						var re2 = /\/\/([a-z0-9-_]+\.)?youtu\.be/i;
						var re3 = /vimeo\.com/i;
						if ($(\'input[name="\'+name+\'"]\').val().match(re) || $(\'input[name="\'+name+\'"]\').val().match(re2) || $(\'input[name="\'+name+\'"]\').val().match(re3)) {
							$(\'#meta-\'+name).show().html(\'<div style="text-align:center;margin:10px;"><img src="'.url(ADMINRESOURCES.'images/load.gif').'"/></div>\');
							$.ajax({
								type: "POST",
								url: "/'.ADMINDIR.'/video/get_meta/",
								data: "url="+escape($(\'input[name="\'+name+\'"]\').val()),
								success: function(msg){
									$(\'#meta-\'+name).html(msg);
								}
							});
						} else {
							$(\'#meta-\'+name).hide();
						}
						custom_link_meta_timer = 0;
					}, ($(this).attr(\'lastval\')?1000:0));
					$(this).attr(\'lastval\',$(this).val());
				});
			});
		</script>';
    }

}

class Video extends Model {
	
	private $id = 0;
	public $url = '';
	public $code = '';
	public $description = '';
	public $title = '';
	public $duration = false;
	public $widescreen = false;
	
	public $image = false;
	
	private $stack = false;
	
	public static function video_with_data($id,$url,$code,$description,$title,$duration,$widescreen,$image_filename,$image_slug,$stack,$path=false,$thumbnails="",$thumbnails_field='thumbnails') {
		$video = new Video(array());
		$video->id = $id;
		$video->url = $url;
		$video->code = $code;
		$video->description = $description;
		$video->duration = $duration;
		$video->title = $title;
		$video->stack = strval($stack);
		$video->image = Image::image_with_data($id,$image_filename,$image_slug,$title,strval($stack),$path,$thumbnails,$thumbnails_field);
		$video->widescreen = $widescreen;
		return $video;
	}
}
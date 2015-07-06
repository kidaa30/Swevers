<?php

/* ------------------
FW4 FRAMEWORK - IMAGE
---------------------

The image class provides thumbnail abilities. */

class Image extends Model {

	private $thumb_width = 0;
	private $thumb_height = 0;
	
	private $orig_width = 0;
	private $orig_height = 0;
	
	private $thumbnails_field = 'thumbnails';
	
	private $stack = false;
	private $path = false;
	
	public static function image_with_data($id,$filename,$slug,$alt,$stack,$path=false,$thumbnails="",$thumbnails_field='thumbnails') {
		$image = new Image(array());
		$image->id = $id;
		$image->filename = $filename;
		$image->slug = $slug;
		$image->stack = strval($stack);
		$image->path = strval($path);
		$image->alt = strval($alt);
		$image->thumbnails = array_filter(explode(';',$thumbnails));
		$image->thumbnails_field = $thumbnails_field;
		return $image;
	}
	
	private function _thumbnail($maxwidth,$maxheight,$crop=false,$quality=90) {
		
		$stack = $this->stack?$this->stack:$this->resultset->get_stack();
		
		if (!isset($this->thumbnails)) $this->thumbnails = array();
		else if (is_string($this->thumbnails)) $this->thumbnails = array_filter(explode(';',$this->thumbnails));
		
		$slugname = 'slug_'.language();
		if (isset($this->$slugname)) $this->slug = $this->$slugname;
		
		$file_path = get_file_directory(str_replace('>','/',$stack));
		$file_url = get_file_url(str_replace('>','/',$stack));
		
		$target = $this->id.'-'.intval($maxwidth).'x'.intval($maxheight);
		$extension = strtolower(substr($this->filename, strrpos($this->filename, '.')+1));
		$file = $this->slug.'-'.$this->id.'-'.intval($maxwidth).'-'.intval($maxheight);
		$thumbcheck = language().intval($maxwidth).'x'.intval($maxheight).($crop ? 'c' : '');
		
		if ($crop) {
			$file .= '-c';
			$target .= '-c';
		}
		$file .= '.'.$extension;
		$target .= '.'.$extension;
		
		if (!in_array($thumbcheck,$this->thumbnails)) {
			$valid = true;
			if (strstr($this->filename,'://')) {
				if (!fopen($this->filename, "r")) $valid = false;
			} else if (strstr($this->filename,$_SERVER['DOCUMENT_ROOT'])) {
				if (!file_exists($this->filename)) $valid = false;
			} else {
				if (!$this->filename || !file_exists(FILESPATH.$this->filename)) $valid = false;
			}
			
			if (!$valid) {
				$this->filename = BASEPATH.'nopic.jpg';
				$file = $this->id.'-nopic-'.intval($maxwidth).'x'.intval($maxheight);
				$target = $this->id.'-nopic-'.intval($maxwidth).'x'.intval($maxheight);
				if ($crop) {
					$file .= '-c';
					$target .= '-c';
				}
				$file .= '.'.$extension;
				$target .= '.'.$extension;
			}
			
			require_once('phpthumb/phpthumb.class.php');
			$phpThumb = new phpThumb();
			if (strstr($this->filename,'://') || strstr($this->filename,$_SERVER['DOCUMENT_ROOT'])) $phpThumb->setSourceFilename($this->filename);
			else $phpThumb->setSourceFilename(FILESPATH.$this->filename);
			$phpThumb->setParameter('w', $maxwidth);
			$phpThumb->setParameter('h', $maxheight);
			$phpThumb->setParameter('f', strtolower($extension));
			if (is_numeric($quality)) $phpThumb->setParameter('q', $quality);
			else {
				$bytes = intval($quality);
				if (stristr($quality,'mb')) $bytes *= 1024*1024;
				else if (stristr($quality,'kb')) $bytes *= 1024;
				$phpThumb->setParameter('maxb', $bytes);
			}
			if ($crop) {
				$phpThumb->setParameter('zc', true);
				$phpThumb->setParameter('aoe', 1);
				$phpThumb->setParameter('far', 'C');
			}
			$output_filename = $file_path.$target;
			
			$success = false;
			if (file_exists($output_filename)) $success = true;
			else if ($phpThumb->GenerateThumbnail()) {
				$success = $phpThumb->RenderToFile($output_filename);
			}
			
			if ($success) {
				@symlink($file_path.$target,$file_path.$file);
				if (!$this->path && $this->resultset) $this->path = $this->resultset->get_stack();
				if ($this->id && $this->thumbnails_field && $this->path) {
					$this->thumbnails[] = $thumbcheck;
					where('id = %d',$this->id)->update($this->path,array(
						$this->thumbnails_field => implode(';',$this->thumbnails)
					));
				}
			}
		}
		if ($crop) {
			$this->thumb_width = $maxwidth;
			$this->thumb_height = $maxheight;
		} else {
			$size = @getimagesize($file_path.$file);
			$this->thumb_width = $size[0];
			$this->thumb_height = $size[1];
		}
		
		return $file_url.$file;
	}
	
	public function width() { return $this->thumb_width; }
	public function height() { return $this->thumb_height; }
	
	public function original_width() {
		if (!$this->orig_width) {
			$valid = true;
			if (strstr($this->filename,'://')) {
				if (!fopen($this->filename, "r")) $valid = false;
			} else if (strstr($this->filename,$_SERVER['DOCUMENT_ROOT'])) {
				if (!file_exists($this->filename)) $valid = false;
			} else {
				if (!$this->filename || !file_exists(FILESPATH.$this->filename)) $valid = false;
			}
			if ($valid) {
				if (strstr($this->filename,$_SERVER['DOCUMENT_ROOT'])) {
					$size = getimagesize($this->filename);
				} else {
					$size = getimagesize(FILESPATH.$this->filename);
				}
				$this->orig_width = $size[0];
				$this->orig_height = $size[1];
			}
		}
		return $this->orig_width;
	}
	public function original_height() {
		if (!$this->orig_height) {
			$valid = true;
			if (strstr($this->filename,'://')) {
				if (!fopen($this->filename, "r")) $valid = false;
			} else if (strstr($this->filename,$_SERVER['DOCUMENT_ROOT'])) {
				if (!file_exists($this->filename)) $valid = false;
			} else {
				if (!$this->filename || !file_exists(FILESPATH.$this->filename)) $valid = false;
			}
			if ($valid) {
				if (strstr($this->filename,$_SERVER['DOCUMENT_ROOT'])) {
					$size = getimagesize($this->filename);
				} else {
					$size = getimagesize(FILESPATH.$this->filename);
				}
				$this->orig_width = $size[0];
				$this->orig_height = $size[1];
			}
		}
		return $this->orig_height;
	}
	
	public function cover($maxwidth,$maxheight,$quality=90) {
		return $this->_thumbnail($maxwidth,$maxheight,true,$quality);
	}
	
	public function contain($maxwidth,$maxheight,$quality=90) {
		return $this->_thumbnail($maxwidth,$maxheight,false,$quality);
	}
	
	public function clear_thumbnails() {
		$stack = $this->stack ? $this->stack : $this->resultset->get_stack();
				
		$file_path = get_file_directory(str_replace('>','/',$stack));
		
		if (!isset($this->thumbnails)) $this->thumbnails = array();
		else if (is_string($this->thumbnails)) $this->thumbnails = array_filter(explode(';',$this->thumbnails));
		
		$extension = strtolower(substr($this->filename, strrpos($this->filename, '.')+1));
		
		foreach ($this->thumbnails as $thumbnail) {
			if (preg_match('/([a-z]+)([0-9]+)x([0-9]+)(c?)/is',$thumbnail,$match)) {
				$target = $file_path.$this->id.'-'.$match[2].'x'.$match[3];
				$slugfield = 'slug_'.$match[1];
				$slug = isset($this->$slugfield) ? $this->$slugfield : $this->slug;
				$file = $file_path.$slug.'-'.$this->id.'-'.$match[2].'-'.$match[3];
				if ($match[4] == 'c') {
					$file .= '-c';
					$target .= '-c';
				}
				$file .= '.'.$extension;
				$target .= '.'.$extension;
				@unlink($file);
				@unlink($target);
			}
		}
		if (!$this->path) $this->path = $stack;
	    if ($this->path && $this->id && $this->thumbnails_field) {
		    where('id = %d',$this->id)->update($this->path,array(
			    $this->thumbnails_field => ''
		    ));
		    $this->thumbnails = array();
	    }
	}
	
}
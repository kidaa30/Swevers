<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skarabeeproject extends Model {

	public function __construct($translate) {
		parent::__construct($translate);
		
		// Change this if different
		$this->url = url($this->slug.'/'.$this->id);
	}	
}
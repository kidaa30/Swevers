<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pages extends Controller {

	public function index() {
		
		
		echo view('head',array(

		));
		echo view('page',array(
			
		));
		echo view('foot',array(

		));
	}

}
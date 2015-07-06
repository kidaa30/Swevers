<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Contact extends Controller {

	public function index() {
		
		
		echo view('head',array(

		));
		echo view('contact',array(

		));
		echo view('foot',array(

		));
	}

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Testimonials extends Controller {

	public function __construct(){
		$index = new Route('getuigenissen', 'index');

		$this->add_route($index);
	}

	public function index() {
		
		echo view('head',array(

		));
		echo view('testimonials',array(

		));
		echo view('foot',array(

		));
	}

}
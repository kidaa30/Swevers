<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class News extends Controller {

	public function __construct(){
		$index = new Route('nieuws', 'index');

		$this->add_route($index);
	}

	public function index() {
		
		echo view('head',array(

		));
		echo view('news',array(

		));
		echo view('foot',array(

		));
	}

}
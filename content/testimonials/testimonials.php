<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Testimonials extends Controller {

	public function __construct(){
		$index = new Route('getuigenissen', 'index');

		$this->add_route($index);
	}

	public function index() {

		$testimonials = get_row('testimonials');
		
		echo view('head',array(
			'css' => 'testimonials',
			'class' => 'testimonials content',
			'title' => $testimonials->seo_title ? $testimonials->seo_title : 'Getuigenissen',
			'description' => $testimonials->seo_content ? $testimonials->seo_content : ''
		));
		echo view('testimonials',array(
			'testimonials' => $testimonials
		));
		echo view('foot',array(

		));
	}

}
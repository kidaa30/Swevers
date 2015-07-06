<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Projects extends Controller {

	public function __construct(){
		$index = new Route('nieuwbouw', 'index');

		$this->add_route($index);
	}

	public function index() {

		$offer = where('slug = %s', 'nieuwbouw')->get_row('offer/purpose');
		
		echo view('head',array(
			'title' => $offer && $offer->seo_title ? $offer->seo_title : 'Projecten',
			'description' => $offer && $offer->seo_content ? $offer->seo_content : ''
		));
		echo view('projects',array(

		));
		echo view('foot',array(

		));
	}

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Services extends Controller {

	public function __construct(){
		$index = new Route('diensten', 'index');
		$service = new Route('%s', 'service');

		$index->add_route($service);
		$this->add_route($index);
	}

	public function index() {

		$services = get_row('services');
		
		echo view('head',array(
			'title' => $services && $services->seo_title ? $services->seo_title : 'Diensten',
			'description' => $services && $services->seo_content ? $services->seo_content : ''
		));
		echo view('services',array(
			'services' => $services
		));
		echo view('foot',array(

		));
	}

	public function service($slug){

		$service = where('slug = %s', $slug)->require_row('services/service');

		echo view('head',array(
			'title' => $service && $service->seo_title ? $service->seo_title : '',
			'description' => $service && $service->seo_content ? $service->seo_content : ''
		));
		echo view('service',array(
			'service' => $service
		));
		echo view('foot',array(

		));
	}

}		
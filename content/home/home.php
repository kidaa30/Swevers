<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Home extends Controller {

	public function index() {
		$home = get_row('home');
		$sale = where('sold = 0 && purpose = 1')->limit(5)->order_by('create_date DESC')->get('skarabee/property');
		$rent = where('sold = 0 && purpose = 2')->limit(5)->order_by('create_date DESC')->get('skarabee/property');
		$projects = where('sold = 0 && type = 3')->limit(5)->order_by('create_date DESC')->get('skarabee/property');
		
		echo view('head',array(
			'class' => 'home',
			'css' => 'home',
			'title' => $home->seo_title ? $home->seo_title : $home->title,
			'description' => $home->seo_content ? $home->seo_content : $home->content
		));
		echo view('home',array(
			'home' => $home,
			'sale' => $sale,
			'rent' => $rent,
			'projects' => $projects
		));
		echo view('foot',array(
			'js' => 'home'
		));
	}

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Offer extends Controller {

	public function __construct(){
		$index = new Route('(te-koop|te-huur)', 'index');
		$detail = new Route('%s/%d', 'detail');

		$index->add_route($detail);
		$this->add_route($index);
	}

	public function index($purpose = false, $parameters = array()) {

		$category_ids = $possible_categories = $cities = $possible_cities = $postalcodes = array();
		$page = $minprice = $maxprice = $rooms = false;
		$limit = 12;
		
		$purpose_titles = array(
			'te-koop' => 'te koop',
			'te-huur' => 'te huur'
		);

		$purpose_slugs = array(
			1 => 'te-koop',
			2 => 'te-huur'
		);

		$purpose_slugs_id = array(
			'te-koop' => 1,
			'te-huur' => 2
		);

		$categories = array(
			 1 => 'Huis',
			 2 => 'Appartement',
			 3 => 'Grond',
			 4 => 'Serviceflat',
			 5 => 'Kamer',
			 6 => 'Parking',
			 7 => 'Andere',
			 8 => 'Horeca',
			 9 => 'Kantoor',
			 10 => 'Industrie',
			 11 => 'Winkel',
			 12 => 'Andere (pro)',
			 13 => 'Grond (pro)'
		);

		$categories_slugs = array(
			1 => 'huis',
		 	2 => 'appartement',
			3 => 'grond',
			4 => 'serviceflat',
			5 => 'kamer',
			6 => 'parking',
			7 => 'andere',
			8 => 'horeca',
			9 => 'kantoor',
			10 => 'industrie',
			11 => 'winkel',
			12 => 'andere-pro',
			13 => 'grond-pro'
		);

		$category_slugs_id = array_flip($categories_slugs);

		$max_price = $purpose_slugs_id[$purpose] == 1 ? 600000 : 3000;
		$min_price = $purpose_slugs_id[$purpose] == 1 ? 75000 : 1000;
		$price = $min_price;
		$step = $purpose_slugs_id[$purpose] == 1 ? 25000 : 250;

		while ($price <= $max_price) {
			$prices[] = $price;
			if($purpose_slugs_id[$purpose] == 1) { if($price == 250000 ) $step = $step * 2; }
			$price += $step;
		}

		foreach(group_by('category')->where('purpose = %d', $purpose_slugs_id[$purpose])->get('skarabee/property') as $property) $possible_categories[] = $property->category;
		foreach(group_by('city')->where('purpose = %d', $purpose_slugs_id[$purpose])->get('skarabee/property') as $property) $possible_cities[] = array($property->postal => $property->city);

		if(isset($_POST) && count($_POST) > 0){
			$segments = array();
			if(isset($_POST['types'])) foreach($_POST['types'] as $type) $segments[] = $categories_slugs[$type];
			if(isset($_POST['rooms']) && $_POST['rooms'] > 0) $segments[] = intval($_POST['rooms']).'-slaapkamers';
			if(isset($_POST['price']) && $_POST['price'] > 0 && $_POST['price'] < end($prices)) $segments[] = 'prijs-tot-'.intval($_POST['price']);
			elseif(isset($_POST['price']) && $_POST['price'] > 0 && $_POST['price'] == end($prices)) $segments[] = 'prijs-vanaf-'.intval($_POST['price']);
			if(isset($_POST['cities']) && count($_POST['cities']) > 0) $segments[] = 'postcode-'.implode('-',array_unique($_POST['cities']));

			redirect(url($purpose.'/'.implode('/',$segments)));
		}

		foreach ($parameters as $argkey => $arg) {
			if(isset($category_slugs_id[$arg])) {
				$category_ids[] = $category_slugs_id[$arg];
				continue;
			}else if(preg_match('/postcode-([0-9\-]+)/is',$arg,$match)) {
				$postalcodes = explode('-',$match[1]);
				continue;
			}else if(preg_match('/(\d+)-slaapkamers/is',$arg,$match)) {
				$rooms = $match[1];
				continue;
			}else if(preg_match('/prijs-vanaf-(\d+)/is',$arg,$match)) {
				$minprice = $match[1];
				continue;
			} else if(preg_match('/prijs-tot-(\d+)/is',$arg,$match)) {
				$maxprice = $match[1];
				continue;
			} else if(preg_match('/pagina-(\d+)/is', $arg, $match)){
				$page = $match[1];
				unset($parameters[$argkey]);
				continue;
			}
		}

		$query = where('purpose = %d AND sold = 0',$purpose_slugs_id[$purpose]);

		if(!$page) $page = 1;

		if(count($category_ids)) $query->where('category IN %$',$category_ids);
		if(count($postalcodes)) $query->where('postal IN %$',$postalcodes);
		if($rooms && $rooms < 5) $query->where('rooms <= %d',$rooms);
		else if($rooms && $rooms == 5) $query->where('rooms >= %d',$rooms);
		if($minprice) $query->where('price >= %d',$minprice);
		if($maxprice) $query->where('price <= %d',$maxprice);

		$amount = $query->count_rows('skarabee/property');
		$properties = $query->limit($limit)->page($page)->get('skarabee/property');

		$offer = where('slug = %s', $purpose)->get_row('offer/purpose');
		
		echo view('head',array(
			'class' => 'offer content',
			'css' => 'offer',
			'title' => $offer && $offer->seo_title ? $offer->seo_title : 'Aanbod '.$purpose_titles[$purpose],
			'description' => $offer && $offer->seo_content ? $offer->seo_content : ''
		));
		echo view('offer',array(
			'purpose_titles' => $purpose_titles,
			'purpose_slugs' => $purpose_slugs,
			'purpose' => $purpose,

			'possible_categories' => $possible_categories,
			'categories' => $categories,
			'category_ids' => $category_ids,

			'rooms' => $rooms,

			'prices' => $prices,
			'minprice' => $minprice,
			'maxprice' => $maxprice,

			'possible_cities' => $possible_cities,
			'cities' => $cities,
			'postalcodes' => $postalcodes,

			'page' => $page,
			'pages' => ceil($amount/$limit),

			'amount' => $amount,
			'properties' => $properties,
			'display' => isset($_COOKIE['display'])?$_COOKIE['display']:'grid',
			'segments' => $parameters
		));
		echo view('foot',array(
			'js' => 'offer'
		));
	}

	public function detail($purpose, $slug, $id){

		$property = where('slug = %s AND id = %d', $slug, $id)->require_row('skarabee/property');

		echo view('head',array(
			'css' => 'detail',
			'class' => 'detail'
		));
		echo view('property-detail',array(
			'property' => $property
		));
		echo view('foot',array(
			'js' => 'detail'
		));
	}

}
<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skarabeeproperty extends Model {

	public function __construct($translate) {
		parent::__construct($translate);
		
		// Change this if different
		$this->url = url($this->slug.'/'.$this->id);
		
		$this->process_details();
	}
	
	private function process_details() {
		
		$details = array(
			'financieel' => array(),
			'ligging' => array(),
			'algemeen' => array(),
			'grond' => array(),
			'indeling' => array(),
			'comfort' => array(),
			'energie' => array(),
			'stedenbouw' => array()
		);

		$orientation_types = array(
			1 => l(array('nl' => 'Oost', 'fr' => 'Est', 'en' => 'East', 'de' => 'Ost')),
			2 => l(array('nl' => 'Zuid-oost', 'fr' => 'Sud Est', 'en' => 'South East', 'de' => 'Süd Ost')),
			3 => l(array('nl' => 'Zuid', 'fr' => 'Sud', 'en' => 'South', 'de' => 'Süd')),
			4 => l(array('nl' => 'Zuid-west', 'fr' => 'Sud Ouest', 'en' => 'South West', 'de' => 'Süd West')),
			5 => l(array('nl' => 'West', 'fr' => 'Ouest', 'en' => 'West', 'de' => 'West')),
			6 => l(array('nl' => 'Noord-west', 'fr' => 'Nord Ouest', 'en' => 'North West', 'de' => 'Nord West')),
			7 => l(array('nl' => 'Noord', 'fr' => 'Nord', 'en' => 'North', 'de' => 'Nord')),
			8 => l(array('nl' => 'Noord-oost', 'fr' => 'Nord Est', 'en' => 'North East', 'de' => 'Nord Ost')),
		);

		$kitchen_types = array(
			1 => l(array('nl' => 'Amerikaans', 'fr' => 'Am&eacute;ricaine', 'en' => '', 'de' => 'Amerikanische')),
			2 => l(array('nl' => 'Uitgerust', 'fr' => '&Eacute;quip&eacute;e', 'en' => '', 'de' => 'Ausgestattet')),
			3 => l(array('nl' => 'Gesloten keuken', 'fr' => 'Cuisine ferm&eacute;', 'en' => '', 'de' => 'Geschlossene K&uuml;che')),
			4 => l(array('nl' => 'Kitchenette', 'fr' => 'Kitchinette', 'en' => '', 'de' => 'Kleine K&uuml;che')),
			5 => l(array('nl' => 'Grote keuken', 'fr' => 'Grande cuisine', 'en' => '', 'de' => 'Ger&auml;umige K&uuml;che')),
			6 => l(array('nl' => 'Open keuken', 'fr' => 'Cuisine ouverte', 'en' => '', 'de' => 'Offene K&uuml;che')),
			7 => l(array('nl' => 'Semi-open keuken', 'fr' => 'Cuisine mi-ouverte', 'en' => '', 'de' => 'Semi offene K&uuml;che'))
		);

		$availability_types = array(
			1 => l(array('nl' => 'Onmiddelijk', 'fr' => 'Tout de suite', 'en' => '', 'de' => 'Sofort')),
			2 => l(array('nl' => 'Bij akte', 'fr' => '&agrave; l\'acte', 'en' => '', 'de' => 'Bei Akte')),
			3 => l(array('nl' => 'Overeen te komen', 'fr' => '&agrave; convenir', 'en' => '', 'de' => 'Zu vereinbaren')),
			4 => l(array('nl' => 'Op datum', 'fr' => '&agrave; la date', 'en' => '', 'de' => 'Am Datun')),
		);

		$glazing_types = array(
			1 => l(array('nl' => 'Dubbel glas', 'fr' => 'Double vitrage', 'en' => '', 'de' => 'Doppeltes Glas')),
			2 => l(array('nl' => 'Deels dubbel glas', 'fr' => 'Double vitrage (partiel)', 'en' => '', 'de' => 'Doppeltes Glas (teils)')),
			3 => l(array('nl' => 'Enkel glas', 'fr' => 'Simple vitrage', 'en' => '', 'de' => 'Einzel Glas')),
			4 => l(array('nl' => 'Voorzetramen', 'fr' => 'survitrage', 'en' => '', 'de' => 'Vorsatzfenster')),
			5 => l(array('nl' => 'Andere', 'fr' => 'Autre', 'en' => '', 'de' => 'Andere'))
		);

		$window_types = array(
			1 => l(array('nl' => 'Aluminium', 'fr' => 'Aluminium', 'en' => 'Aluminium', 'de' => 'Aluminium')),
			2 => l(array('nl' => 'PVC', 'fr' => 'PVC', 'en' => 'PVC', 'de' => 'PVC')),
			3 => l(array('nl' => 'Hout', 'fr' => 'Bois', 'en' => '', 'de' => 'Holz')),
			4 => l(array('nl' => 'Andere', 'fr' => 'Autre', 'en' => '', 'de' => 'Andere'))
		);

		$heating_types = array(
			1 => l(array('nl' => 'Airco', 'fr' => 'Airco', 'en' => '', 'de' => 'Airco')),
			2 => l(array('nl' => 'Boiler', 'fr' => 'Chauffe-eau', 'en' => '', 'de' => 'Boiler')),
			3 => l(array('nl' => 'Huurboiler', 'fr' => 'Chauffe-eau de louage', 'en' => '', 'de' => 'Leihboiler')),
			4 => l(array('nl' => 'Centrale verwarming', 'fr' => 'chauffage central', 'en' => '', 'de' => 'Zentrale Heizung')),
			5 => l(array('nl' => 'Kolen', 'fr' => 'Charbons', 'en' => '', 'de' => 'Kohle')),
			6 => l(array('nl' => 'Combi boiler', 'fr' => 'Combi chauffe-eau', 'en' => '', 'de' => 'Combi Boiler')),
			7 => l(array('nl' => 'Condensatie boile', 'fr' => 'Chauffe-eau de condensation', 'en' => '', 'de' => 'Kondensator Boiler')),
			8 => l(array('nl' => 'Stadsverwarming', 'fr' => 'Chauffage urbain', 'en' => '', 'de' => 'Stadtheizung')),
			9 => l(array('nl' => 'Elektrische boiler', 'fr' => 'Chauffe-eau électrique', 'en' => '', 'de' => 'Elektrische Boiler')),
			10 => l(array('nl' => 'Elektrische huurboiler', 'fr' => 'Chauffe-eau électrique de louage', 'en' => '', 'de' => 'Elektrische Leihboiler')),
			11 => l(array('nl' => 'Elektrisch', 'fr' => '&Eacute;lectrique', 'en' => '', 'de' => 'Elektrisch')),
			12 => l(array('nl' => 'Haard', 'fr' => 'Feu Ouvert', 'en' => '', 'de' => 'Kamin')),
			13 => l(array('nl' => 'Haard mogelijk', 'fr' => 'Feu Ouvert possible', 'en' => '', 'de' => 'Kamin m&ouml;glich')),
			14 => l(array('nl' => 'Vloerverwarming', 'fr' => 'Chauffage par le sol', 'en' => '', 'de' => 'Bodenheizung')),
			15 => l(array('nl' => 'Deels vloerverwarming', 'fr' => 'Chauffage par le sol (partiel)', 'en' => '', 'de' => 'Bodenheizung (teils)')),
			16 => l(array('nl' => 'Gashaard', 'fr' => 'Feu ouvert au gaz', 'en' => '', 'de' => 'Kamin mit Gas')),
			17 => l(array('nl' => 'Gasvuur', 'fr' => 'Cuisini&egrave;re &agrave; gaz', 'en' => '', 'de' => 'Gasherd')),
			18 => l(array('nl' => 'Koude-warmteopslag', 'fr' => 'Stockage de froide et de chaleur', 'en' => '', 'de' => 'Kalte-W&auml;rme Aufschlag')),
			19 => l(array('nl' => 'Geiser', 'fr' => 'Chauffe-eau', 'en' => '', 'de' => 'Durchlauferhitzer')),
			20 => l(array('nl' => 'Huurgeiser', 'fr' => 'Chauffe-eau de louage', 'en' => '', 'de' => 'Leihdurchlauferhitzer')),
			21 => l(array('nl' => 'Stookolie', 'fr' => 'Mazout', 'en' => '', 'de' => 'Heiz&ouml;l')),
			22 => l(array('nl' => 'Warmtepomp', 'fr' => 'Pompe &agrave; chaleur', 'en' => '', 'de' => 'W&auml;rmepumpe')),
			23 => l(array('nl' => 'Warme luchtverwarming', 'fr' => 'Chauffage par circulation air chaud', 'en' => '', 'de' => 'W&auml;rme Luftheizung')),
			24 => l(array('nl' => 'Hoog rendement boiler', 'fr' => 'Boiler &agrave; haut rendement', 'en' => '', 'de' => 'Hohe Nutzleistung Boiler')),
			25 => l(array('nl' => 'Aardgas', 'fr' => 'Gaz naturel', 'en' => '', 'de' => 'Erdgas')),
			26 => l(array('nl' => 'Zonnecollector', 'fr' => 'Capteur solaire', 'en' => '', 'de' => 'Solarkollektor')),
			27 => l(array('nl' => 'Windmolen', 'fr' => '&eacute;olienne', 'en' => '', 'de' => 'Windm&uuml;hle')),
			28 => l(array('nl' => 'Andere', 'fr' => 'Autre', 'en' => '', 'de' => 'Andere')),
			29 => l(array('nl' => 'Geen', 'fr' => 'Pas', 'en' => '', 'de' => 'Kein')),
		);

		$land_use_designation_types = array(
			1 => l(array('nl' => 'Agrarisch gebied', 'fr' => '', 'en' => '', 'de' => 'Landwirtschaftliches Gebiet')),
			2 => l(array('nl' => 'Bosgebied', 'fr' => '', 'en' => '', 'de' => 'Waldgebiet')),
			3 => l(array('nl' => 'Dagrecreatie', 'fr' => '', 'en' => '', 'de' => 'Tageserholung')),
			4 => l(array('nl' => 'Verblijfrecreatie', 'fr' => '', 'en' => '', 'de' => 'Aufunthalterholung')),
			5 => l(array('nl' => 'Industriegebied voor ambachtelijke bedrijven of gebieden voor kleine en middelgrote ondernemingen', 'fr' => 'Pour des petites et grandes entretprises', 'en' => '', 'de' => 'Industriegebiet f&uuml;r Handwerksberufe oder kleine und middelgrosse Unternehmungen')),
			6 => l(array('nl' => 'Industriegebied', 'fr' => '', 'en' => '', 'de' => 'Industriegebiet')),
			7 => l(array('nl' => 'Landschappelijk waardevolle agrarisch gebied', 'fr' => '', 'en' => '', 'de' => 'Landschaftliches wertvolles agrarisches Gebiet')),
			8 => l(array('nl' => 'Natuurgebied', 'fr' => '', 'en' => '', 'de' => 'Naturgebiet')),
			9 => l(array('nl' => 'Natuurreservaat', 'fr' => '', 'en' => '', 'de' => 'Naturreservat')),
			10 => l(array('nl' => 'Andere', 'fr' => 'Autre', 'en' => '', 'de' => 'Andere')),
			11 => l(array('nl' => 'Landelijk parkgebied', 'fr' => '', 'en' => '', 'de' => 'L&auml;ndliches Parkgebiet')),
			12 => l(array('nl' => 'Woongebied met culturele, historische en/of esthetische waarde', 'fr' => '', 'en' => '', 'de' => 'Wohngebiet mit kulturellem, historischem und/oder etnischem Wert')),
			13 => l(array('nl' => 'Woongebied', 'fr' => '', 'en' => '', 'de' => 'Wohngebiet')),
			14 => l(array('nl' => 'Woongebied met landelijk karakter', 'fr' => '', 'en' => '', 'de' => 'Wohngebiet mit l&auml;ndlichem Charakter')),
			15 => l(array('nl' => 'Woonpark', 'fr' => '', 'en' => '', 'de' => 'Wohnpark')),
			16 => l(array('nl' => 'Woonuitbreidingsgebied', 'fr' => '', 'en' => '', 'de' => 'Wohnerweiterungsgebiet')),
		);

		$roof_types = array(
			1 => l(array('nl' => 'Composiet', 'fr' => '', 'en' => 'Composite roof', 'de' => '')),
			2 => l(array('nl' => 'Gekruisd puntdak', 'fr' => '', 'en' => 'Cross gable roof', 'de' => '')),
			3 => l(array('nl' => 'Koepeldak', 'fr' => '', 'en' => 'Dome roof', 'de' => '')),
			4 => l(array('nl' => 'Plat betonnen dak', 'fr' => '', 'en' => 'Flat concrete roof', 'de' => '')),
			5 => l(array('nl' => 'Plat dakleer dak', 'fr' => '', 'en' => 'Flat felt roof', 'de' => '')),
			6 => l(array('nl' => 'Plat dak', 'fr' => '', 'en' => 'Flat roof', 'de' => '')),
			7 => l(array('nl' => 'Plat houten dak', 'fr' => '', 'en' => 'Flat wooden roof', 'de' => '')),
			8 => l(array('nl' => 'Hellend dak', 'fr' => '', 'en' => 'Lean to roof', 'de' => '')),
			9 => l(array('nl' => 'Mansardedak', 'fr' => '', 'en' => 'Mansard roof', 'de' => '')),
			10 => l(array('nl' => 'Zadeldak', 'fr' => '', 'en' => 'Pitched roof', 'de' => '')),
			11 => l(array('nl' => 'Schilddak', 'fr' => '', 'en' => 'Shield roof', 'de' => '')),
			12 => l(array('nl' => 'Tentdak', 'fr' => '', 'en' => 'Tent roof', 'de' => ''))
		);

		$marketing_types = json_decode($this->features);

		// FINANCIEEL
		if(intval($this->price) && $this->show_price) $details['financieel'][l(array('nl'=>'Prijs','fr'=>'Prix','en' => 'Price', 'de'=>'Preis'))] = '&euro; '.number_format($this->price, 0, ',', '.');
		if(intval($this->cadastrall_income)) $details['financieel'][l(array('nl'=>'Kadastraal inkomen','fr'=>'Revenu Rcadastral','en' => 'Land registry income', 'de'=>'Kataster Wert'))] = '&euro; '.number_format($this->cadastrall_income, 0, ',', '.');
		if(intval($this->cadastrall_income_indexed)) $details['financieel'][l(array('nl'=>'Ge&iuml;ndexeerd kad.&nbsp;inkomen','fr'=>'Revenu cadastral index&eacute;','en' => 'Indexed land registry income', 'de'=>'Indiziertes Kataster Wert'))] = '&euro; '.number_format($this->cadastrall_income_indexed, 0, ',', '.');
		if(intval($this->communal_expenses)) $details['financieel'][l(array('nl'=>'Kosten','fr'=>'Charges','en' => 'Costs', 'de'=>'Kosten'))] = '&euro; '.number_format($this->communal_expenses, 0, ',', '.');
		// Onroerende voorheffing $details['financieel'][l(array('nl'=>'Onroerende voorheffing','fr'=>"Pr&eacute;compte immobilier",'en'=>'Land tax'))] = '&euro; '.number_format(str_replace(',','.',$whise_details[297]),0,',','.');
		if($this->availability) $details['financieel'][l(array('nl'=>'Ter beschikking stelling','fr'=>'Disponible','en' => 'Available', 'de'=>'Zu Verfügung stellen'))] = $availability_types[$this->availability];
		if($this->availability && $this->availability_date) $details['financieel'][l(array('nl'=>'Ter beschikking stelling','fr'=>'Disponible','en' => 'Available', 'de'=>''))] = $availability_types[$this->availability].'<br />'.strftime('%d %B %Y',$this->availability_date);

		// LIGGING

		// ALGEMEEN
		$details['algemeen'][l(array('nl' => 'Nieuwbouw', 'fr' => 'New immobilier', 'en' => 'New estate', 'de' => 'Neue Immobilien'))] = $this->newly_constructed ? l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja')) : l(array('nl' => 'Nee', 'fr' => 'Non', 'en' => 'No', 'de' => 'Nein')) ;
		$details['algemeen'][l(array('nl'=>'Bebouwing','fr'=>'D&eacuteveloppement','en' => 'Development', 'de'=>'Bebauung'))] = $this->style;
		if(intval($this->surface_livable)) $details['algemeen'][l(array('nl'=>'Bewoonbare oppervlakte','fr'=>'Surface habitable','en' => 'Habitable surface', 'de'=>'Bewohnbare Oberfläche'))] = number_format($this->surface_livable,0,',','.').' m&sup2;';
		$details['algemeen'][l(array('nl'=>'Verdieping','fr'=>'&Eacute;tage','en' => 'Floor', 'de'=>'Etage'))] = $this->floors;
		$details['algemeen'][l(array('nl'=>'Bouwjaar','fr'=>'Ann&eacute;e de construction','en' => 'Construction year', 'de'=>'Baujahr'))] = $this->construction_year;
		$details['algemeen'][l(array('nl'=>'Renovatiejaar','fr'=>'R&eacute;novation','en' => 'Renovation', 'de'=>'Renovierungjahr'))] = $this->renovation_year;
		if($this->has_terrace) $details['algemeen'][l(array('nl'=>'Terras','fr'=>'Terrace','en' => 'Terrace', 'de'=>''))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_terrace && intval($this->surface_terrace)) $details['algemeen'][l(array('nl'=>'Terras','fr'=>'Terrace','en' => 'Terrace', 'de'=>'Terrasse'))] = number_format($this->surface_terrace,0,',','.').' m&sup2;';
		if($this->has_balcony) $details['algemeen'][l(array('nl'=>'Balkon','fr'=>'Balcon','en' => 'Balcony', 'de'=>'Balkon'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_balcony && intval($this->surface_balcony)) $details['algemeen'][l(array('nl'=>'Balkon','fr'=>'Balcon','en' => 'Balcony', 'de'=>'Balkon'))] = number_format($this->surface_balcony,0,',','.').' m&sup2;';
		if($this->has_garage) $details['algemeen'][l(array('nl'=>'Garages','fr'=>'Garages','en' => 'Garages', 'de'=>'Garages'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_garage && $this->garages)$details['algemeen'][l(array('nl'=>'Garages','fr'=>'Garages','en' => 'Garages', 'de'=>'Garages'))] = $this->garages;
		if($this->has_parking) $details['algemeen'][l(array('nl'=>'Parkings','fr'=>'Parkings','en' => 'Parkings', 'de'=>'Parkplatz'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_parking && $this->parkings)$details['algemeen'][l(array('nl'=>'Parkings','fr'=>'Parkings','en' => 'Parkings', 'de'=>'Parkplatz'))] = $this->parkings;
		if($this->roof_type) $details['algemeen'][l(array('nl' => 'Daksoort', 'fr' => 'Type de toit', 'en' => 'Roof type', 'de' => 'Dachtyp'))] = $roof_types[$this->roof_type];

		// GROND
		if(intval($this->surface_terrain)) $details['grond'][l(array('nl'=>'Grondoppervlakte','fr'=>'Superficie terrain','en' => '', 'de'=>'Grundoberfläche'))] = number_format($this->surface_terrain,0,',','.').' m&sup2;';
		if($this->has_garden) $details['grond'][l(array('nl'=>'Tuin','fr'=>'Jardin','en' => 'Garden', 'de'=>'Garten'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_garden && intval($this->surface_garden)) $details['grond'][l(array('nl'=>'Tuin','fr'=>'Jardin','en' => 'Garden', 'de'=>'Garten'))] = number_format($this->surface_garden,0,',','.').' m&sup2;';
		if($this->orientation) $details['grond'][l(array('nl'=>'Ori&euml;ntatie tuin','fr'=>'Orientation du jardin','en' => 'Orientation of the garden', 'de'=>'Orientierung Garten'))] = $orientation_types[$this->orientation];
		if(intval($this->terrain_width_front)) $details['grond'][l(array('nl'=>'Breedte aan straatkant','fr'=>'Largeur à la rue','en' => 'Width at the street', 'de'=>'Breite Strassenseite'))] = number_format($this->terrain_width_front,0,',','.').' m&sup2;';
		if(intval($this->terrain_width)) $details['grond'][l(array('nl'=>'Terrein breedte','fr'=>'Largeur du terrain','en' => 'Land width', 'de'=>'Stegbreite'))] = number_format($this->terrain_width,0,',','.').' m&sup2;';
		if(intval($this->terrain_depth)) $details['grond'][l(array('nl'=>'Terrein diepte','fr'=>'Profondeur terrain','en' => 'Ground depth', 'de'=>'Terraintiefe'))] = number_format($this->terrain_depth,0,',','.').' m&sup2;';

		// INDELING
		if($this->has_bedrooms) $details['indeling'][l(array('nl'=>'Slaapkamers','fr'=>'Chambres','en' => 'Bedrooms', 'de'=>'Schlafzimmer'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_bedrooms && $this->bedrooms) $details['indeling'][l(array('nl'=>'Slaapkamers','fr'=>'Chambres','en' => 'Bedrooms', 'de'=>'Schlafzimmer'))] = $this->bedrooms;
		if($this->has_living) $details['indeling'][l(array('nl'=>'Woonkamer','fr'=>'Salle de s&eacute;jour','en' => 'Living room', 'de'=>'Wohnzimmer'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_living && intval($this->surface_living)) $details['indeling'][l(array('nl'=>'Woonkamer','fr'=>'Salle de s&eacute;jour','en' => 'Living room', 'de'=>'Wohnzimmer'))] = number_format($this->surface_living,0,',','.').' m&sup2;';
		if($this->has_kitchen) $details['indeling'][l(array('nl'=>'Keuken','fr'=>'Salle de s&eacute;jour','en' => 'Kitchen', 'de'=>'Küche'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if(intval($this->surface_kitchen)) $details['indeling'][l(array('nl'=>'Keuken','fr'=>'Salle de s&eacute;jour','en' => 'Kitchen', 'de'=>'Küche'))] = number_format($this->surface_kitchen,0,',','.').' m&sup2;';
		if($this->kitchen_type) $details['indeling'][l(array('nl'=>'Keuken','fr'=>'Salle de s&eacute;jour','en' => 'Kitchen', 'de'=>'Küche'))] = number_format($this->surface_kitchen,0,',','.').' m&sup2; <br />'.$kitchen_types[$this->kitchen_type];
		if($this->has_storage) $details['indeling'][l(array('nl'=>'Berging','fr'=>'D&eacute;barras','en' => 'Storage', 'de'=>'Abstellraum'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_storage && intval($this->surface_storage)) $details['indeling'][l(array('nl'=>'Berging','fr'=>'D&eacute;barras','en' => 'Storage', 'de'=>'Abstellraum'))] = number_format($this->surface_storage,0,',','.').' m&sup2;';
		if($this->has_office) $details['indeling'][l(array('nl'=>'Bureau','fr'=>'Bureau','en' => 'Bureau', 'de'=>''))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_office && $this->offices) $details['indeling'][l(array('nl'=>'Bureau','fr'=>'Bureau','en' => 'Bureau', 'de'=>'Büro'))] = $this->offices;
		// veranda
		if($this->has_bathroom) $details['indeling'][l(array('nl'=>'Badkamers','fr'=>'Salle de bain','en' => 'Bathrooms', 'de'=>'Badezimmer'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_bathroom && $this->bathrooms) $details['indeling'][l(array('nl'=>'Badkamers','fr'=>'Salle de bain','en' => 'Bathrooms', 'de'=>'Badezimmer'))] = $this->bathrooms;
		// douchekamers
		if($this->has_toilet) $details['indeling'][l(array('nl'=>'Toiletten','fr'=>'Toilettes','en' => 'Toilets', 'de'=>'Toiletten'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_toilet && $this->toilets) $details['indeling'][l(array('nl'=>'Toiletten','fr'=>'Toilettes','en' => 'Toilets', 'de'=>'Toiletten'))] = $this->toilets;
		if($this->has_cellar) $details['indeling'][l(array('nl'=>'Kelder','fr'=>'Cave','en' => 'Cellar', 'de'=>'Keller'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if($this->has_cellar && intval($this->surface_cellar)) $details['indeling'][l(array('nl'=>'Kelder','fr'=>'Cave','en' => 'Cellar', 'de'=>'Keller'))] = number_format($this->surface_cellar,0,',','.').' m&sup2;';
		if($this->has_attic) $details['indeling'][l(array('nl'=>'Zolder','fr'=>'Grenier','en' => 'Attic', 'de'=>'Dachboden'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));
		if(intval($this->surface_attic)) $details['indeling'][l(array('nl'=>'Zolder','fr'=>'Grenier','en' => 'Attic', 'de'=>'Dachboden'))] = number_format($this->surface_attic,0,',','.').' m&sup2;';

		// COMFORT
		if($this->furnished) $details['comfort'][l(array('nl'=>'Gemeubeld','fr'=>'Meubl&eacute;','en'=> 'Furnished', 'de'=>'Möbliert'))] = l(array('nl' => 'Ja', 'fr' => 'Oui','en'=> 'Yes', 'de' => 'Ja'));
		if($this->has_alarm) $details['comfort'][l(array('nl'=>'Alarm','fr'=>'Alarme','en'=> 'Alarm', 'de'=>'Alarm'))] = l(array('nl' => 'Ja', 'fr' => 'Oui','en'=> 'Yes', 'de' => 'Ja'));
		if($this->has_elevator) $details['comfort'][l(array('nl'=>'Lift','fr'=>'Ascenseur','en'=> 'Elevator', 'de'=>'Aufzug'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en'=> 'Yes', 'de' => 'Ja'));
		if($this->has_roller_blinds) $details['comfort'][l(array('nl'=>'Rolluiken','fr'=>'Volets','en'=> 'Blinds', 'de'=>'Rolladen'))] = l(array('nl' => 'Ja', 'fr' => 'Oui','en'=> 'Yes',  'de' => 'Ja'));
		if($this->has_airco) $details['comfort'][l(array('nl'=>'Air conditioning','fr'=>'Air conditioning','en'=> 'Air conditioning', 'de'=>'Klimatisierung'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en'=> 'Yes', 'de' => 'Ja'));
		if($this->has_pool) $details['comfort'][l(array('nl'=>'Zwembad','fr'=>'Piscine','en'=> 'Pool', 'de'=>'Schwimmbad'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en'=> 'Yes', 'de' => 'Ja'));
		if($this->has_pool && $this->pool_comment) $details['comfort'][l(array('nl'=>'Zwembad','fr'=>'Piscine','en'=> 'Pool', 'de'=>'Schwimmbad'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en'=> 'Yes', 'de' => 'Ja')).'<br />'.$this->pool_comment;

		// ENERGIE
		if($this->epc) $details['energie'][l(array('nl' => 'EPC', 'fr' => 'PEB', 'en' => 'EPC', 'de' => 'EPC'))] = $this->epc.' kWh/m&sup2;';
		if($this->epc_certificate) $details['energie'][l(array('nl' => 'EPC Certificaat', 'fr' => 'Certification PEB', 'en' => 'Energy certificate', 'de' => 'EPC Zertifikat'))] = $this->epc_certificate;
		if($this->elevel) $details['energie'][l(array('nl'=>'E-peil','fr'=>'Niveau &eacute;nerg&eacute;tique','en'=>'Energy level', 'de'=>'E-Niveau'))] = $this->elevel;
		if($this->klevel) $details['energie'][l(array('nl'=>'K-peil','fr'=>'Niveau &eacute;nerg&eacute;tique','en'=>'Energy level', 'de'=>'K-Niveau'))] = $this->klevel;
		if($this->glazing_type){
			$glazing_types_string = '';
			foreach (explode(',',$this->glazing_type) as $i => $glazing_type) {
				if($i > 0) $glazing_types_string .= '<br />';
				$glazing_types_string .= $glazing_types[$glazing_type];
			}
			$details['energie'][l(array('nl'=>'Type beglazing','fr'=>'Type de vitrage','en'=>'Glazing type', 'de'=>'Type Begläzung'))] = $glazing_types_string;
		}
		if($this->window_type) $details['energie'][l(array('nl'=>'Schrijnwerkerij','fr'=>'Menuiserie','en'=>'Joinery', 'de'=>'Schreiner'))] = $window_types[$this->window_type];
		if($this->diagnostics_certificate_date) $details['energie'][l(array('nl'=>'Elektriciteitskeuring','fr'=>'Certificat d\'&eacute;lectricit&eacute;','en'=>'Electricity certificate', 'de'=>'Elektrizitätprüfung'))] = strftime('%d %B %Y',$this->diagnostics_certificate_date);
		if($this->heating_source){
			$heating_types_string = '';
			foreach (explode(',',$this->heating_source) as $i => $heating_source) {
				if($i > 0) $heating_types_string .= '<br />';
				$heating_types_string .= $heating_types[$heating_source];
			}
			$details['energie'][l(array('nl'=>'Verwarming','fr'=>'Chauffage','en'=>'Heating type', 'de'=>'Heizung'))] = $heating_types_string;
		}
		// zonnepanelen
		// zonneboiler

		// STEDENBOUW
		if($this->land_use_designation) $details['stedenbouw'][l(array('nl'=>'Bestemming','fr'=>'Affectation','en'=>'Destination', 'de'=>'Bestimmung'))] = $land_use_designation_types[$this->land_use_designation];
		$details['stedenbouw'][l(array('nl'=>'Bouwvergunning','fr'=>'Permis de b&acirc;tir','en'=>'Building permission', 'de'=>''))] = l(array('nl' => 'Niet meedegedeeld', 'fr' => 'Non communiqué', 'en' => 'Not disclosed', 'de' => 'Baugenehmigung'));
		if($this->planning_permission) $details['stedenbouw'][l(array('nl'=>'Bouwvergunning','fr'=>'Permis de b&acirc;tir','en'=>'Building permission', 'de'=>'Baugenehmigung'))] = $this->planning_permission ? l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja')) : l(array('nl' => 'Nee', 'fr' => 'Non', 'en' => 'No', 'de' => 'Nein'));
		$details['stedenbouw'][l(array('nl'=>'Verkavelingvergunning','fr'=>'Permis de lotir','en'=>'Parcelling permission', 'de'=>'Parzellierungnehmigung'))] = l(array('nl' => 'Niet meedegedeeld', 'fr' => 'Non communiqué', 'en' => 'Not disclosed', 'de' => ''));
		if($this->subdivision_permit) $details['stedenbouw'][l(array('nl'=>'Verkavelingvergunning','fr'=>'PPermis de lotir','en'=>'Parcelling permission', 'de'=>'Parzellierungnehmigung'))] = $this->subdivision_permit ? l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja')) : l(array('nl' => 'Nee', 'fr' => 'Non', 'en' => 'No', 'de' => 'Nein'));
		$details['stedenbouw'][l(array('nl'=>'Voorkooprecht','fr'=>'Droit de pr&eacute;emption','en'=>'Right of pre-emption', 'de'=>''))] = l(array('nl' => 'Niet meedegedeeld', 'fr' => 'Non communiqué', 'en' => 'Not disclosed', 'de' => 'Vorkaufrecht'));
		if($this->preemption_right) $details['stedenbouw'][l(array('nl'=>'Voorkooprecht','fr'=>'Droit de pr&eacute;emption','en'=>'Right of pre-emption', 'de'=>'Vorkaufrecht'))] = $this->preemption_right ? l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja')) : l(array('nl' => 'Nee', 'fr' => 'Non', 'en' => 'No', 'de' => 'Nein'));
		$details['stedenbouw'][l(array('nl'=>'Dagvaarging','fr'=>'Intimation en justice','en'=>'Intimation', 'de'=>''))] = l(array('nl' => 'Niet meedegedeeld', 'fr' => 'Non communiqué', 'en' => 'Not disclosed', 'de' => 'Vorladung'));
		if($this->urbanism_citation) $details['stedenbouw'][l(array('nl'=>'Dagvaarging','fr'=>'Intimation en justice','en'=>'Intimation', 'de'=>'Vorladung'))] = $this->urbanism_citation ? l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja')) : l(array('nl' => 'Nee', 'fr' => 'Non', 'en' => 'No', 'de' => 'Nein'));
		if($this->judicial_decision) $details['stedenbouw'][l(array('nl'=>'Rechterlijke beslissing','fr'=>'D&eacute;cision judiciaire','en'=>'Judicial decision', 'de'=>'Gerichtsentscheidung'))] = $this->judicial_decision ? l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja')) : l(array('nl' => 'Nee', 'fr' => 'Non', 'en' => 'No', 'de' => 'Nein'));
		// Overstroming
		// Afgebakend
		if($this->is_protected) $details['stedenbouw'][l(array('nl'=>'Beschermd erfgoed','fr'=>'Patrimoine prot&eacute;g&eacute;','en'=>'Protected heritage', 'de'=>'Geschütztes Erbe'))] = l(array('nl' => 'Ja', 'fr' => 'Oui', 'en' => 'Yes', 'de' => 'Ja'));


		$this->details = $this->details_array_filter($details);
	}

	private function details_array_filter($array) {
		foreach ($array as $key => $var) {
			if (is_array($var)) {
				$var = $this->details_array_filter($var);
				$array[$key] = $var;
			}
			if (!$var) unset($array[$key]);
		}
		return $array;
	}
	
}
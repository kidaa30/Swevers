<?php
/**
 * This class provides communication with Skarabee's server.
 *
 * To be configured by providing skarabee_username and skarabee_password in global config.php
 *
 * @author       Sam Feyaerts (sam@fw4.be)
 * @copyright    {@link http://www.fw4.be FW4} - 2015
 */
	
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skarabee extends Controller {
	
	private static $client = false;

	/**
	* Imports available properties from and communicates import progress with Skarabee's servers
	*/
	public function import() {
		
		$client = self::get_client();
	    
	    $types = array(
		    'LOT' => 2,
		    'MODEL' => 4,
		    'PROJECT' => 3,
		    'TRANSACTION' => 1
	    );
	    $categories = array(
		    'Dwelling' => 1,
		    'Flat' => 2,
		    'Land' => 3,
		    'ServiceFlat' => 4,
		    'Room' => 5,
		    'Parking' => 6,
		    'Other' => 7,
		    'Catering' => 8,
		    'Office' => 9,
		    'Industry' => 10,
		    'Shop' => 11,
		    'ProfessionalOther' => 12,
		    'ProfessionalLand' => 13
	    );
	    $subcategories = array(
		    'Dwelling' => array('nl'=>'Huis','fr'=>'Maison','en'=>'House','de'=>'Haus'),
		    'Flat' => array('nl'=>'Appartement','fr'=>'Appartement','en'=>'Apartment','de'=>'Appartement'),
		    'Land' => array('nl'=>'Grond','fr'=>'Terrain','en'=>'Land','de'=>'Boden'),
		    'ServiceFlat' => array('nl'=>'Serviceflat','fr'=>'Service flat','en'=>'Service flat','de'=>'Serviceflat'),
		    'Room' => array('nl'=>'Kamer','fr'=>'Chambre','en'=>'Room','de'=>'Zimmer'),
		    'Parking' => array('nl'=>'Parking','fr'=>'Parking','en'=>'Parking','de'=>'Parkplatz'),
		    'Other' => array('nl'=>'Andere','fr'=>'Autre','en'=>'Other','de'=>'Andere'),
		    'Catering' => array('nl'=>'Horeca','fr'=>'Horeca','en'=>'Catering','de'=>'Gaststättengewerbe'),
			'Industry' => array('nl'=>'Bedrijfsterrein','fr'=>'Surface industriel','en'=>'Industrial property','de'=>'Betriebsterrain'),
			'Office' => array('nl'=>'Kantoor','fr'=>'Bureau','en'=>'Office','de'=>'Büro'),
		    'Shop' => array('nl'=>'Winkel','fr'=>'Magasin','en'=>'Shop','de'=>'Laden'),
		    'ProfessionalOther' => array('nl'=>'Professioneel pand','fr'=>'Bien professionel','en'=>'Professional property','de'=>'Professionelle Immobilien'),
		    'ProfessionalLand' => array('nl'=>'Grond','fr'=>'Terrain','en'=>'Land','de'=>'Boden'),
			
			'ArchitectDwelling' => array('nl'=>'Architectenwoning','fr'=>'Maison architecte','en'=>'Architect house','de'=>'Architektenwohnung'),
			'Bastide' => array('nl'=>'Bastide','fr'=>'Bastide','en'=>'Bastide','de'=>'Bastide'),
			'Bungalow' => array('nl'=>'Bungalow','fr'=>'Plain-pied','en'=>'Bungalow','de'=>'Bungalow'),
			'CanalHouse' => array('nl'=>'Grachtenpand','fr'=>'Canal maison','en'=>'Canal house','de'=>'Grachtenhaus'),
			'Castle' => array('nl'=>'Kasteel','fr'=>'Château','en'=>'Castle','de'=>'Schloss'),
			'Chalet' => array('nl'=>'Chalet','fr'=>'Chalet','en'=>'Chalet','de'=>'Chalet'),
			'CityDwelling' => array('nl'=>'Stadswoning','fr'=>'Maison de ville','en'=>'City house','de'=>'Stadtswohnung'),
			'CourtyardHouse' => array('nl'=>'Hofjeswoning','fr'=>'Maison avec cour','en'=>'Courtyard house','de'=>'Hofhaus'),
			'Cottage' => array('nl'=>'Landhuis','fr'=>'Maison de campagne','en'=>'Cottage','de'=>'Landhaus'),
			'Domain' => array('nl'=>'Landgoed','fr'=>'Domaine','en'=>'Domain','de'=>'Domäne'),
			'DriveInHouse' => array('nl'=>'Drive-inwoning','fr'=>'Bel-étage','en'=>'Drive-in house','de'=>'Drive-in-Haus'),
			'DykeHouse' => array('nl'=>'Dijkwoning','fr'=>'Maison sur digue','en'=>'Dyke house','de'=>'Dyke Haus'),
			'ExclusiveVilla' => array('nl'=>'Exclusieve villa','fr'=>'Villa exclusive','en'=>'Exclusive villa','de'=>'exklusive villa'),
			'Farm' => array('nl'=>'Boerderij','fr'=>'Ferme','en'=>'Farm','de'=>'Bauernhof'),
			'Farmhouse' => array('nl'=>'Fermette','fr'=>'Fermette','en'=>'Farmhouse','de'=>'Fermette'),
			'FishermanDwelling' => array('nl'=>'Vissershuis','fr'=>'Maison de pêcheur','en'=>'Fisherman house','de'=>'Fischershaus'),
			'GroundfloorBasementHouse' => array('nl'=>'Bel-étage','fr'=>'Bel-étage','en'=>'Bel-étage','de'=>'Bel-étage'),
			'HotelDwelling' => array('nl'=>'Stadspaleis','fr'=>'Hôtel particulier','en'=>'Townhouse','de'=>'Reihenhaus'),
			'LordHouse' => array('nl'=>'Herenhuis','fr'=>'Maison de maître','en'=>'Manor','de'=>'Herrschafliches Haus'),
			'Mansion' => array('nl'=>'Landhuis','fr'=>'Manoir','en'=>'Mansion','de'=>'Landhaus'),
			'Mas' => array('nl'=>'Herenboerderij','fr'=>'Mas','en'=>'Farmhouse','de'=>'Bauernhaus'),
			'PatioBungalow' => array('nl'=>'Patiowoning','fr'=>'Maison patio','en'=>'Patio house','de'=>'Patio Haus'),
			'PileDwelling' => array('nl'=>'Paalwoning','fr'=>'Maison palafittique','en'=>'Stilt house','de'=>'Stelzenhaus'),
			'Presbytery' => array('nl'=>'Pastorie','fr'=>'Presbytère','en'=>'Presbytery','de'=>'Pfarrhaus'),
			'SemiBungalow' => array('nl'=>'Semi-bungalow','fr'=>'Semi-bungalow','en'=>'Semi-bungalow','de'=>'Halbbungalow'),
			'SingleFamilyDwelling' => array('nl'=>'Eengezinswoning','fr'=>'Maison unifamiliale','en'=>'Single family house','de'=>'Einfamilienhaus'),
			'SmallDwelling' => array('nl'=>'Maisonnette','fr'=>'Maisonnette','en'=>'Small house','de'=>'kleines haus'),
			'SplitLevelHouse' => array('nl'=>'Split-level woning','fr'=>'Maison à paliers multiples','en'=>'Split-level house','de'=>'Split-Level-Haus'),
			'StandingDwelling' => array('nl'=>'Prestigewoning','fr'=>'Propriété de prestige','en'=>'Luxury house','de'=>'Luxus-Haus'),
			'TownDwelling' => array('nl'=>'Dorpshuis','fr'=>'Maison de village','en'=>'Townhouse','de'=>'Reihenhaus'),
			'Villa' => array('nl'=>'Villa','fr'=>'Villa','en'=>'Villa','de'=>'Villa'),
			'WaterDwelling' => array('nl'=>'Waterwoning','fr'=>'Maison flottante','en'=>'Water dwelling','de'=>'Wasser-Wohnung'),
			
			'BasementFlat' => array('nl'=>'Kelder appartement','fr'=>'Souterrain','en'=>'Basement flat','de'=>'Souterrainwohnung'),
			'Duplex' => array('nl'=>'Duplex','fr'=>'Duplex','en'=>'Duplex','de'=>'Duplex'),
			'GroundfloorFlat' => array('nl'=>'Gelijkvloers appartement','fr'=>'Rez de chaussée','en'=>'Ground floor flat','de'=>'Erdgeschoss-Wohnung'),
			'GroundfloorBasementFlat' => array('nl'=>'Bel-étage flat','fr'=>'Appartement bel-étage','en'=>'Ground floor flat','de'=>'Erdgeschoss-Wohnung'),
			'IndependentRoom' => array('nl'=>'Aparte kamer','fr'=>'Chambre de bonne','en'=>'Separate room','de'=>'Separates Zimmer'),
			'Loft' => array('nl'=>'Loft','fr'=>'Loft','en'=>'Loft','de'=>'Loft'),
			'Maisonette' => array('nl'=>'Maisonnette','fr'=>'Maisonnette','en'=>'Maisonnette','de'=>'Maisonnette'),
			'Penthouse' => array('nl'=>'Penthouse','fr'=>'Penthouse','en'=>'Penthouse','de'=>'Penthouse'),
			'RoofAppartement' => array('nl'=>'Dakappartement','fr'=>'Mansarde','en'=>'Roof apartment','de'=>'Dach Appartement'),
			'Studio' => array('nl'=>'Studio','fr'=>'Studio','en'=>'Studio','de'=>'Studio'),
			'Triplex' => array('nl'=>'Triplex','fr'=>'Triplex','en'=>'Triplex','de'=>'Triplex'),
			'UpstairsDwelling' => array('nl'=>'Bovenwoning','fr'=>'Habitation à l\'étage','en'=>'Upstairs home','de'=>'Upstairs Hause'),

			'StudentRoom' => array('nl'=>'Studentenkamer','fr'=>'Chambre d\'étudiant','en'=>'Student room','de'=>'Studentenzimmer'),

			'Carport' => array('nl'=>'Carport','fr'=>'Carport','en'=>'Carport','de'=>'Carport'),
			'Garage' => array('nl'=>'Garage','fr'=>'Garage','en'=>'Garage','de'=>'Garage'),
			'GarageBox' => array('nl'=>'Garagebox','fr'=>'Garage box','en'=>'Garage box','de'=>'Garage box'),
			'ParkingCellar' => array('nl'=>'Parkeerkelder','fr'=>'Parking sous-sol','en'=>'Parking cellar','de'=>'Parkkeller'),
			'ParkingLot' => array('nl'=>'Parkeerterrein','fr'=>'Terrain de parking','en'=>'Parking lot','de'=>'Parkterrain'),
			'ParkingPlace' => array('nl'=>'Parkeerplaats','fr'=>'Parking','en'=>'Parking spot','de'=>'Parkplatz'),
			'Barn' => array('nl'=>'Schuur','fr'=>'Grange','en'=>'Barn','de'=>'Scheune'),
			'Caravan' => array('nl'=>'Caravan','fr'=>'Caravane','en'=>'Caravan','de'=>'Karawane'),
			'Forest' => array('nl'=>'Bos','fr'=>'Forêt','en'=>'Forest','de'=>'Wald'),
			'HolidayHouse' => array('nl'=>'Vakantiewoning','fr'=>'Gîte','en'=>'Holiday house','de'=>'Ferienwohnung'),
			'HouseBoat' => array('nl'=>'Woonboot','fr'=>'Péniche','en'=>'Houseboat','de'=>'Wohnbot'),
			'PastureLand' => array('nl'=>'Weide','fr'=>'Pâturage','en'=>'Pasture','de'=>'Wiese'),
			'Pavillion' => array('nl'=>'Clubhuis','fr'=>'Pavillion','en'=>'Pavillion','de'=>'Clubhaus'),
			'RecreationGround' => array('nl'=>'Recreatiegrond','fr'=>'Parcelle de récréation','en'=>'Recreational land','de'=>'Freizeit-Land'),
			'WatermillHouse' => array('nl'=>'Watermolen','fr'=>'Moulin à eau','en'=>'Watermill','de'=>'Wassermühle'),
			'WindmillHouse' => array('nl'=>'Windmomen','fr'=>'Moulin à vent','en'=>'Windmill','de'=>'Windmühle'),
			
			'Bar' => array('nl'=>'Bar','fr'=>'Bar','en'=>'Bar','de'=>'Bar'),
			'Cafe' => array('nl'=>'Café','fr'=>'Café','en'=>'Café','de'=>'Wirtschaft'),
			'Disco' => array('nl'=>'Discotheek','fr'=>'Discothèque','en'=>'Disco','de'=>'Diskothek'),
			'Hotel' => array('nl'=>'Hotel','fr'=>'Hôtel','en'=>'Hotel','de'=>'Hotel'),
			'MotorwayRestaurant' => array('nl'=>'Wegrestaurant','fr'=>'Relais routier','en'=>'Roadside diner','de'=>'Raststätte'),
			'PartyHall' => array('nl'=>'Feestzaal','fr'=>'Salle communalle','en'=>'Banquet hall','de'=>'Festzentrum'),
			'Restaurant' => array('nl'=>'Restaurant','fr'=>'Restaurant','en'=>'Restaurant','de'=>'Restaurant'),
			'TeaRoom' => array('nl'=>'Eetcafé','fr'=>'Tea-room','en'=>'Tearoom','de'=>'Esswirtschaft'),
			
			'Services' => array('nl'=>'Servicekantoor','fr'=>'Bureau de service','en'=>'Service office','de'=>'Service Büro'),
			
			'Hangar' => array('nl'=>'Hangar','fr'=>'Hangar','en'=>'Hangar','de'=>'Hangar'),
			'Warehouse' => array('nl'=>'Opslagruimte','fr'=>'Entrepôt','en'=>'Warehouse','de'=>'Lagerraum'),
			'Workshop' => array('nl'=>'Atelier','fr'=>'Atelier','en'=>'Atelier','de'=>'Atelier'),
			
			'Boutique' => array('nl'=>'Boetiek','fr'=>'Boutique','en'=>'Boutique','de'=>'Boutique'),
			'Showroom' => array('nl'=>'Showroom','fr'=>'Showroom','en'=>'Showroom','de'=>'Showroom'),
			'Practice' => array('nl'=>'Praktijk','fr'=>'Cabinet','en'=>'Practice','de'=>'Praxis'),
			
			'Agricultural' => array('nl'=>'Landbouwgrond','fr'=>'Terrain agricole','en'=>'Farmland','de'=>'Ackerland'),
			'ArableLand' => array('nl'=>'Akker','fr'=>'Champ','en'=>'Field','de'=>'Acker'),
			
			'Orchard' => array('nl'=>'Boomgaard','fr'=>'Verger','en'=>'Orchard','de'=>'Obstgarten'),
			'Shop' => array('nl'=>'Winkel','fr'=>'Magasin','en'=>'Shop','de'=>'Laden'),
			'Vineyard' => array('nl'=>'Wijngaard','fr'=>'Vignoble','en'=>'Vineyard','de'=>'Weingarten')
	    );
	    $subcategory_ids = array_flip(array_merge(array(''),array_keys($subcategories)));
	    
	    $styles = array(
		    'Dwelling_Corner' => array('nl'=>'Hoekwoning','fr'=>'Maison de coin','en'=>'Corner house','de'=>'Eckwohnung'),
		    'Dwelling_Detached' => array('nl'=>'Vrijstaand','fr'=>'Individuelle','en'=>'Detached','de'=>'Alleinstehend'),
		    'Dwelling_Linked' => array('nl'=>'Rijhuis','fr'=>'Maison de ville','en'=>'Linked','de'=>'Reihenhaus'),
		    'Dwelling_Quadrant' => array('nl'=>'Kwadrantwoning','fr'=>'Maison quadrant','en'=>'Quadrant house','de'=>'Quadrant Haus'),
		    'Dwelling_SemiDetached' => array('nl'=>'Half-open bebouwing','fr'=>'Trois façades','en'=>'Semi-detached','de'=>'Quadrant Haus'),
		    'Dwelling_Terraced' => array('nl'=>'Rijwoning','fr'=>'Deux façades','en'=>'Terraced','de'=>'Rijwoning'),
		    'Flat_Corridor' => array('nl'=>'Corridorflat','fr'=>'Corridor plat','en'=>'Corridor flat','de'=>'Corridor Wohnung'),
		    'Flat_Gallery' => array('nl'=>'Galerij','fr'=>'Galerie','en'=>'Gallery','de'=>'Galerie'),
		    'Flat_Highrise' => array('nl'=>'Hoogbouw','fr'=>'Grande hauteur','en'=>'Highrise','de'=>'Hochbau'),
		    'Flat_Porch' => array('nl'=>'Portiekflat','fr'=>'Porche','en'=>'Porch','de'=>'Trobogen Appartement'),
		    'Flat_Condominium' => array('nl'=>'Condominium','fr'=>'Copropriété','en'=>'Condo','de'=>'Condominium'),
		    'Flat_Villa' => array('nl'=>'Villa-appartement','fr'=>'Dans une villa','en'=>'In a villa','de'=>'Villa-Appartement'),
		    'Flat_Townhouse' => array('nl'=>'In stadswoning','fr'=>'Dans une maison de village','en'=>'In a townhouse','de'=>'In stadtwohung'),
		    'Flat_Haussmann' => array('fr'=>'Haussmannien','en'=>'Haussmannian','de'=>'Haussmann'),
		    'Land_Corner' => array('nl'=>'Hoekpand','fr'=>'Sur un angle','en'=>'At a corner','de'=>'Eckwohnung'),
		    'Land_Detached' => array('nl'=>'Open bebouwing','fr'=>'Quatre façades','en'=>'Detached construction','de'=>'Offene Bebauung'),
		    'Land_Linked' => array('nl'=>'Gesloten bebouwing','fr'=>'Deux façades','en'=>'Linked construction','de'=>'Verlinkte Bau'),
		    'Land_SemiDetached' => array('nl'=>'Half-open bebouwing','fr'=>'Trois façades','en'=>'Semi-detached construction','de'=>'Quadrant Haus'),
		    'Land_Terraced' => array('nl'=>'Gesloten bebouwing','fr'=>'Deux façades','en'=>'Terraced construction','de'=>'Reihen Bau'),
		    'ServiceFlat_Corridor' => array('nl'=>'Corridorflat','fr'=>'Corridor plat','en'=>'Corridor flat','de'=>'Corridor Wohnung'),
		    'ServiceFlat_Gallery' => array('nl'=>'Galerij','fr'=>'Galerie','en'=>'Gallery','de'=>'Galerie'),
		    'ServiceFlat_Porch' => array('nl'=>'Portiekflat','fr'=>'Porche','en'=>'Porch','de'=>'Trobogen Appartement'),
		    'ServiceFlat_Condominium' => array('nl'=>'Condominium','fr'=>'Copropriété','en'=>'Condo','de'=>'Condominium'),
		    'ServiceFlat_Villa' => array('nl'=>'Villa-appartement','fr'=>'Dans une villa','en'=>'In a villa','de'=>'Villa-Appartement'),
		    'ServiceFlat_Townhouse' => array('nl'=>'In stadswoning','fr'=>'Dans une maison de village','en'=>'In a townhouse','de'=>'In stadtwohung'),
		    'ServiceFlat_Haussmann' => array('fr'=>'Haussmannien','en'=>'Haussmannian','de'=>'Haussmann'),
		    'Room_InComplex' => array('nl'=>'In complex','fr'=>'Dans un complexe','en'=>'In a complex','de'=>'In einem komplexen'),
		    'Room_InDwelling' => array('nl'=>'In een huis','fr'=>'Dans un maison','en'=>'In a house','de'=>'In einem Haus'),
		    'Room_InFlat' => array('nl'=>'In appartement','fr'=>'Dans une appartement','en'=>'In an apartment','de'=>''),
		    'Room_Townhouse' => array('nl'=>'In stadswoning','fr'=>'Dans une maison de village','en'=>'In a townhoue','de'=>'Ist in einer Wohnung'),
		    'Parking_Annex' => array('nl'=>'Aangebouwd','fr'=>'Attaché','en'=>'Attached','de'=>'Befestigt'),
		    'Parking_Inbuilt' => array('nl'=>'Inpandig','fr'=>'Intégré','en'=>'Inbuilt','de'=>'Integriert'),
		    'Parking_Detached' => array('nl'=>'Vrijstaand','fr'=>'Détaché','en'=>'Detached','de'=>'Freistehend'),
		    'Other_Corner' => array('nl'=>'Hoekpand','fr'=>'Sur un angle','en'=>'Corner property','de'=>'Eckwohnung'),
		    'Other_Detached' => array('nl'=>'Vrijstaand','fr'=>'Détaché','en'=>'Detached','de'=>'Alleinstehend'),
		    'Other_Linked' => array('nl'=>'Geschakeld','fr'=>'Deux façades','en'=>'Linked','de'=>'Verlinkte Bau'),
		    'Other_Quadrant' => array('nl'=>'Kwadrantpand','fr'=>'Bien quadrant','en'=>'Quadrant property','de'=>'Kwadrantpand'),
		    'Other_SemiDetached' => array('nl'=>'Half-open bebouwing','fr'=>'Trois façades','en'=>'Semi-detached','de'=>'Quadrant Haus'),
		    'Other_Terraced' => array('nl'=>'Gesloten bebouwing','fr'=>'Deux façades','en'=>'Terraced','de'=>'Verlinkte Bau'),
		    'Catering_Corner' => array('nl'=>'Op hoek','fr'=>'Sur un angle','en'=>'At a corner','de'=>'An der Ecke'),
		    'Catering_Detached' => array('nl'=>'Vrijstaand','fr'=>'Individuelle','en'=>'Detached','de'=>'Alleinstehend'),
		    'Catering_Linked' => array('nl'=>'Schakel','fr'=>'Deux façades','en'=>'Linked','de'=>'Link'),
		    'Catering_Quadrant' => array('nl'=>'Kwadrant','fr'=>'Quadrant','en'=>'Quadrant','de'=>'Quadrant'),
		    'Catering_SemiDetached' => array('nl'=>'Half-open bebouwing','fr'=>'Trois façades','en'=>'Semi-detached','de'=>'Quadrant Haus'),
		    'Catering_Terraced' => array('nl'=>'Gesloten bebouwing','fr'=>'Deux façades','en'=>'Terraced','de'=>'Verlinkte Bau'),
		    'Catering_InShoppingCenter' => array('nl'=>'Shopping center','fr'=>'Centre commercial','en'=>'Shopping center','de'=>'Shopping Zentrum'),
		    'Office_InOfficeBuilding' => array('nl'=>'Kantoorgebouw','fr'=>'Immeuble de bureaux','en'=>'Office building','de'=>'Geschäftsgebäude'),
		    'Office_InShoppingCenter' => array('nl'=>'Shopping center','fr'=>'Centre commercial','en'=>'Shopping center','de'=>'Shopping Zentrum'),
		    'Industry_Detached' => array('nl'=>'Vrijstaand','fr'=>'Individuelle','en'=>'Detached','de'=>'Alleinstehend'),
		    'Shop_Corner' => array('nl'=>'Op hoek','fr'=>'Sur un angle','en'=>'At a corner','de'=>'An der Ecke'),
		    'Shop_Detached' => array('nl'=>'Vrijstaand','fr'=>'Individuelle','en'=>'Detached','de'=>'Alleinstehend'),
		    'Shop_Linked' => array('nl'=>'Schakel','fr'=>'Deux façades','en'=>'Linked','de'=>'Link'),
		    'Shop_Quadrant' => array('nl'=>'Kwadrant','fr'=>'Quadrant','en'=>'Quadrant','de'=>'Quadrant'),
		    'Shop_SemiDetached' => array('nl'=>'Half-open bebouwing','fr'=>'Trois façades','en'=>'Semi-detached','de'=>'Quadrant Haus'),
		    'Shop_Terraced' => array('nl'=>'Gesloten bebouwing','fr'=>'Deux façades','en'=>'Terraced','de'=>'Verlinkte Bau'),
		    'Shop_InShoppingCenter' => array('nl'=>'Shopping center','fr'=>'Centre commercial','en'=>'Shopping center','de'=>'Shopping Zentrum'),
		    'ProfessionalOther_Detached' => array('nl'=>'Vrijstaand','fr'=>'Individuelle','en'=>'Detached','de'=>'Alleinstehend'),
		    'ProfessionalLand_Corner' => array('nl'=>'Op hoek','fr'=>'Sur un angle','en'=>'At a corner','de'=>'An der Ecke'),
		    'ProfessionalLand_Detached' => array('nl'=>'Vrijstaand','fr'=>'Individuelle','en'=>'Detached','de'=>'Alleinstehend'),
		    'ProfessionalLand_Linked' => array('nl'=>'Schakel','fr'=>'Deux façades','en'=>'Linked','de'=>'Link'),
		    'ProfessionalLand_Quadrant' => array('nl'=>'Kwadrant','fr'=>'Quadrant','en'=>'Quadrant','de'=>'Quadrant'),
		    'ProfessionalLand_SemiDetached' => array('nl'=>'Half-open bebouwing','fr'=>'Trois façades','en'=>'Semi-detached','de'=>'Quadrant Haus'),
		    'ProfessionalLand_Terraced' => array('nl'=>'Gesloten bebouwing','fr'=>'Deux façades','en'=>'Terraced','de'=>'Verlinkte Bau'),
		    'ProfessionalLand_InShoppingCenter' => array('nl'=>'Shopping center','fr'=>'Centre commercial','en'=>'Shopping center','de'=>'Shopping Zentrum'),
		);
		
		$purposes = array(
			'FOR_RENT' => 2,
			'FOR_RENT_ORDER_ENDED' => 2,
			'FOR_SALE' => 1,
			'FOR_SALE_ORDER_ENDED' => 1,
			'IN_MANAGEMENT' => 3,
			'OPTION_FOR_RENT' => 2,
			'OPTION_FOR_SALE' => 1,
			'PRICING' => 3,
			'PROSPECT_FOR_RENT' => 2,
			'PROSPECT_FOR_SALE' => 1,
			'RENTED' => 2,
			'SOLD' => 1
		);
		
		$purpose_statuses = array(
			'FOR_RENT' => 1,
			'FOR_RENT_ORDER_ENDED' => 1,
			'FOR_SALE' => 1,
			'FOR_SALE_ORDER_ENDED' => 1,
			'IN_MANAGEMENT' => 5,
			'OPTION_FOR_RENT' => 3,
			'OPTION_FOR_SALE' => 3,
			'PRICING' => 5,
			'PROSPECT_FOR_RENT' => 4,
			'PROSPECT_FOR_SALE' => 4,
			'RENTED' => 2,
			'SOLD' => 2
		);
		
		$rent_types = array(
			'CIVAL' => 1,
			'COMMERCIAL' => 2,
			'HABITATION' => 3,
			'MAINRESIDENCE' => 4,
			'PROFESSIONAL' => 5
		);
		
		$rent_times = array(
			'LIFETIME' => 6,
			'NINE_YEARS' => 5,
			'ONE_YEAR' => 1,
			'RENOVATION' => 7,
			'SIX_YEARS' => 4,
			'THREE_YEARS' => 3,
			'TWO_YEARS' => 2
		);
		
		$payment_periods = array(
			'MONTHLY' => 1,
			'TRIMESTRIAL' => 2
		);
		
		$payment_types = array(
			'AT_END_OF_PERIOD' => 1,
			'IN_ADVANCE' => 2
		);
		
		$availabilities = array(
			'BY_DATE' => 4,
			'BY_DEED' => 2,
			'IMMEDIATELY' => 1,
			'IN_CONSULTATION' => 3
		);
		
		$price_types = array(
			'M2_PER_MONTH' => 5,
			'M2_PER_YEAR' => 6,
			'PER_M2' => 4,
			'PRICE_PER_MONTH' => 2,
			'PRICE_PER_YEAR' => 3,
			'TOTAL' => 1
		);
		
		$price_indications = array(
			'ASKING_PRICE' => 1,
			'BUY_PRICE' => 2,
			'NEGOTIABLE' => 3,
			'ON_REQUEST' => 4
		);
		
		$shop_locations = array(
			'OTHER' => 4,
			'SHOPPING_CENTRE' => 1,
			'SHOPPING_GALLERY' => 2,
			'SHOPPING_STREET' => 3
		);
		
		$states = array(
			'LUXURY_FINISH' => 6,
			'NORMAL' => 1,
			'READY_FOR_ENTRY' => 5,
			'TO_BE_DEMOLISHED' => 2,
			'TO_REFURBISH' => 3,
			'TO_RENOVATE' => 4
		);
		
		$garden_qualities = array(
			'CONSTRUCTED' => 4,
			'MAINTAINED' => 2,
			'NEGLECTED' => 3,
			'NICELY_CONSTRUCTED' => 5,
			'NORMAL' => 1,
			'TO_CONSTRUCT' => 6
		);
		
		$roof_types = array(
			'COMPOSITE_ROOF' => 1,
			'CROSS_GABLE_ROOF' => 2,
			'DOME_ROOF' => 3,
			'FLAT_CONCRETE_ROOF' => 4,
			'FLAT_FELT_ROOF' => 5,
			'FLAT_ROOF' => 6,
			'FLAT_WOODEN_ROOF' => 7,
			'LEAN_TO_ROOF' => 8,
			'MANSARD_ROOF' => 9,
			'PITCHED_ROOF' => 10,
			'SHIELD_ROOF' => 11,
			'TENT_ROOF' => 12
		);
		
		$evaluations = array(
			'BAD' => 1,
			'GOOD' => 3,
			'MEDIOCRE' => 2,
			'VERY_GOOD' => 4
		);
		
		$roof_covers = array(
			'CONCRETE' => 1,
			'IN_TERRACE' => 2,
			'INDUSTRIAL_TILES' => 3,
			'OTHER' => 4,
			'SHEETING' => 5,
			'SLATE' => 6,
			'STUBBLE' => 7,
			'TILES' => 8,
			'TILES_TERRA_COTTA' => 9,
			'ZINC' => 10
		);
		
		$window_types = array(
			'ALUMINIUM' => 1,
			'PVC' => 2,
			'WOOD' => 3,
			'OTHER' => 4
		);
		
		$glazing_types = array(
			'DOUBLE' => 1,
			'PARTLY_DOUBLE' => 2,
			'SINGLE' => 3,
			'BAY' => 4,
			'OTHER' => 5
		);
		
		$heating_types = array(
			'COLLECTIVE_HEATING' => 1,
			'INDIVIDUAL' => 2,
			'COLLECTIVE' => 1
		);
		
		$heating_sources = array(
			'AIRCO' => 1,
			'BOILER_OWN' => 2,
			'BOILER_RENT' => 3,
			'CENTRAL_FACILITIES' => 4,
			'CENTRALLY_HEATED' => 4,
			'COALS' => 5,
			'COMBI_BOILER' => 6,
			'CONDENSING_BOILER' => 7,
			'DISTRICT_HEATING' => 8,
			'ELECTRICAL_BOILER_OWN' => 9,
			'ELECTRICAL_BOILER_RENT' => 10,
			'ELECTRICITY' => 11,
			'FIRE_PLACE' => 12,
			'FIRE_PLACE_POSSIBLE' => 13,
			'FLOOR_HEATING_FULLY' => 14,
			'FLOOR_HEATING_PARTIAL' => 15,
			'GAS_FIREPLACE' => 16,
			'GAS_STOVE' => 17,
			'GEOTHERMAL' => 18,
			'GEYSER_OWN' => 19,
			'GEYSER_RENT' => 20,
			'HEATING_OIL' => 21,
			'HEATING_PUMP' => 22,
			'HOT_AIR_HEATING' => 23,
			'HP_BOILER' => 24,
			'NATURAL_GAS' => 25,
			'NONE' => 29,
			'OTHER' => 28,
			'PARENT_HEARTH' => 12,
			'SOLAR_COLLECTORS' => 26,
			'WINDMILL' => 27
		);
		
		$water_heating_sources = array(
			'ELECTRICITY' => 1,
			'GAS' => 2,
			'GASOIL' => 3,
			'SOLAR_COLLECTORS' => 4
		);
		
		$plumbing_types = array(
			'BOILER_OWN' => 1,
			'BOILER_RENT' => 2,
			'CENTRAL_FACILITIES' => 3,
			'CENTRALLY_HEATED' => 3,
			'ELECTRICAL_BOILER_OWN' => 4,
			'ELECTRICAL_BOILER_RENT' => 5,
			'ELECTRICITY' => 6,
			'GEYSER_OWN' => 7,
			'GEYSER_RENT' => 8,
			'HEATING_OIL' => 9,
			'NATURAL_GAS' => 10,
			'NONE' => 14,
			'OTHER' => 13,
			'SOLAR_COLLECTORS' => 12,
			'SOLAR_BOILER' => 11
		);
		
		$common_walls = array(
			'ONE_WALL' => 1,
			'TWO_WALLS' => 2,
			'THREE_WALLS' => 3
		);
		
		$orientations = array(
			'EAST' => 1,
			'NORTH' => 7,
			'NORTH_EAST' => 8,
			'NORTH_WEST' => 6,
			'SOUTH' => 3,
			'SOUTH_EAST' => 2,
			'SOUTH_WEST' => 4,
			'WEST' => 5
		);
		
		$maintenance = array(
			'BAD' => 1,
			'EXCELLENT' => 4,
			'GOOD' => 3,
			'MEDIOCRE' => 2,
			'MODERATE' => 2
		);
		
		$construction_types = array(
			'BREEZE_BLOCK' => 1,
			'BRICK' => 2,
			'CONCRETE' => 3,
			'GRINDING' => 4,
			'HONE_OF_SIZE' => 5,
			'OTHER' => 10,
			'PREFAB_FRAME' => 6,
			'STONE' => 7,
			'TRADITIONAL_FRAME' => 8,
			'WOOD' => 9,
			'WOOD_FRAME' => 10
		);
		
		$frontage_types = array(
			'BRICK' => 1,
			'COATING' => 2,
			'COB' => 3,
			'GRINDING' => 4,
			'HALF_TIMBERING' => 5,
			'HONE_OF_SIZE' => 6,
			'WOOD' => 7
		);
		
		$living_types = array(
			'GARDEN_ROOM' => 5,
			'L_ROOM' => 1,
			'SERRE' => 6,
			'SUN_ROOM' => 7,
			'T_ROOM' => 2,
			'U_ROOM' => 3,
			'Z_ROOM' => 4
		);
		
		$garage_types = array(
			'BASEMENT' => 1,
			'CARPORT' => 2,
			'ELEVATOR_ACCESS' => 3,
			'GARAGE_POSSIBLE' => 4,
			'GARAGEBOX' => 5,
			'GROUND_FLOOR' => 6,
			'INBUILT' => 7,
			'INSIDE' => 8,
			'OUTSIDE' => 9,
			'PARKING_CELLAR' => 1,
			'PARKING_PLACE' => 10,
			'SOUTTERAIN' => 11,
			'STONE_DETACHED' => 12,
			'STONE_OUTBUILDED' => 15,
			'WOOD_DETACHED' => 13,
			'WOOD_OUTBUILDED' => 14
		);
		
		$profession_room_types = array(
			'ATTACHED' => 1,
			'DETACHED' => 2,
			'INBUILT' => 3,
			'POSSIBLE' => 4
		);
		
		$office_types = array(
			'ATTACHED' => 1,
			'DETACHED' => 2,
			'INBUILT' => 3,
			'POSSIBLE' => 4
		);
		
		$storage_types = array(
			'BOX' => 1,
			'DETACHED_STONE' => 2,
			'DETACHED_WOOD' => 3,
			'INSIDE' => 4,
			'MOUNTED_STONE' => 5,
			'MOUNTED_WOOD' => 6,
			'PATIO_BUNGALOW' => 7,
			'SEMI_BUNGALOW' => 8
		);
		
		$kitchen_types = array(
			'AMERICAN_KITCHEN' => 1,
			'BUILD_WITH_APPLIANCES' => 2,
			'CLOSED_KITCHEN' => 3,
			'KITCHENETTE' => 4,
			'LARGE_KITCHEN' => 5,
			'OPEN_KITCHEN' => 6,
			'SEMI_OPEN_KITCHEN' => 7
		);
		
		$cellar_types = array(
			'BASEMENT' => 1,
			'CRAWL_SPACE' => 2,
			'FULL_GROUND' => 3
		);
		
		$isolation_types = array(
			'FLOOR_ISOLATION' => 1,
			'FULLY_ISOLATED' => 2,
			'GREEN_CONSTRUCTION' => 3,
			'NO_CAVITY' => 4,
			'NO_ISOLATION' => 5,
			'ROOF_ISOLATION' => 6,
			'WALL_ISOLATION' => 7
		);
		
		$easements = array(
			'PASSAGE' => 1,
			'PRESENT' => 6,
			'RIGHT_OF_EXIT' => 2,
			'RIGHT_OF_WAY' => 3,
			'ROAD_PLANNING' => 5,
			'SIGHT' => 4
		);
		
		$environmental_planning_types = array(
			'APPLIED_FOR' => 2,
			'BEING_PROCESSED' => 3,
			'OK' => 1
		);
		
		$land_use_designations = array(
			'Ag' => 1,
			'Bg' => 2,
			'Gdr' => 3,
			'Gvr' => 4,
			'Iab' => 5,
			'Igb' => 6,
			'Lwag' => 7,
			'Ng' => 8,
			'Nr' => 9,
			'OTHER' => 10,
			'Pg' => 11,
			'Wche' => 12,
			'Wg' => 13,
			'Wglk' => 14,
			'Wp' => 15,
			'Wug' => 16
		);
		
		$picture_types = array(
			'EXTERIEUR' => 1,
			'GARDEN' => 2,
			'INTERIEUR' => 3,
			'MAP_DOWNSTAIRS' => 4,
			'MAP_OVERALL' => 5,
			'MAP_UPSTAIRS' => 6
		);
		
		$floor_types = array(
			'LEVEL_ATTIC' => 1,
			'LEVEL_BASEMENT' => 2,
			'LEVEL_FLOOR' => 3,
			'LEVEL_GROUNDFLOOR_FLAT' => 4,
			'LEVEL_VLIERING' => 5
		);
		
		$certificate_fields = array(
			'AS_BUILT' => 'as_built_certificate_date',
			'DIAGNOSTICS_ELECTRICITY' => 'diagnostics_certificate_date',
			'ELECTRICITY_CERTIFICATE' => 'electricity_certificate_date',
			'ENERGY_CONSUMPTION' => 'energy_consumption_certificate_date',
			'ENERGY_PERFORMANCE' => 'energy_performance_certificate_date',
			'GAS' => 'gas_certificate_date',
			'GAZ' => 'gas_certificate_date',
			'GROUNDPOLUTION' => 'polution_certificate_date',
			'HOUSE_ADAPTED_TO_DISABLED_HALLMARK' => 'accessibility_certificate_date',
			'LEAD' => 'lead_certificate_date',
			'NATURAL_RISK' => 'nature_risk_certificate_date',
			'NATURE_RISK' => 'nature_risk_certificate_date',
			'OIL_TANK' => 'oil_tank_certificate_date',
			'PLANNING_CERT' => 'planning_certificate_date',
			'PRIVATE_AREA' => 'private_area_certificate_date',
			'SMOKE_DETECTION' => 'smoke_detection_certificate_date',
			'SOIL_CERT' => 'soil_certificate_date'
		);
		
	    $publication_summaries = $client->GetPublicationSummaries();
	    
	    if (isset($publication_summaries->GetPublicationSummariesResult->PublicationSummaries->PublicationSummary)) {
		    
		    $present_properties = array();
		    $feedbacks = array();
		    
		    foreach ($publication_summaries->GetPublicationSummariesResult->PublicationSummaries->PublicationSummary as $summary) {
			    
			    $publication = $client->GetPublication(array( 'PublicationId' => $summary->ID ));
			    
			    if (isset($publication->GetPublicationResult->Publication)) {
				    
				    $publication = $publication->GetPublicationResult->Publication;
				    
				    $flashes = array();
				    if (isset($publication->Property->AllFlashes->Flash)) {
					    if (!is_array($publication->Property->AllFlashes->Flash)) $publication->Property->AllFlashes->Flash = array($publication->Property->AllFlashes->Flash);
					    foreach ($publication->Property->AllFlashes->Flash as $flash) {
						    if (!isset($flashes[substr($flash->ID,-1)])) $flashes[substr($flash->ID,-1)] = array();
						    $flashes[substr($flash->ID,-1)][strtolower(substr($flash->LanguageID,-2))] = $flash;
					    }
					}
				    
				    // Process photos
				    $photos = array();
				    if (isset($publication->Pictures->Picture)) {
					    if (!is_array($publication->Pictures->Picture)) $publication->Pictures->Picture = array($publication->Pictures->Picture);
					    foreach ($publication->Pictures->Picture as $picture) {
						    $photos[$picture->Index] = array(
							    'orig_filename' => $picture->Name,
							    'description' => $picture->Description,
								'url' => $picture->URL,
								'type' => isset($picture_types[$picture->PictureType]) ? $picture_types[$picture->PictureType] : null
						    );
					    }
					}
				    
				    // Process documents
				    $documents = array();
				    if (isset($publication->Documents->Document)) {
					    if (!is_array($publication->Documents->Document)) $publication->Documents->Document = array($publication->Documents->Document);
					    foreach ($publication->Documents->Document as $document) {
						    $documents[] = array(
							    'filename' => $document->OriginalName,
							    'name' => $document->Description,
								'url' => $document->URL,
								'filetype' => $document->FileType == 'UNDEFINED' ? null : strtolower($document->FileType)
						    );
					    }
					}
					
					// Process open house
					$openhouse = array();
					if (isset($publication->Property->OpenHouse->FromToDates->FromToDate)) {
					    if (!is_array($publication->Property->OpenHouse->FromToDates->FromToDate)) $publication->Property->OpenHouse->FromToDates->FromToDate = array($publication->Property->OpenHouse->FromToDates->FromToDate);
					    foreach ($publication->Property->OpenHouse->FromToDates->FromToDate as $fromtodate) {
							$from = date_parse_from_format('Y-m-d H:i:s', $fromtodate->From);
							$to = date_parse_from_format('Y-m-d H:i:s', $fromtodate->To);
						    $openhouse[] = array(
							    'comment' => $publication->Property->OpenHouse->Comment,
							    'from' => mktime($from['hour'],$from['minute'],$from['second'],$from['month'],$from['day'],$from['year']),
								'to' => mktime($to['hour'],$to['minute'],$to['second'],$to['month'],$to['day'],$to['year'])
						    );
					    }
					}
					
					// Process floors
					$floors = array();
					if (isset($publication->Property->Floors->Floor)) {
					    if (!is_array($publication->Property->Floors->Floor)) $publication->Property->Floors->Floor = array($publication->Property->Floors->Floor);
					    foreach ($publication->Property->Floors->Floor as $floor) {
						    $newfloor = array(
							    'level' => $floor->Level,
							    'description' => trim($floor->Description),
							    'rooms' => $floor->NumOfRooms < 0 ? null : $floor->NumOfRooms,
							    'bedrooms' => $floor->NumOfBedRooms < 0 ? null : $floor->NumOfBedRooms,
							    'type' => isset($floor_types[$floor->Type]) ? $floor_types[$floor->Type] : null,
							    'alleyway' => $floor->Alleyway == 'UNDEFINED' ? null : ($floor->Alleyway == 'TRUE') ,
							    'attic_stair' => $floor->AtticFixStair == 'UNDEFINED' ? null : ($floor->AtticFixStair == 'TRUE') ,
							    'attic_room_possible' => $floor->AtticRoomPossible == 'UNDEFINED' ? null : ($floor->AtticRoomPossible == 'TRUE') ,
							    'balcony' => $floor->Balcony == 'UNDEFINED' ? null : ($floor->Balcony == 'TRUE') ,
							    'dormer' => $floor->Dormer == 'UNDEFINED' ? null : ($floor->Dormer == 'TRUE') ,
							    'hall' => $floor->Hall == 'UNDEFINED' ? null : ($floor->Hall == 'TRUE') ,
							    'machine_room' => $floor->MachineRoom == 'UNDEFINED' ? null : ($floor->MachineRoom == 'TRUE') ,
							    'provision_room' => $floor->ProvisionRoom == 'UNDEFINED' ? null : ($floor->ProvisionRoom == 'TRUE') ,
							    'roof_terrace' => $floor->RoofTerrace == 'UNDEFINED' ? null : ($floor->RoofTerrace == 'TRUE') ,
							    'shower' => $floor->Shower == 'UNDEFINED' ? null : ($floor->Shower == 'TRUE') ,
							    'stair_in_living' => $floor->StairInLivingRoom == 'UNDEFINED' ? null : ($floor->StairInLivingRoom == 'TRUE') ,
							    'storage' => $floor->Storage == 'UNDEFINED' ? null : ($floor->Storage == 'TRUE') ,
							    'toilet' => $floor->Toilet == 'UNDEFINED' ? null : ($floor->Toilet == 'TRUE') ,
							    'utility_room' => $floor->UtilityRoom == 'UNDEFINED' ? null : ($floor->UtilityRoom == 'TRUE') ,
							    'vestibule' => $floor->Vestibule == 'UNDEFINED' ? null : ($floor->Vestibule == 'TRUE') ,
							    'kitchen_renewed_year' => $floor->KitchenRenewed <= 0 ? null : $floor->KitchenRenewed ,
							    'kitchen_surface' => $floor->KitchenSurface <= 0 ? null : $floor->KitchenSurface ,
							    'kitchen_equipped' => null,
							    'living_surface' => $floor->LivingRoomSurface <= 0 ? null : $floor->LivingRoomSurface ,
							    'living_type' => null,
							    
							    'bathroom1_length' => $floor->Bathroom1Length <= 0 ? null : $floor->Bathroom1Length ,
							    'bathroom1_width' => $floor->Bathroom1Width <= 0 ? null : $floor->Bathroom1Width ,
							    'bathroom1_bath' => false,
							    'bathroom1_short_bath' => false,
							    'bathroom1_shower' => false,
							    'bathroom1_toilet' => false,
							    
							    'bathroom2_length' => $floor->Bathroom2Length <= 0 ? null : $floor->Bathroom2Length ,
							    'bathroom2_width' => $floor->Bathroom2Width <= 0 ? null : $floor->Bathroom2Width ,
							    'bathroom2_bath' => false,
							    'bathroom2_short_bath' => false,
							    'bathroom2_shower' => false,
							    'bathroom2_toilet' => false,
						    );
						    
						    if (isset($floor->KitchenTypes->KitchenType)) {
							    if (!is_array($floor->KitchenTypes->KitchenType)) $floor->KitchenTypes->KitchenType = array($floor->KitchenTypes->KitchenType);
							    foreach ($floor->KitchenTypes->KitchenType as $type) {
								    if ($type != 'UNDEFINED') {
									    $newfloor['kitchen_equipped'] = ($type == 'BUILD_WITH_APPLIANCES');
										break;
									}
							    }
						    }
						    
						    if (isset($floor->LivingRoomTypes->LivingRoomType)) {
							    if (!is_array($floor->LivingRoomTypes->LivingRoomType)) $floor->LivingRoomTypes->LivingRoomType = array($floor->LivingRoomTypes->LivingRoomType);
							    foreach ($floor->LivingRoomTypes->LivingRoomType as $type) {
								    if (isset($living_types[$type])) {
									    $newfloor['living_type'] = $living_types[$type];
									    break;
								    }
							    }
						    }
						    
						    for ($i=1;$i<=2;$i++) {
							    $types_field = 'Bathroom'.$i.'Types';
							    if (isset($floor->$types_field->BathRoomType)) {
								    if (!is_array($floor->$types_field->BathRoomType)) $floor->$types_field->BathRoomType = array($floor->$types_field->BathRoomType);
								    foreach ($floor->$types_field->BathRoomType as $type) {
									    if ($type == 'BATHROOM_BATH') $newfloor['bathroom'.$i.'_bath'] = true;
									    else if ($type == 'BATHROOM_HIPBATH') $newfloor['bathroom'.$i.'_short_bath'] = true;
									    else if ($type == 'BATHROOM_SHOWER') $newfloor['bathroom'.$i.'_shower'] = true;
									    else if ($type == 'BATHROOM_TOILET') $newfloor['bathroom'.$i.'_toilet'] = true;
								    }
							    }
						    }
						    
						    $floors[$floor->Level] = $newfloor;
					    }
					}
					
				    // Process features
				    $features = array();
				    if (isset($publication->Property->MarketingTypes->MarketingType)) {
					    if (!is_array($publication->Property->MarketingTypes->MarketingType)) $publication->Property->MarketingTypes->MarketingType = array($publication->Property->MarketingTypes->MarketingType);
					    foreach ($publication->Property->MarketingTypes->MarketingType as $marketing_type) {
						    if (!is_array($marketing_type->Descriptions->Description)) $marketing_type->Descriptions->Description = array($marketing_type->Descriptions->Description);
						    foreach ($marketing_type->Descriptions->Description as $marketing_type_description) {
							    if ($marketing_type_description->_ == $marketing_type->Code) continue;
								$language = strtolower(substr($marketing_type_description->LanguageId,-2));
								if (!isset($features[$language])) $features[$language] = array();
								$features[$language][] = capitalize($marketing_type_description->_,false);
							}
					    }
					}
					
					if (isset($publication->Property->Dimensions->RentableSurface->_)) $publication->Property->Dimensions->RentableSurface = $publication->Property->Dimensions->RentableSurface->_;
					if (isset($publication->Property->Dimensions->CantineSurface->_)) $publication->Property->Dimensions->CantineSurface = $publication->Property->Dimensions->CantineSurface->_;
					if (isset($publication->Property->Dimensions->CateringSurface->_)) $publication->Property->Dimensions->CateringSurface = $publication->Property->Dimensions->CateringSurface->_;
					if (isset($publication->Property->Dimensions->IndustrySurface->_)) $publication->Property->Dimensions->IndustrySurface = $publication->Property->Dimensions->IndustrySurface->_;
					if (isset($publication->Property->Dimensions->IndustryOfficeSurface->_)) $publication->Property->Dimensions->IndustryOfficeSurface = $publication->Property->Dimensions->IndustryOfficeSurface->_;
					if (isset($publication->Property->Dimensions->IndustryLandSurface->_)) $publication->Property->Dimensions->IndustryLandSurface = $publication->Property->Dimensions->IndustryLandSurface->_;
					if (isset($publication->Property->Dimensions->IndustryHallSurface->_)) $publication->Property->Dimensions->IndustryHallSurface = $publication->Property->Dimensions->IndustryHallSurface->_;
					if (isset($publication->Property->Dimensions->OfficeSurface->_)) $publication->Property->Dimensions->OfficeSurface = $publication->Property->Dimensions->OfficeSurface->_;
					if (isset($publication->Property->Dimensions->ProductionHallSurface->_)) $publication->Property->Dimensions->ProductionHallSurface = $publication->Property->Dimensions->ProductionHallSurface->_;
					if (isset($publication->Property->Dimensions->SalesRoomSurface->_)) $publication->Property->Dimensions->SalesRoomSurface = $publication->Property->Dimensions->SalesRoomSurface->_;
					if (isset($publication->Property->Dimensions->ShopSurface->_)) $publication->Property->Dimensions->ShopSurface = $publication->Property->Dimensions->ShopSurface->_;
					
				    $data = array(
						'software_id' => $publication->Property->ID,
						'publication_id' => $summary->ID,
						'create_date' => strtotime($publication->Property->Created),
						'update_date' => strtotime($publication->Property->Modified),
						
						'type' => isset($types[$publication->Property->PropertyType]) ? $types[$publication->Property->PropertyType] : 1,
						'category' => isset($categories[$publication->Property->Typo->Sort]) ? $categories[$publication->Property->Typo->Sort] : 1,
						'purpose' => isset($purposes[$publication->Property->Status]) ? $purposes[$publication->Property->Status] : 3,
						'purpose_status' => isset($purpose_statuses[$publication->Property->Status]) ? $purpose_statuses[$publication->Property->Status] : 1,
						'rent_type' => isset($rent_types[$publication->Property->RentType]) ? $rent_types[$publication->Property->RentType] : null,
						'rent_time' => isset($rent_times[$publication->Property->RentTime]) ? $rent_times[$publication->Property->RentTime] : null,
						'rent_temporary' => intval($publication->Property->TemporaryRent) < 0 ? null : ($publication->Property->TemporaryRent == '1') ,
						'payment_period' => isset($payment_periods[$publication->Property->PaymentPeriod]) ? $payment_periods[$publication->Property->PaymentPeriod] : null,
						'payment_type' => isset($payment_types[$publication->Property->Payment]) ? $payment_types[$publication->Property->Payment] : null,
						'to_take_over' => intval($publication->Property->TakeOver) < 0 ? null : ($publication->Property->TakeOver == '1') ,
						'investment' => intval($publication->Property->IsYieldProperty) < 0 ? null : ($publication->Property->IsYieldProperty == '1') ,
						'exclusive' => ($publication->Property->IsExclusive == '1'),
						'availability' => isset($availabilities[$publication->Property->Acception]) ? $availabilities[$publication->Property->Acception] : null,
						'availability_date' => null,
						'service_charges_included' => $publication->Property->ServiceCharges == 'UNDEFINED' ? null : ($publication->Property->ServiceCharges == 'COSTS_INCLUSIVE') ,
						
						'selected_flash' => intval(preg_replace('/\D/','',$publication->Info->FlashID)),
						
						'show_price' => !$publication->Info->HidePrice,
						'price' => floatval($publication->Property->Price),
						'price_type' => isset($price_types[$publication->Property->PriceType]) ? $price_types[$publication->Property->PriceType] : 1,
						'price_indication' => isset($price_types[$publication->Property->PriceIndication]) ? $price_types[$publication->Property->PriceIndication] : null,
						'vat_included' => !($publication->Property->IsVATInclusive == 'FALSE' || $publication->Property->IsVATExclusive == 'TRUE'),
						'charges' => floatval($publication->Property->ChargesAndProvisions) >= 0 ? floatval($publication->Property->ChargesAndProvisions) : null,
						'communal_expenses' => floatval($publication->Property->CommunalExpenses) >= 0 ? floatval($publication->Property->CommunalExpenses) : null,
						'heating_water_costs' => floatval($publication->Property->HeatingWaterCosts) >= 0 ? floatval($publication->Property->HeatingWaterCosts) : null,
						'heating_costs' => floatval($publication->Property->HeatingCosts) >= 0 ? floatval($publication->Property->HeatingCosts) : null,
						'garage_costs' => floatval($publication->Property->CostsGarage) >= 0 ? floatval($publication->Property->CostsGarage) : null,
						
						'show_address' => !$publication->Info->HideAddress,
						'show_number' => !$publication->Info->HideHouseNumber,
						'reference' => $publication->Property->Reference,
						'address_name' => $publication->Property->Address->Residence,
						'address' => trim($publication->Property->Address->Street.' '.$publication->Property->Address->HouseNumber.$publication->Property->Address->HouseNumberExtension.' '.$publication->Property->Address->MailBox),
						'postal' => $publication->Property->Address->City->ZipCode,
						'city' => ucfirst($publication->Property->Address->City->_),
						'country' => strtolower($publication->Property->Address->CountryID),
						'nearby_public_transport' => ($publication->Property->NearbyPublicTransport == 'UNDEFINED' ? null : ($publication->Property->NearbyPublicTransport == 'TRUE')),
						'nearby_shops' => ($publication->Property->NearbyShops == 'UNDEFINED' ? null : ($publication->Property->NearbyShops == 'TRUE')),
						'nearby_school' => ($publication->Property->NearbySchool == 'UNDEFINED' ? null : ($publication->Property->NearbySchool == 'TRUE')),
						'nearby_highway' => ($publication->Property->NearbyHighway == 'UNDEFINED' ? null : ($publication->Property->NearbyHighway == 'TRUE')),
						'shop_location' => isset($shop_locations[$publication->Property->ShopLocation]) ? $shop_locations[$publication->Property->ShopLocation] : null,
						
						'surface' => $publication->Property->Area < 0 ? null : $publication->Property->Area,
						'surface_terrain' => $publication->Property->LandArea < 0 ? null : $publication->Property->LandArea,
						'surface_livable' => floatval($publication->Property->SurfaceLivable) < 0 ? null : floatval($publication->Property->SurfaceLivable),
						'surface_buildable' => isset($publication->Property->Dimensions->ConstructionSurface->_) && floatval($publication->Property->Dimensions->ConstructionSurface->_) >= 0 ? floatval($publication->Property->Dimensions->ConstructionSurface->_) : null,
						'surface_terrace' => isset($publication->Property->SurfaceTerrace) && floatval($publication->Property->SurfaceTerrace) >= 0 ? floatval($publication->Property->SurfaceTerrace) : null,
						'surface_garden' => isset($publication->Property->SurfaceGarden) && floatval($publication->Property->SurfaceGarden) >= 0 ? floatval($publication->Property->SurfaceGarden) : null,
						'surface_balcony' => isset($publication->Property->Dimensions->BalconySurface->_) && floatval($publication->Property->Dimensions->BalconySurface->_) >= 0 ? floatval($publication->Property->Dimensions->BalconySurface->_) : null,
						'construction_year' => $publication->Property->ConstructionYear < 0 ? null : $publication->Property->ConstructionYear,
						'renovation_year' => $publication->Property->RenovationYear < 0 ? null : $publication->Property->RenovationYear,
						'newly_constructed' => ($publication->Property->NewEstate == 'UNDEFINED' ? null : ($publication->Property->NewEstate == 'TRUE')),
						'under_construction' => ($publication->Property->UnderConstruction == 'UNDEFINED' ? null : ($publication->Property->UnderConstruction == 'TRUE')),
						'in_production' => ($publication->Property->IsInProduction == 'UNDEFINED' ? null : ($publication->Property->IsInProduction == 'TRUE')),
						'state' => isset($states[$publication->Property->GeneralState]) ? $states[$publication->Property->GeneralState] : null,
						'terrain_width' => floatval($publication->Property->Width) < 0 ? null : floatval($publication->Property->Width),
						'terrain_depth' => floatval($publication->Property->Depth) < 0 ? null : floatval($publication->Property->Depth),
						'terrain_width_front' => floatval($publication->Property->FrontWidth) < 0 ? null : floatval($publication->Property->FrontWidth),
						'floor_number' => is_numeric($publication->Property->FloorLevelNL) ? intval($publication->Property->FloorLevelNL) : null,
						'floors' => $publication->Property->NumberOfFloors < 0 ? null : $publication->Property->NumberOfFloors,
						'parkings' => $publication->Property->NumberOfParkingPlaces < 0 ? null : $publication->Property->NumberOfParkingPlaces,
						'has_parking' => ($publication->Property->NumberOfParkingPlaces < 0 && $publication->Property->HasParkingPlace == 'UNDEFINED') ? null : ($publication->Property->NumberOfParkingPlaces > 0 || $publication->Property->HasParkingPlace == 'TRUE'),
						'has_garden' => ($publication->Property->SurfaceGarden < 1 && $publication->Property->HasBackYard == 'UNDEFINED') ? null : ($publication->Property->SurfaceGarden > 0 || $publication->Property->HasBackYard == 'TRUE'),
						'has_balcony' => ($publication->Property->HasBalcony == 'UNDEFINED' ? null : ($publication->Property->HasBalcony == 'TRUE')),
						'garden_width' => floatval($publication->Property->BackYardWidth) < 0 ? null : floatval($publication->Property->BackYardWidth),
						'garden_depth' => floatval($publication->Property->BackYardDepth) < 0 ? null : floatval($publication->Property->BackYardDepth),
						'garden_quality' => isset($garden_qualities[$publication->Property->GardenQuality]) ? $garden_qualities[$publication->Property->GardenQuality] : null,
						'roof_type' => isset($roof_types[$publication->Property->RoofType]) ? $roof_types[$publication->Property->RoofType] : null,
						'roof_evaluation' => isset($evaluations[$publication->Property->RoofEvaluation]) ? $evaluations[$publication->Property->RoofEvaluation] : null,
						'roof_comment' => $publication->Property->RoofComment,
						'roof_cover' => isset($roof_covers[$publication->Property->RoofCoverType]) ? $roof_covers[$publication->Property->RoofCoverType] : null,
						'roof_cover_evaluation' => isset($evaluations[$publication->Property->RoofCoverEvaluation]) ? $evaluations[$publication->Property->RoofCoverEvaluation] : null,
						'roof_cover_comment' => isset($publication->Property->RoofCoverComment) ? $publication->Property->RoofCoverComment : '',
						'window_type' => isset($window_types[$publication->Property->WindowType]) ? $window_types[$publication->Property->WindowType] : null,
						'window_evaluation' => isset($evaluations[$publication->Property->WindowEvaluation]) ? $evaluations[$publication->Property->WindowEvaluation] : null,
						'window_comment' => $publication->Property->WindowComment,
						'glazing_evaluation' => isset($evaluations[$publication->Property->GlazingEvaluation]) ? $evaluations[$publication->Property->GlazingEvaluation] : null,
						'glazing_comment' => $publication->Property->GlazingComment,
						'electricity_evaluation' => isset($evaluations[$publication->Property->ElectricityEvaluation]) ? $evaluations[$publication->Property->ElectricityEvaluation] : null,
						'electricity_comment' => $publication->Property->ElectricityComment,
						'plumbing_evaluation' => isset($evaluations[$publication->Property->PlumbingEvaluation]) ? $evaluations[$publication->Property->PlumbingEvaluation] : null,
						'plumbing_comment' => $publication->Property->PlumbingComment,
						'sanitary_evaluation' => isset($evaluations[$publication->Property->SanitaryEvaluation]) ? $evaluations[$publication->Property->SanitaryEvaluation] : null,
						'sanitary_comment' => $publication->Property->SanitaryComment,
						'modification_allowed' => ($publication->Property->Modification == 'UNDEFINED' ? null : ($publication->Property->Modification == 'ALLOWED')),
						'common_walls' => isset($common_walls[$publication->Property->CommonWalls]) ? $common_walls[$publication->Property->CommonWalls] : null,
						'orientation' => isset($orientations[$publication->Property->Orientation]) ? $orientations[$publication->Property->Orientation] : null,
						'maintenance_inside' => isset($maintenance[$publication->Property->MaintenanceInside]) ? $maintenance[$publication->Property->MaintenanceInside] : null,
						'maintenance_outside' => isset($maintenance[$publication->Property->MaintenanceOutside]) ? $maintenance[$publication->Property->MaintenanceOutside] : null,
						'construction_type' => isset($construction_types[$publication->Property->ConstructionType]) ? $construction_types[$publication->Property->ConstructionType] : null,
						'construction_evaluation' => isset($evaluations[$publication->Property->ConstructionEvaluation]) ? $evaluations[$publication->Property->ConstructionEvaluation] : null,
						'frontage_type' => isset($frontage_types[$publication->Property->FrontageType]) ? $frontage_types[$publication->Property->FrontageType] : null,
						'frontage_evaluation' => isset($evaluations[$publication->Property->FrontageEvaluation]) ? $evaluations[$publication->Property->FrontageEvaluation] : null,
						
						'rooms' => $publication->Property->NumberOfRooms < 0 ? null : $publication->Property->NumberOfRooms,
						'bedrooms' => $publication->Property->NumberOfBedrooms < 0 ? null : $publication->Property->NumberOfBedrooms,
						'bathrooms' => $publication->Property->NumberOfBathrooms < 0 ? null : $publication->Property->NumberOfBathrooms,
						'offices' => $publication->Property->NumberOfOffices < 0 ? null : $publication->Property->NumberOfOffices,
						'garages' => $publication->Property->NumberOfGarages < 0 ? null : $publication->Property->NumberOfGarages,
						'garage_size' => $publication->Property->NumberOfPlacesInGarage < 0 ? null : $publication->Property->NumberOfPlacesInGarage,
						'toilets' => $publication->Property->NumberOfToilets < 0 ? null : $publication->Property->NumberOfToilets,
						'has_garage' => ($publication->Property->NumberOfGarages < 0 && $publication->Property->HasGarage == 'UNDEFINED') ? null : ($publication->Property->NumberOfGarages > 0 || $publication->Property->HasGarage == 'TRUE'),
						'has_terrace' => ($publication->Property->SurfaceTerrace < 1 && $publication->Property->HasTerrace == 'UNDEFINED') ? null : ($publication->Property->SurfaceTerrace > 0 || $publication->Property->HasTerrace == 'TRUE'),
						'has_cellar' => ($publication->Property->SurfaceBasement < 1 && $publication->Property->HasCellar == 'UNDEFINED') ? null : ($publication->Property->SurfaceBasement > 0 || $publication->Property->HasCellar == 'TRUE'),
						'has_attic' => ($publication->Property->SurfaceAttick < 1 && $publication->Property->HasAttick == 'UNDEFINED') ? null : ($publication->Property->SurfaceAttick > 0 || $publication->Property->HasAttick == 'TRUE'),
						'has_showroom' => ($publication->Property->SurfaceShowroom < 1 && $publication->Property->HasShowroom == 'UNDEFINED') ? null : ($publication->Property->SurfaceShowroom > 0 || $publication->Property->HasShowroom == 'TRUE'),
						'has_office' => $publication->Property->HasOffice == 'UNDEFINED' ? null : $publication->Property->HasOffice == 'TRUE',
						'has_greenhouse' => $publication->Property->HasGreenHouse == 'UNDEFINED' ? null : $publication->Property->HasGreenHouse == 'TRUE',
						'has_profession_room' => ($publication->Property->SurfaceProfessionRoom < 1 && $publication->Property->HasProfessionRoom == 'UNDEFINED') ? null : ($publication->Property->SurfaceProfessionRoom > 0 || $publication->Property->HasProfessionRoom == 'TRUE'),
						'has_living' => ($publication->Property->SurfaceLiving < 1 && $publication->Property->HasLivingRoom == 'UNDEFINED') ? null : ($publication->Property->SurfaceLiving > 0 || $publication->Property->HasLivingRoom == 'TRUE'),
						'has_kitchen' => ($publication->Property->SurfaceKitchen < 1 && $publication->Property->HasKitchen == 'UNDEFINED') ? null : ($publication->Property->SurfaceKitchen > 0 || $publication->Property->HasKitchen == 'TRUE'),
						'has_utility_room' => ($publication->Property->SurfaceUtilityRoom < 1 && $publication->Property->HasUtilityRoom == 'UNDEFINED') ? null : ($publication->Property->SurfaceUtilityRoom > 0 || $publication->Property->HasUtilityRoom == 'TRUE'),
						'has_bedrooms' => ($publication->Property->NumberOfBedrooms < 1 && $publication->Property->HasBedrooms == 'UNDEFINED') ? null : ($publication->Property->NumberOfBedrooms > 0 || $publication->Property->HasBedrooms == 'TRUE'),
						'has_bathroom' => ($publication->Property->NumberOfBathrooms < 1 && $publication->Property->HasBathroom == 'UNDEFINED') ? null : ($publication->Property->NumberOfBathrooms > 0 || $publication->Property->HasBathroom == 'TRUE'),
						'has_toilet' => ($publication->Property->NumberOfToilets < 1 && $publication->Property->HasToilet == 'UNDEFINED') ? null : ($publication->Property->NumberOfToilets > 0 || $publication->Property->HasToilet == 'TRUE'),
						'has_storage' => ((!isset($publication->Property->SurfaceStock) || $publication->Property->SurfaceStock < 1) && $publication->Property->HasStock == 'UNDEFINED') ? null : ((isset($publication->Property->SurfaceStock) && $publication->Property->SurfaceStock > 0) || $publication->Property->HasStock == 'TRUE'),
						'has_wash_place' => $publication->Property->HasWashPlace == 'UNDEFINED' ? null : $publication->Property->HasWashPlace == 'TRUE',
						'has_dinging_room' => ($publication->Property->SurfaceDiningRoom < 1 && $publication->Property->HasDiningRoom == 'UNDEFINED') ? null : ($publication->Property->SurfaceDiningRoom > 0 || $publication->Property->HasDiningRoom == 'TRUE'),
						'surface_kitchen' => isset($publication->Property->SurfaceKitchen) && floatval($publication->Property->SurfaceKitchen) >= 0 ? floatval($publication->Property->SurfaceKitchen) : null,
						'surface_living' => isset($publication->Property->SurfaceLiving) && floatval($publication->Property->SurfaceLiving) >= 0 ? floatval($publication->Property->SurfaceLiving) : null,
						'surface_storage' => isset($publication->Property->SurfaceStock) && floatval($publication->Property->SurfaceStock) >= 0 ? floatval($publication->Property->SurfaceStock) : null,
						'surface_utility_room' => isset($publication->Property->SurfaceUtilityRoom) && floatval($publication->Property->SurfaceUtilityRoom) >= 0 ? floatval($publication->Property->SurfaceUtilityRoom) : null,
						'surface_showroom' => isset($publication->Property->SurfaceShowroom) && floatval($publication->Property->SurfaceShowroom) >= 0 ? floatval($publication->Property->SurfaceShowroom) : null,
						'surface_profession_room' => isset($publication->Property->SurfaceProfessionRoom) && floatval($publication->Property->SurfaceProfessionRoom) >= 0 ? floatval($publication->Property->SurfaceProfessionRoom) : null,
						'surface_attic' => isset($publication->Property->SurfaceAttick) && floatval($publication->Property->SurfaceAttick) >= 0 ? floatval($publication->Property->SurfaceAttick) : null,
						'surface_cellar' => isset($publication->Property->SurfaceBasement) && floatval($publication->Property->SurfaceBasement) >= 0 ? floatval($publication->Property->SurfaceBasement) : null,
						'surface_dining_room' => isset($publication->Property->SurfaceDiningRoom) && floatval($publication->Property->SurfaceDiningRoom) >= 0 ? floatval($publication->Property->SurfaceDiningRoom) : null,
						'surface_cantine' => isset($publication->Property->Dimensions->CantineSurface) && floatval($publication->Property->Dimensions->CantineSurface) >= 0 ? floatval($publication->Property->Dimensions->CantineSurface) : null,
						'surface_horeca' => isset($publication->Property->Dimensions->CateringSurface) && floatval($publication->Property->Dimensions->CateringSurface) >= 0 ? floatval($publication->Property->Dimensions->CateringSurface) : null,
						'surface_industry' => isset($publication->Property->Dimensions->IndustrySurface) && floatval($publication->Property->Dimensions->IndustrySurface) >= 0 ? floatval($publication->Property->Dimensions->IndustrySurface) : null,
						'surface_industry_land' => isset($publication->Property->Dimensions->IndustryLandSurface) && floatval($publication->Property->Dimensions->IndustryLandSurface) >= 0 ? floatval($publication->Property->Dimensions->IndustryLandSurface) : null,
						'surface_industry_office' => isset($publication->Property->Dimensions->IndustryOfficeSurface) && floatval($publication->Property->Dimensions->IndustryOfficeSurface) >= 0 ? floatval($publication->Property->Dimensions->IndustryOfficeSurface) : null,
						'surface_industry_hall' => isset($publication->Property->Dimensions->IndustryHallSurface) && floatval($publication->Property->Dimensions->IndustryHallSurface) >= 0 ? floatval($publication->Property->Dimensions->IndustryHallSurface) : null,
						'surface_office' => isset($publication->Property->Dimensions->OfficeSurface) && floatval($publication->Property->Dimensions->OfficeSurface) >= 0 ? floatval($publication->Property->Dimensions->OfficeSurface) : null,
						'surface_production' => isset($publication->Property->Dimensions->ProductionHallSurface) && floatval($publication->Property->Dimensions->ProductionHallSurface) >= 0 ? floatval($publication->Property->Dimensions->ProductionHallSurface) : null,
						'surface_rentable' => isset($publication->Property->Dimensions->RentableSurface) && floatval($publication->Property->Dimensions->RentableSurface) >= 0 ? floatval($publication->Property->Dimensions->RentableSurface) : null,
						'surface_sales_room' => isset($publication->Property->Dimensions->SalesRoomSurface) && floatval($publication->Property->Dimensions->SalesRoomSurface) >= 0 ? floatval($publication->Property->Dimensions->SalesRoomSurface) : null,
						'surface_shop' => isset($publication->Property->Dimensions->ShopSurface) && floatval($publication->Property->Dimensions->ShopSurface) >= 0 ? floatval($publication->Property->Dimensions->ShopSurface) : null,
						'living_type' => isset($living_types[$publication->Property->LivingRoomType]) ? $living_types[$publication->Property->LivingRoomType] : null,
						'garage_type' => isset($garage_types[$publication->Property->GarageType]) ? $garage_types[$publication->Property->GarageType] : null,
						'profession_room_type' => isset($profession_room_types[$publication->Property->ProfessionRoomType]) ? $profession_room_types[$publication->Property->ProfessionRoomType] : null,
						'office_type' => isset($office_types[$publication->Property->OfficeType]) ? $office_types[$publication->Property->OfficeType] : null,
						'storage_type' => isset($storage_types[$publication->Property->StorageRoom]) ? $storage_types[$publication->Property->StorageRoom] : null,
						'kitchen_type' => isset($kitchen_types[$publication->Property->KitchenGenre]) ? $kitchen_types[$publication->Property->KitchenGenre] : null,
						'cellar_type' => isset($cellar_types[$publication->Property->BasesType]) ? $cellar_types[$publication->Property->BasesType] : null,
						'cellar_evaluation' => isset($evaluations[$publication->Property->BasesEvaluation]) ? $evaluations[$publication->Property->BasesEvaluation] : null,
						'cellar_comment' => isset($publication->Property->BasesComment) ? $publication->Property->BasesComment : null,
						
						'has_elevator' => ($publication->Property->HasElevator == 'UNDEFINED' ? null : ($publication->Property->HasElevator == 'TRUE')),
						'has_alarm' => ($publication->Property->HasAlarm == 'UNDEFINED' ? null : ($publication->Property->HasAlarm == 'TRUE')),
						'furnished' => ($publication->Property->HasFurniture == 'UNDEFINED' ? null : ($publication->Property->HasFurniture == 'TRUE')),
						'kitchen_equipped' => $publication->Property->KitchenType == 'UNDEFINED' ? null : $publication->Property->KitchenType == 'BUILD_WITH_APPLIANCES',
						'pets_allowed' => ($publication->Property->PetsAllowed == 'UNDEFINED' ? null : ($publication->Property->PetsAllowed == 'TRUE')),
						'child_friendly' => ($publication->Property->IsChildFriendly == 'UNDEFINED' ? null : ($publication->Property->IsChildFriendly == 'TRUE')),
						'has_roller_blinds' => ($publication->Property->HasRollerBlinds == 'UNDEFINED' ? null : ($publication->Property->HasRollerBlinds == 'TRUE')),
						'has_heating' => ($publication->Property->HasHeating == 'UNDEFINED' ? null : ($publication->Property->HasHeating == 'TRUE')),
						'has_electricity' => ($publication->Property->HasElectricity == 'UNDEFINED' ? null : ($publication->Property->HasElectricity == 'TRUE')),
						'has_sanitary' => ($publication->Property->HasSanitary == 'UNDEFINED' ? null : ($publication->Property->HasSanitary == 'TRUE')),
						'has_external_solar_blinds' => ($publication->Property->ExternalSolarBlinds == 'UNDEFINED' ? null : ($publication->Property->ExternalSolarBlinds == 'TRUE')),
						'has_ventilation' => ($publication->Property->HasVentilation == 'UNDEFINED' ? null : ($publication->Property->HasVentilation == 'TRUE')),
						'garnished' => ($publication->Property->Garnished == 'UNDEFINED' ? null : !($publication->Property->Garnished == 'NO')),
						'has_cable_tv' => ($publication->Property->HasCable_TV == 'UNDEFINED' ? null : ($publication->Property->HasCable_TV == 'TRUE')),
						'has_cai_tv' => ($publication->Property->HasCAI_TV == 'UNDEFINED' ? null : ($publication->Property->HasCAI_TV == 'TRUE')),
						'has_pool' => ($publication->Property->HasPool == 'UNDEFINED' ? null : ($publication->Property->HasPool == 'TRUE')),
						'has_airco' => ($publication->Property->HasAirco == 'UNDEFINED' ? null : ($publication->Property->HasAirco == 'TRUE')),
						'has_jacuzzi' => ($publication->Property->HasJacuzzi == 'UNDEFINED' ? null : ($publication->Property->HasJacuzzi == 'TRUE')),
						'has_intercom' => ($publication->Property->HasIntercom == 'UNDEFINED' ? null : ($publication->Property->HasIntercom == 'TRUE')),
						'has_electricity_connection' => ($publication->Property->HasElectricityConnect == 'UNDEFINED' ? null : ($publication->Property->HasElectricityConnect == 'TRUE')),
						'has_gas_connection' => ($publication->Property->HasGasConnect == 'UNDEFINED' ? null : ($publication->Property->HasGasConnect == 'TRUE')),
						'has_water_connection' => ($publication->Property->HasWaterConnect == 'UNDEFINED' ? null : ($publication->Property->HasWaterConnect == 'TRUE')),
						'has_sewer_connection' => ($publication->Property->HasSewerConnect == 'UNDEFINED' ? null : ($publication->Property->HasSewerConnect == 'TRUE')),
						'has_internet_connection' => ($publication->Property->HasInternetConnect == 'UNDEFINED' ? null : ($publication->Property->HasInternetConnect == 'TRUE')),
						'has_fireplace' => ($publication->Property->HasFirePlace == 'UNDEFINED' ? null : ($publication->Property->HasFirePlace == 'TRUE')),
						'external_solar_blinds_comment' => $publication->Property->ExternalSolarBlindsComment,
						'ventilation_comment' => $publication->Property->VentilationComment,
						'cable_tv_comment' => $publication->Property->Cable_TVComment,
						'pool_comment' => $publication->Property->PoolComment,
						'elevator_evaluation' => isset($evaluations[$publication->Property->ElevatorEvaluation]) ? $evaluations[$publication->Property->ElevatorEvaluation] : null,
						'elevator_comment' => $publication->Property->ElevatorComment,
						'alarm_evaluation' => isset($evaluations[$publication->Property->AlarmEvaluation]) ? $evaluations[$publication->Property->AlarmEvaluation] : null,
						'alarm_comment' => $publication->Property->AlarmComment,
						'roller_blinds_evaluation' => isset($evaluations[$publication->Property->RollerBlindsEvaluation]) ? $evaluations[$publication->Property->RollerBlindsEvaluation] : null,
						'roller_blinds_comment' => $publication->Property->RollerBlindsComment,
						'isolation_evaluation' => isset($evaluations[$publication->Property->IsolationEvaluation]) ? $evaluations[$publication->Property->IsolationEvaluation] : null,
						'isolation_comment' => $publication->Property->IsolationComment,
						'isolation_type' => null,
						'klevel' => !isset($publication->Property->Energy->KLevel) || is_null($publication->Property->Energy->KLevel) ? null : intval($publication->Property->Energy->KLevel),
						'elevel' => !isset($publication->Property->Energy->EnergyLevel) || is_null($publication->Property->Energy->EnergyLevel) ? null : intval($publication->Property->Energy->EnergyLevel),
						'epc' => !isset($publication->Property->Energy->Index) || is_null($publication->Property->Energy->Index) || $publication->Property->Energy->Index == 0 ? null : intval($publication->Property->Energy->Index),
						'epc_certificate' => !isset($publication->Property->Energy->EnergyCertificateNr) || is_null($publication->Property->Energy->EnergyCertificateNr) ? null : $publication->Property->Energy->EnergyCertificateNr,
						'heating_type' => null,
						'heating_source' => null,
						'heating_evaluation' => isset($evaluations[$publication->Property->HeatingEvaluation]) ? $evaluations[$publication->Property->HeatingEvaluation] : null,
						'heating_comment' => $publication->Property->HeatingComment,
						'water_heating_type' => null,
						'water_heating_source' => null,
						'water_heating_evaluation' => isset($evaluations[$publication->Property->WarmWaterEvaluation]) ? $evaluations[$publication->Property->WarmWaterEvaluation] : null,
						
						'cadastrall_numbers' => $publication->Property->CadastrallNumbers,
						'cadastrall_area' => $publication->Property->CadastrallArea < 0 ? null : $publication->Property->CadastrallArea,
						'cadastrall_income' => floatval($publication->Property->CadastrallIncome) >= 0 ? floatval($publication->Property->CadastrallIncome) : null,
						'cadastrall_income_indexed' => floatval($publication->Property->CadastrallIncomeIndexed) >= 0 ? floatval($publication->Property->CadastrallIncomeIndexed) : null,
						'cadastrall_description' => $publication->Property->CadastrallDescription,
						'percent_private_usage' => ($publication->Property->UsagePrivatePercent <= 0 && $publication->Property->UsageProfessionalPercent <= 0) ? null : $publication->Property->UsagePrivatePercent,
						'percent_professional_usage' => ($publication->Property->UsagePrivatePercent <= 0 && $publication->Property->UsageProfessionalPercent <= 0) ? null : $publication->Property->UsageProfessionalPercent,
						
						'easement' => isset($easements[$publication->Property->Easement]) ? $easements[$publication->Property->Easement] : null,
						'restriction_comment' => $publication->Property->RestrictionComment,
						'environmental_planning_type' => isset($environmental_planning_types[$publication->Property->EnvironmentalPlanning]) ? $environmental_planning_types[$publication->Property->EnvironmentalPlanning] : null,
						'clauses' => $publication->Property->Clauses,
						'is_monument' => ($publication->Property->IsMonumentsAct == 'UNDEFINED' ? null : ($publication->Property->IsMonumentsAct == 'TRUE')),
						'is_protected' => ($publication->Property->IsProtected == 'UNDEFINED' ? null : ($publication->Property->IsProtected == 'TRUE')),
						'has_asbestus' => ($publication->Property->HasAsbestus == 'UNDEFINED' ? null : ($publication->Property->HasAsbestus == 'TRUE')),
						'has_ground_pollution' => ($publication->Property->HasGroundPollution == 'UNDEFINED' ? null : ($publication->Property->HasGroundPollution == 'TRUE')),
						'planning_permission' => ($publication->Property->UrbanDevelopment->Permit == 'UNDEFINED' ? null : ($publication->Property->UrbanDevelopment->Permit == 'TRUE')),
						'subdivision_permit' => ($publication->Property->UrbanDevelopment->AllotmentPermit == 'UNDEFINED' ? null : ($publication->Property->UrbanDevelopment->AllotmentPermit == 'TRUE')),
						'preemption_right' => ($publication->Property->UrbanDevelopment->PreemptiveRights == 'UNDEFINED' ? null : ($publication->Property->UrbanDevelopment->PreemptiveRights == 'TRUE')),
						'urbanism_citation' => ($publication->Property->UrbanDevelopment->Summons == 'UNDEFINED' ? null : ($publication->Property->UrbanDevelopment->Summons == 'TRUE')),
						'judicial_decision' => ($publication->Property->UrbanDevelopment->JudicialDecision == 'UNDEFINED' ? null : ($publication->Property->UrbanDevelopment->JudicialDecision == 'TRUE')),
						'land_use_designation' => isset($land_use_designations[$publication->Property->UrbanDevelopment->AreaApplication->Code]) ? $land_use_designations[$publication->Property->UrbanDevelopment->AreaApplication->Code] : null,
						//'flood_risk' => TODO
						//'flood_risk_type' => TODO
						
						'as_built_certificate_date' => null,
						'diagnostics_certificate_date' => null,
						'electricity_certificate_date' => null,
						'energy_consumption_certificate_date' => null,
						'energy_performance_certificate_date' => null,
						'gas_certificate_date' => null,
						'polution_certificate_date' => null,
						'accessibility_certificate_date' => null,
						'lead_certificate_date' => null,
						'nature_risk_certificate_date' => null,
						'oil_tank_certificate_date' => null,
						'planning_certificate_date' => null,
						'private_area_certificate_date' => null,
						'smoke_detection_certificate_date' => null,
						'soil_certificate_date' => null,
						
						'youtube_code' => null
				    );
				    
				    if (isset($publication->Property->Address->Position->Y)) $data['coord_lat'] = floatval(str_replace(',','.',$publication->Property->Address->Position->Y));
				    if (isset($publication->Property->Address->Position->X)) $data['coord_lon'] = floatval(str_replace(',','.',$publication->Property->Address->Position->X));
				    
				    if (isset($publication->Property->VideoUrl) && preg_match('/(?:http:|https:|)\/\/(?:player.|www.)?(?:youtu(?:be\.com|\.be|be\.googleapis\.com))\/(?:video\/|embed\/|watch\?v=|v\/)?([A-Za-z0-9._%-]*)(\&\S+)?/is',$publication->Property->VideoUrl,$match)) {
					    $data['youtube_code'] = $match[1];
				    }
				    
				    $data['sold'] = ($data['purpose_status'] == 2);
				    
				    if (preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/s', $publication->Property->Dates->AvailableFromText, $match)) {
					    $data['availability_date'] = mktime(0,0,0,$match[2],$match[3],$match[1]);
				    }
				    
				    $glazing_types_array = array();
				    if (isset($publication->Property->GlazingTypes->GlazingType)) {
					    if (!is_array($publication->Property->GlazingTypes->GlazingType)) $publication->Property->GlazingTypes->GlazingType = array($publication->Property->GlazingTypes->GlazingType);
					    foreach ($publication->Property->GlazingTypes->GlazingType as $glazing_type) {
						    if (isset($glazing_types[$glazing_type])) {
								$glazing_types_array[] = $glazing_types[$glazing_type];
						    }
					    }
				    }
				    $data['glazing_type'] = implode(',',$glazing_types_array);
				    
				    $heating_sources_array = array();
				    if (isset($publication->Property->HeatingTypes->HeatingType)) {
					    if (!is_array($publication->Property->HeatingTypes->HeatingType)) $publication->Property->HeatingTypes->HeatingType = array($publication->Property->HeatingTypes->HeatingType);
					    foreach ($publication->Property->HeatingTypes->HeatingType as $heating_type) {
						    if (isset($heating_types[$heating_type])) {
								$data['heating_type'] = $heating_types[$heating_type];
						    } else if (isset($heating_sources[$heating_type])) {
							    $heating_sources_array[] = $heating_sources[$heating_type];
							}
					    }
				    }
				    $data['heating_source'] = implode(',',$heating_sources_array);
				    
				    if (isset($publication->Property->WarmWaterTypes->WarmWaterType)) {
					    if (!is_array($publication->Property->WarmWaterTypes->WarmWaterType)) $publication->Property->WarmWaterTypes->WarmWaterType = array($publication->Property->WarmWaterTypes->WarmWaterType);
					    foreach ($publication->Property->WarmWaterTypes->WarmWaterType as $warm_water_type) {
						    if (isset($heating_types[$warm_water_type])) {
								$data['water_heating_type'] = $heating_types[$warm_water_type];
						    } else if (isset($water_heating_sources[$warm_water_type])) {
							    $data['water_heating_source'] = $water_heating_sources[$warm_water_type];
							}
					    }
				    }
				    
				    $plumbing_types_array = array();
				    if (isset($publication->Property->PlumbingTypes->PlumbingType)) {
					    if (!is_array($publication->Property->PlumbingTypes->PlumbingType)) $publication->Property->PlumbingTypes->PlumbingType = array($publication->Property->PlumbingTypes->PlumbingType);
					    foreach ($publication->Property->PlumbingTypes->PlumbingType as $plumbing_type) {
						    if (isset($plumbing_types[$plumbing_type])) {
							    $plumbing_types_array[] = $plumbing_types[$plumbing_type];
						    }
					    }
				    }
				    $data['plumbing_type'] = implode(',',$plumbing_types_array);
				    
				    if (isset($publication->Property->IsolationTypes->IsolationType)) {
					    if (!is_array($publication->Property->IsolationTypes->IsolationType)) $publication->Property->IsolationTypes->IsolationType = array($publication->Property->IsolationTypes->IsolationType);
					    foreach ($publication->Property->IsolationTypes->IsolationType as $isolation_type) {
						    if (isset($isolation_types[$isolation_type])) {
							    $data['isolation_type'] = $isolation_types[$isolation_type];
							    break;
						    }
					    }
				    }
				    
				    if (isset($publication->Property->Certifications->Certification)) {
					    if (!is_array($publication->Property->Certifications->Certification)) $publication->Property->Certifications->Certification = array($publication->Property->Certifications->Certification);
					    foreach ($publication->Property->Certifications->Certification as $certification) {
						    if (isset($certificate_fields[$certification->Type]) && isset($certification->Date) && preg_match('/^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/s', $certification->Date, $match)) {
							    $data[$certificate_fields[$certification->Type]] = mktime(0,0,0,$match[2],$match[3],$match[1]);
						    }
					    }
				    }
				    
				    if (isset($subcategory_ids[$publication->Property->Typo->Genre])) {
					    $data['subcategory_id'] = $subcategory_ids[$publication->Property->Typo->Genre];
				    } else {
					    $data['subcategory_id'] = $subcategory_ids[$publication->Property->Typo->Sort];
					}
				    
				    foreach (array_keys(languages()) as $language) {
					    if (!isset($subcategories[$publication->Property->Typo->Genre][$language]) && !isset($subcategories[$publication->Property->Typo->Sort][$language])) continue;
					    if (isset($subcategories[$publication->Property->Typo->Genre][$language])) {
						    $data['subcategory_'.$language] = $subcategories[$publication->Property->Typo->Genre][$language];
					    } else {
						    $data['subcategory_'.$language] = $subcategories[$publication->Property->Typo->Sort][$language];
						}
						$data['subcategory_slug_'.$language] = slug_format($data['subcategory_'.$language]);
						$data['slug_'.$language] = slug_format($data['subcategory_slug_'.$language].'-'.l(array('nl'=>'in','fr'=>'a','en'=>'in'),$language).'-'.$data['city']);
						if (isset($styles[$publication->Property->Typo->Characterisation][$language])) {
							$data['style_'.$language] = $styles[$publication->Property->Typo->Characterisation][$language];
						}
						
						for ($i = 1; $i <= 5; $i++) {
							if (isset($flashes[$i][$language])) {
								$flash = $flashes[$i][$language];
								$data['flash'.$i.'_title_'.$language] = $flash->Title;
								$data['flash'.$i.'_content_'.$language] = $flash->Text;
							}
						}
						
						$data['features_'.$language] = isset($features[$language]) ? json_encode($features[$language]) : '[]';
				    }
				    
					if ($existing = where('software_id = %d',$data['software_id'])->get_row('skarabee>property')) {
						
						// Update existing photos
						$existing_photos = array();
						foreach ($existing->photo as $current_photo) {
							$found = false;
					    	foreach ($photos as $key => $photo) {
						    	if ($photo['orig_filename'] == $current_photo->orig_filename) {
						    		if ($key != $current_photo->_sort_order || $photo['type'] != $current_photo->image_type) {
						    			where('id = %d',$current_photo->id)->update('skarabee>property>photo',array(
							    			'_sort_order' => $key,
							    			'image_type' => $photo['type']
						    			));
						    		}
						    		$existing_photos[] = $key;
						    		$found = true;
						    	}
					    	}
					    	if (!$found) {
						    	where('id = %d',$current_photo->id)->delete('skarabee>property>photo');
						    }
						}
						$photos = array_diff_key($photos,array_flip($existing_photos));
						
						// Update existing documents
						$existing_documents = array();
						foreach ($existing->file as $current_file) {
							$found = false;
					    	foreach ($documents as $key => $document) {
						    	if ($document['url'] == $current_file->url) {
						    		if ($document['name'] != $current_file->name || $document['filename'] != $current_file->filename) {
						    			where('id = %d',$current_file->id)->update('skarabee>property>file',$document);
						    		}
						    		$existing_documents[] = $key;
						    		$found = true;
						    	}
					    	}
					    	if (!$found) {
						    	where('id = %d',$current_file->id)->delete('skarabee>property>file');
						    }
						}
						$documents = array_diff_key($documents,array_flip($existing_documents));
						
						// Update existing open house
						$existing_records = array();
						foreach ($existing->open_house as $current_record) {
							$found = false;
					    	foreach ($openhouse as $key => $record) {
						    	if ($record['from'] == $current_record->fromdate && $record['to'] == $current_record->todate) {
						    		if ($record['comment'] != $current_record->comment) {
						    			where('id = %d',$current_record->id)->update('skarabee>property>open_house',array(
							    			'comment' => $record['comment']
						    			));
						    		}
						    		$existing_records[] = $key;
						    		$found = true;
						    	}
					    	}
					    	if (!$found) {
						    	where('id = %d',$current_record->id)->delete('skarabee>property>open_house');
						    }
						}
						$openhouse = array_diff_key($openhouse,array_flip($existing_records));
						
						// Update existing floors
						$existing_floors = array();
						foreach ($existing->floor as $current_floor) {
							if (isset($floors[$current_floor->level])) {
								if (count(array_diff_assoc($floors[$current_floor->level],$current_floor->to_array()))) {
									foreach ($floors[$current_floor->level] as $key => $value) {
										if ($value != $current_floor->$key) {
											where('id = %d',$current_floor->id)->update('skarabee>property>floor',$floors[$current_floor->level]);
											break;
										}
									}
								}
								$existing_floors[] = $current_floor->level;
							} else {
								where('id = %d',$current_record->id)->delete('skarabee>property>floor');
							}
						}
						$floors = array_diff_key($floors,array_flip($existing_floors));
						
						// Update data
						where('id = %d',$existing->id)->update('skarabee>property',$data);
						
						$id = $existing->id;
						
					} else {
				    	
						$id = insert('skarabee>property',$data);
						
					}
					
					// Create new photos
					foreach ($photos as $key => $photo) {
						$newphoto = array(
				    		'property_id' => $id,
				    		'orig_filename' => $photo['orig_filename'],
				    		'filename' => 'properties/'.$id.'/'.md5($photo['orig_filename']).'.jpg',
				    		'upload_date' => time(),
				    		'_sort_order' => $key,
			    			'image_type' => $photo['type']
				    	);
						foreach (array_keys(languages()) as $language) {
							$newphoto['alt_'.$language] = $data['subcategory_'.$language].' '.l(array('nl'=>'in','fr'=>'a','en'=>'in'),$language).' '.$data['city'];
							$newphoto['slug_'.$language] = slug_format($newphoto['alt_'.$language]);
						}
						
						if (!file_exists(FILESPATH.'properties')) mkdir(FILESPATH.'properties');
						if (!file_exists(FILESPATH.'properties/'.$id)) mkdir(FILESPATH.'properties/'.$id);
				    	
				    	if (self::download_remote_file($photo['url'], FILESPATH.$newphoto['filename'])) {
				    		insert('skarabee>property>photo',$newphoto);
				    	}
					}
					
					// Add new documents
					foreach ($documents as $key => $document) {
						$document['property_id'] = $id;
				    	insert('skarabee>property>file',$document);
					}
					
					// Add new open house
					foreach ($openhouse as $key => $record) {
						insert('skarabee>property>open_house',array(
				    		'property_id' => $id,
				    		'comment' => $record['comment'],
				    		'fromdate' => $record['from'],
				    		'todate' => $record['to']
				    	));
					}
					
					// Add new floors
					foreach ($floors as $level => $floor) {
						$floor['property_id'] = $id;
						insert('skarabee>property>floor',$floor);
					}
					
					$present_properties[] = $data['software_id'];
					
					$current = where('id = %d',$id)->get_row('skarabee>property');
					
					$feedbacks[] = array(
						'PublicationID' => $data['publication_id'],
						'Status' => 'AVAILABLE',
						'StatusDescription' => $existing ? 'Property was updated' : 'Property was created',
						'ExternalID' => strval($id),
						'URL' => $current->url
					);
					
				}
			    
		    }
		    
		    if (count($present_properties)) {
			    foreach (where('software_id NOT IN %$',$present_properties)->get('skarabee>property') as $todelete) {
				    $feedbacks[] = array(
						'PublicationID' => $todelete->publication_id,
						'Status' => 'DELETED',
						'StatusDescription' => 'Property was deleted',
						'ExternalID' => $todelete->id,
						'URL' => $todelete->url
					);
			    }
			    where('software_id NOT IN %$',$present_properties)->delete('skarabee>property');
		    }
		    
		    $site = current_site();
		    if ($site->live) {
			    $feedbackresponse = $client->Feedback(array( 'FeedbackList' => array( 'FeedbackList' => $feedbacks ) ));
			}
	    }
	    
	}
	
	private static function download_remote_file($url,$target) {
	    if ($file = @fopen($url,"rb")) {
		    if ($newf = fopen($target, "wb")) {
				while(!feof($file)) {
					fwrite($newf, fread($file, 1024 * 8 ), 1024 * 8 );
				}
			}
			fclose($file);
			if ($newf) {
				fclose($newf);
				$result = (filesize($target) > 1);
				if (!$result) unlink($target);
				return $result;
			} else {
				return false;
			}
	    } else {
	    	return false;
	    }
	}

	/**
	* Adds a contact's info to the Skarabee database. To be used whenever a visitor fills out a contact form.
	*
	* @param string $user_firstname
	*	The user's first name
	* @param string $user_lastname
	*	The user's last name
	* @param string $user_email
	*	The user's e-mail address
	* @param string $user_message
	*	The message the user sent to the realtor
	* @param Skarabeeproperty $property optional
	*	The property the user is contacting the realtor about
	* @param string $user_phone optional
	*	The user's phone number
	* @param string $user_mobile_phone optional
	*	The user's mobile phone number
	* @param string $user_postal optional
	*	The postal code of the user's address
	* @param string $user_city optional
	*	The city of the user's address
	* @param string $user_street optional
	*	The street of the user's address
	* @param string $user_house_number optional
	*	The house number of the user's address
	*
	* @return boolean Returns whether or not the data was accepted by Skarabee
	*/
	public static function save_contact($user_firstname, $user_lastname, $user_email, $user_message, $property = false, $user_phone = false, $user_mobile_phone = false, $user_postal = false, $user_city = false, $user_street = false, $user_house_number = false) {
		$client = self::get_client();
		
		use_library('libphonenumber');
		$phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
		
		$data = array(
			'FirstName' => capitalize(trim($user_firstname)),
			'LastName' => capitalize(trim($user_lastname)),
			'Comments' => trim($user_message),
			'Email' => strtolower(trim($user_email))
		);
		
		if ($property && isset($property->software_id)) $data['PublicationID'] = $property->software_id;
		
		if ($user_phone) {
			try {
				$phone = $phoneUtil->parse($user_phone, strtoupper('be'));
				$data['Phone'] = $phoneUtil->format($phone, \libphonenumber\PhoneNumberFormat::NATIONAL);
			} catch (\libphonenumber\NumberParseException $e) {
				$data['Phone'] = $user_phone;
			}
		}
		
		if ($user_mobile_phone) {
			try {
				$phone = $phoneUtil->parse($user_mobile_phone, strtoupper('be'));
				$data['CellPhone'] = $phoneUtil->format($phone, \libphonenumber\PhoneNumberFormat::NATIONAL);
			} catch (\libphonenumber\NumberParseException $e) {
				$data['CellPhone'] = $user_mobile_phone;
			}
		}
		
		if ($user_city) $data['City'] = capitalize(trim($user_city));
		if ($user_postal) $data['ZipCode'] = strtoupper(trim($user_postal));
		if ($user_street) $data['Street'] = capitalize(trim($user_street),false);
		if ($user_house_number) $data['HouseNumber'] = capitalize(trim($user_house_number));
		
		$result = $client->InsertContactMes(array(
			'ContactMes' => array($data)
		));
		
		return !isset($result->InsertContactMesResult->InvalidContactMes->InvalidContactMe);
	}
	

	/**
	* Connects to the Skarabee server with the configured credentials
	*
	* @return SoapClient Returns a client to be used for SOAP requests
	*/
	private static function get_client() {
		
		if (self::$client) return self::$client;
		
		$filedir = pathinfo(__FILE__,PATHINFO_DIRNAME);
		
		self::$client = new SoapClient($filedir.'/soap_weblink_wsdl.xml', array( 
	        'login' => Config::field('skarabee_username'), 
	        'password' => Config::field('skarabee_password'), 
	        'cache_wsdl' => WSDL_CACHE_NONE,
	        'trace' => true,
	        'location' => 'http://weblink.skarabee.com/v33/weblink.asmx'
	    ));
	    
	    return self::$client;
	}
	
}
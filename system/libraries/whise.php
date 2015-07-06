<?

function getContactTitles() {
	
	$contacttitles = array();
	
	$filename = FILESPATH.'_whise_contact_titles';
	
	if (file_exists($filename)) {
		$time = filemtime($filename);
		if ($time + 86400 > time()) {
			return json_decode(file_get_contents($filename),true);
		}
	}
	$contacttypes = @file_get_contents('http://webservices.whoman2.be/websiteservices/EstateService.svc/GetContactTitleList?EstateServiceGetContactTitleListRequest={"ClientId":"'.Config::field('whise_id').'","Page":0,"RowsPerPage":100,"Language":"nl-BE"}');
	if ($contacttypes) $contacttypes = json_decode($contacttypes,true);
	if (isset($contacttypes['d']['ContactTitleList'])) {
		
		if (!is_array(reset($contacttypes['d']['ContactTitleList']))) $contacttypes['d']['ContactTitleList'] = array($contacttypes['d']['ContactTitleList']);
		
		foreach ($contacttypes['d']['ContactTitleList'] as $contacttype) {
			$contacttitles[$contacttype['ContactTitleId']] = $contacttype['ShortDescription'];
		}
		
		$fh = fopen($filename, 'w');
		fwrite($fh, json_encode($contacttitles));
		fclose($fh);
		
		return $contacttitles;
		
	}
	return json_decode(file_get_contents($filename),true);
}

function addHMODHToWhise($contactdetails,$searchdetails) {
	
	$offices = json_decode(@file_get_contents('http://webservices.whoman2.be/websiteservices/EstateService.svc/GetOfficeList?EstateServiceGetOfficeListRequest=%7b%22ClientId%22:%22'.Config::field('whise_id').'%22,%22Page%22:0,%22RowsPerPage%22:10,%22Language%22:%22nl-BE%22%7d'),true);
	
	if (!isset($offices['d']['OfficeList']) || !is_array($offices['d']['OfficeList'])) return false;
	
	$office = array_shift($offices['d']['OfficeList']);
	
	if (isset($searchdetails['Message'])) {
		$contactdetails['Message'] = $searchdetails['Message'];
		unset($searchdetails['Message']);
	}
	
	$purposecontacttypes = array(1=>0,2=>0);
	$contacttypes = json_decode(@file_get_contents('http://webservices.whoman2.be/websiteservices/EstateService.svc/GetContactTypeList?EstateServiceGetContactTypeListRequest=%7b%22OfficeId%22:'.$office['OfficeId'].',%22ClientId%22:%22'.Config::field('whise_id').'%22,%22Page%22:0,%22RowsPerPage%22:10,%22Language%22:%22nl-BE%22%7d'),true);
	if (isset($contacttypes['d']['BaseContactTypeList']) && is_array($contacttypes['d']['BaseContactTypeList'])) {
		foreach ($contacttypes['d']['BaseContactTypeList'] as $basecontacttype) {
			$contacttype = reset($basecontacttype['ContactTypes']);
			if ($basecontacttype['Name'] == 'potentiele koper') $purposecontacttypes[1] = $contacttype['ContactTypeId'];
			if ($basecontacttype['Name'] == 'potentiele huurder') $purposecontacttypes[2] = $contacttype['ContactTypeId'];
		}
	}
	
	$data = array(
		'estateServiceUpdateContactRequest' => array_merge(array(
			'__type' => 'EstateServiceUpdateContactRequest:Whoman.Estate',
			
			'ClientID' => Config::field('whise_id'),
			'OfficeID' => $office['OfficeId'],
			'Comments' => 'Contactpersoon aangemaakt door website',
			'Message' => '',
			
			'FirstName' => '',
			'Name' => '',
			'PrivateEmail' => '',
			'PrivateTel' => '',
			
			'ContactTypeIDList' => array(),
			
			'SearchCriteria' => array_merge(array(
				'__type' => 'EstateServiceUpdateContactRequestSearchCriteria:Whoman.Estate',
				'CategoryIdList' => array(),
				'PriceRange' => array(0,0),
				'PurposeIdList' => array(),
				'PurposeStatusIdList' => array(),
				'RegionIDList' => array(),
				'SubCategoryIdList' => array(),
				'ZipList' => array(),
				'SubCategoryIdList' => array(),
				'CountryID' => 1,
				'Zip' => ''
			),$searchdetails),
			
			'Address1' => '',
			'Address2' => '',
			'AgreementMail' => 1,
			'AgreementSms' => 0,
			'Box' => '',
			'City' => '',
			'ContactOriginID' => 0,
			'ContactTitleID' => 0,
			'CountryID' => 1,
			'LanguageID' => str_replace('_', '-', setlocale(LC_ALL, 0)),
			'Number' => '',
			'Zip' => ''
		), $contactdetails)
	);
	
	if (isset($searchdetails['PurposeIdList']) && is_array($searchdetails['PurposeIdList'])) {
		foreach ($searchdetails['PurposeIdList'] as $purpose) {
			$data['estateServiceUpdateContactRequest']['ContactTypeIDList'][] = $purposecontacttypes[$purpose];
		}
	}
	
	$ch = curl_init('http://webservices.whoman2.be/websiteservices/EstateService.svc/UpdateContact');
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	
	return json_decode($output);
}

function addContactToWhise($first_name,$last_name,$email_address,$phone_number='',$message='',$property=false,$property_url='') {
	
	$offices = json_decode(@file_get_contents('http://webservices.whoman2.be/websiteservices/EstateService.svc/GetOfficeList?EstateServiceGetOfficeListRequest=%7b%22ClientId%22:%22'.Config::field('whise_id').'%22,%22Page%22:0,%22RowsPerPage%22:10,%22Language%22:%22nl-BE%22%7d'),true);
	
	if (!isset($offices['d']['OfficeList']) || !is_array($offices['d']['OfficeList'])) return false;
	
	$office = array_shift($offices['d']['OfficeList']);
	
	$purposecontacttypes = array(1=>0,2=>0);
	
	$contacttypes = json_decode(@file_get_contents('http://webservices.whoman2.be/websiteservices/EstateService.svc/GetContactTypeList?EstateServiceGetContactTypeListRequest=%7b%22OfficeId%22:'.$office['OfficeId'].',%22ClientId%22:%22'.Config::field('whise_id').'%22,%22Page%22:0,%22RowsPerPage%22:10,%22Language%22:%22nl-BE%22%7d'),true);
	
	if (isset($contacttypes['d']['BaseContactTypeList']) && is_array($contacttypes['d']['BaseContactTypeList'])) {
		foreach ($contacttypes['d']['BaseContactTypeList'] as $basecontacttype) {
			$contacttype = reset($basecontacttype['ContactTypes']);
			if ($basecontacttype['Name'] == 'potentiele koper') $purposecontacttypes[1] = $contacttype['ContactTypeId'];
			if ($basecontacttype['Name'] == 'potentiele huurder') $purposecontacttypes[2] = $contacttype['ContactTypeId'];
		}
	}
	
	$data = array(
		'estateServiceUpdateContactRequest' => array(
			'__type' => 'EstateServiceUpdateContactRequest:Whoman.Estate',
			
			'ClientID' => Config::field('whise_id'),
			'OfficeID' => $office['OfficeId'],
			'Comments' => 'Contactpersoon aangemaakt door website'.($property_url?' ('.$property_url.')':''),
			'Message' => $message,
			
			'FirstName' => $first_name,
			'Name' => $last_name,
			'PrivateEmail' => $email_address,
			'PrivateTel' => $phone_number,
			
			'ContactTypeIDList' => array(),
			
			'SearchCriteria' => array(
				'__type' => 'EstateServiceUpdateContactRequestSearchCriteria:Whoman.Estate',
				'CategoryIdList' => array(),
				'PriceRange' => array(0,0),
				'PurposeIdList' => array(),
				'PurposeStatusIdList' => array(),
				'RegionIDList' => array(),
				'SubCategoryIdList' => array(),
				'ZipList' => array(),
				'SubCategoryIdList' => array(),
				'CountryID' => 1,
				'Zip' => ''
			),
			
			'Address1' => '',
			'Address2' => '',
			'AgreementMail' => 1,
			'AgreementSms' => 0,
			'Box' => '',
			'City' => '',
			'ContactOriginID' => 0,
			'ContactTitleID' => 0,
			'CountryID' => 1,
			'LanguageID' => str_replace('_', '-', setlocale(LC_ALL, 0)),
			'Number' => '',
			'Zip' => ''
		)
	);
	
	if ($property) {
		$data['estateServiceUpdateContactRequest']['ContactTypeIDList'] = array($purposecontacttypes[$property->purpose]);
		$data['estateServiceUpdateContactRequest']['SearchCriteria']['CategoryIdList'] = array($property->category_id);
		$data['estateServiceUpdateContactRequest']['SearchCriteria']['PurposeIdList'] = array($property->purpose);
		$data['estateServiceUpdateContactRequest']['SearchCriteria']['PurposeStatusIdList'] = array($property->purpose_status);
		$data['estateServiceUpdateContactRequest']['SearchCriteria']['ZipList'] = array($property->postal);
		$data['estateServiceUpdateContactRequest']['EstateID'] = $property->whise_id;
	}
	
	$ch = curl_init('http://webservices.whoman2.be/websiteservices/EstateService.svc/UpdateContact');
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	
	return json_decode($output);
}
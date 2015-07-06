<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Address extends FW4_Type {

	protected static $script_printed = false;
	
	public function print_field($field,$data,$object) { 
		$fieldname = $field['name'];
		$fieldname_address = $field['name'].'_address';
		$fieldname_postal_code = $field['name'].'_postal_code';
		$fieldname_city = $field['name'].'_city';
		$fieldname_country = $field['name'].'_country';
		$fieldname_coordinates = $field['name'].'_coordinates'; ?>
		
		<? if (isset($field['readonly'])): ?>
			<? if (isset($data->id)): ?>
				<div class="input"><label for="<?=$field['name']?>"<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
					<fieldset style="width:300px;float:left;">
						<div class="field">
							<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_address"><?=l(array('nl'=>'Adres','fr'=>'Adresse','en'=>'Address'))?></label>
							<div class="value"><?=isset($data->$fieldname_address)?$data->$fieldname_address:''?></div>
							<input type="hidden" name="<?=$field['name']?>_address" class="address_address" maxlength="150"<?=isset($field['required'])?' required="required"':''?> value="<?=isset($data->$fieldname_address)?$data->$fieldname_address:''?>"/>
						</div>
						<div class="field">
							<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_city"><?=l(array('nl'=>'Postcode','fr'=>'Code','en'=>'Zip code'))?></label>
							<div class="value"><?=isset($data->$fieldname_postal_code)?$data->$fieldname_postal_code:''?></div>
							<input type="hidden" name="<?=$field['name']?>_postal_code" class="address_postal_code" maxlength="6"<?=isset($field['required'])?' required="required"':''?> value="<?=isset($data->$fieldname_postal_code)?$data->$fieldname_postal_code:''?>"/>
						</div>
						<div class="field">
							<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_city"><?=l(array('nl'=>'Gemeente','fr'=>'Commune','en'=>'City'))?></label>
							<div class="value"><?=isset($data->$fieldname_city)?$data->$fieldname_city:''?></div>
							<input type="hidden" name="<?=$field['name']?>_city" class="address_city" maxlength="100"<?=isset($field['required'])?' required="required"':''?> value="<?=isset($data->$fieldname_city)?$data->$fieldname_city:''?>"/>
						</div>
						<? if (isset($field['display_country'])): ?>					
							<div class="field">
								<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_country"><?=l(array('nl'=>'Land','fr'=>'Pays','en'=>'Country'))?></label>
								<div class="value"><?=isset($data->$fieldname_country)?$data->$fieldname_country:''?></div>
								<input type="hidden" name="<?=$field['name']?>_country" class="address_country" maxlength="150" value="<?=isset($data->$fieldname_country)?$data->$fieldname_country:''?>"/>
							</div>
						<? endif; ?>
						<input type="hidden" name="<?=$field['name']?>_coordinates" class="address_coordinates" value="<?=isset($data->$fieldname_coordinates)?$data->$fieldname_coordinates:''?>"/>
					</fieldset>
					
					<div class="address_map" style="width:250px;height:183px;margin:0 0 20px 390px;border:1px solid #999;"></div>
				</div>
				<div class="clear"></div>
			<? endif; ?>
		<? else: ?>
    	
			<div class="input"><label for="<?=$field['name']?>"<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label>
				<fieldset style="width:300px;float:left;">
					<div class="field">
						<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_address"><?=l(array('nl'=>'Adres','fr'=>'Adresse','en'=>'Address'))?></label>
						<input type="text" name="<?=$field['name']?>_address" class="address_address" maxlength="150"<?=isset($field['required'])?' required="required"':''?> value="<?=isset($data->$fieldname_address)?$data->$fieldname_address:''?>"/>
					</div>
					<div class="field">
						<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_city"><?=l(array('nl'=>'Postcode','fr'=>'Code','en'=>'Zip code'))?></label>
						<input type="text" name="<?=$field['name']?>_postal_code" class="address_postal_code" maxlength="6"<?=isset($field['required'])?' required="required"':''?> value="<?=isset($data->$fieldname_postal_code)?$data->$fieldname_postal_code:''?>"/>
					</div>
					<div class="field">
						<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_city"><?=l(array('nl'=>'Gemeente','fr'=>'Commune','en'=>'City'))?></label>
						<input type="text" name="<?=$field['name']?>_city" class="address_city" maxlength="100"<?=isset($field['required'])?' required="required"':''?> value="<?=isset($data->$fieldname_city)?$data->$fieldname_city:''?>"/>
					</div>
					<? if (isset($field['display_country'])): ?>					
						<div class="field">
							<label style="display:inline-block;width:80px;" for="<?=$field['name']?>_country"><?=l(array('nl'=>'Land','fr'=>'Pays','en'=>'Country'))?></label>
							<input type="text" name="<?=$field['name']?>_country" class="address_country" maxlength="150" value="<?=isset($data->$fieldname_country)?$data->$fieldname_country:''?>"/>
						</div>
					<? endif; ?>
                    <input type="hidden" name="<?=$field['name']?>_coordinates" class="address_coordinates" value="<?=isset($data->$fieldname_coordinates)?$data->$fieldname_coordinates:''?>"/>
				</fieldset>
				
				<div class="address_map" style="width:250px;height:183px;margin:0 0 20px 390px;border:1px solid #999;"></div>
			</div>
			<div class="clear"></div>
			
		<? endif; ?>
		
    <? }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
	    if (!isset($field['readonly'])) {
			$data[strval($field['name']).'_address'] = ($newdata[strval($field['name']).'_address']);
			$data[strval($field['name']).'_city'] = ($newdata[strval($field['name']).'_city']);
			$data[strval($field['name']).'_coordinates'] = $newdata[strval($field['name']).'_coordinates'];
			$data[strval($field['name']).'_postal_code'] = $newdata[strval($field['name']).'_postal_code'];
			if (isset($newdata[strval($field['name']).'_country'])) ($data[strval($field['name']).'_country'] = $newdata[strval($field['name']).'_country']);
		}
		return $data;
	}
    
    function get_structure($field,$fields) {
    	return '<structure>
    		<string name="'.$field['name'].'_address" length="150"/>
    		<string name="'.$field['name'].'_city" length="100"/>
    		<string name="'.$field['name'].'_coordinates" length="30"/>
    		<string name="'.$field['name'].'_postal_code" length="6"/>'.
    		(isset($field['display_country'])?'<string name="'.$field['name'].'_country" length="150"/>':'').'
    	</structure>';
    }
    
    public function summary($field,$data,$object) {
    	$address = $field['name'].'_address';
    	$postal = $field['name'].'_postal_code';
    	$city = $field['name'].'_city';
	    return $data->$address.($data->$address?', ':'').$data->$postal.' '.$data->$city;
    }
    
    public function get_scripts() {
	    return '<script src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
		<script>
           
			var geocoder;
			$(function(){
            
				geocoder = new google.maps.Geocoder();
				$(\'.address_map\').each(function(){
                
					var center = new google.maps.LatLng(50.99, 4.3);
					var zoom = 9;
					
					var myOptions = {
						zoom: zoom,
						center: center,
						mapTypeId: google.maps.MapTypeId.ROADMAP,
						center: new google.maps.LatLng(50.99, 4.3),
						panControl: false,
						zoomControl: true,
						zoomControlOptions: {
							position: google.maps.ControlPosition.RIGHT_TOP
						},
						mapTypeControl: false,
						scaleControl: false,
						streetViewControl: false,
						overviewMapControl: false,
					};
					
					var map = new google.maps.Map($(this).get(0), myOptions);
					
					var fieldset = $(this).siblings(\'fieldset\');
					
					map.markersArray = [];
					 
					if (fieldset.find(\'input.address_address\').val() && fieldset.find(\'input.address_city\').val() && fieldset.find(\'input.address_postal_code\').val()) {
					       
						geocoder.geocode( { \'address\': fieldset.find(\'input.address_address\').val()+\',\'+fieldset.find(\'input.address_postal_code\').val()+\' \'+fieldset.find(\'input.address_city\').val()}, function(results, status) {
                        
							if (status == google.maps.GeocoderStatus.OK) {
                           
								map.setCenter(results[0].geometry.location);
								var marker = new google.maps.Marker({
								    map: map,
								    position: results[0].geometry.location
								});
                               map.markersArray.push(marker);
							}
						});
					}
					
					fieldset.find(\'input\').change(function(){
						if (fieldset.find(\'input.address_address\').val() && fieldset.find(\'input.address_postal_code\').val() && fieldset.find(\'input.address_city\').val()) {
						
							$.each(map.markersArray, function(index, value) { 
								map.markersArray[index].setMap(null);
							});
							
							map.markersArray.length = 0;
							geocoder.geocode( { \'address\': fieldset.find(\'input.address_address\').val()+\',\'+fieldset.find(\'input.address_postal_code\').val()+\' \'+fieldset.find(\'input.address_city\').val()+(fieldset.find(\'input.address_country\').length > 0?\' \'+fieldset.find(\'input.address_country\').val():\'\')}, function(results, status) {
								if (status == google.maps.GeocoderStatus.OK) {
									map.setCenter(results[0].geometry.location);
									var marker = new google.maps.Marker({
									    map: map,
									    position: results[0].geometry.location
									});
								    fieldset.find(\'input.address_coordinates\').val(results[0].geometry.location.lat() + \';\' + results[0].geometry.location.lng());
									map.markersArray.push(marker);
								} else fieldset.find(\'input.address_coordinates\').val(\'\');
							});
						}
					});
				});
			});
		</script>';
    }

}
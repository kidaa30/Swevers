<?php

/* -------------------------
FW4 FRAMEWORK - TYPE MANAGER
----------------------------

The type manager and type classes allow the addition of new datatypes and admin plugins. */

class FW4_Type {
	
	// System requests all objects that this type contains. When parent is given, return only object corresponding to said parent. If type can not contain objects (if it's just a field), return false;
	public function get_objects($fieldname, $field, $parent_name = false, $parent_id = false) {
		return false;
	}
	
	// A row containing this type was retrieved from the database, process it
	// public function on_fetch($field,$data,$object) { }
	
	// Prepare data for database insertion. ($data may contain other fields)
	public function insert($data,$field,$newdata,$olddata,$object) {
		return $this->edit($data,$field,$newdata,$olddata,$object);
	}
	
	// Prepare data for database insertion. ($data may contain other fields)
	public function update($data,$field,$newdata,$olddata,$object) {
		return $this->edit($data,$field,$newdata,$olddata,$object);
	}
	
	public function edit($data,$field,$newdata,$olddata,$object) {
		if (isset($newdata[strval($field['name'])])) $data[strval($field['name'])] = $newdata[strval($field['name'])];
		return $data;
	}
	
	public function edited($field,$data,$object) {
		
	}
	
	// Process data deletion
	public function delete($fieldname,$field,$table,$id) {
		
	}
	public function deleted($field,$data) {
		
	}
	
	// Format data retrieved from database
	public function format_data($data,$fieldname,$field,$language) {
		return $data;
	}
	
	// Define table structure
	public function get_structure($field,$fields) {
		return '<structure><string name="'.$field['name'].'" length="250"/></structure>';
	}
		
	// Validate the submitted data (set 'invalid' on the appropriate fields in the $fields array)
	public function validate($fields,$fieldname,$orig_data) {
		if (isset($_POST[$fieldname])) {
			$field = $fields[$fieldname];
			if (isset($field['required']) && $field['required'] && !trim($_POST[$fieldname])) {
				$fields[$fieldname]['invalid'] = true;
			}
		}
		return $fields;
	}
	
	function get_scripts() {
		return '';
	}
	
	// Print the input field for the admin page
	public function print_field($field,$data,$object) { ?>
		<div class="input"><label for="<?=$field['name']?>"<?=isset($field['invalid'])&&$field['invalid']?' class="invalid"':''?>><?=isset($field['label'])?$field['label']:ucwords(preg_replace("/[^\w-]+/i"," ",$field['name']))?></label> <input type="text" name="<?=$field['name']?>" value="<?=$data[$field['name']]?>" maxlength="<?=$field['length']?>" /></div>
	<? }

}
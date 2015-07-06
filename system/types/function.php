<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Function_Type extends FW4_Type {

    public function print_field($field,$data,$object) { 
	    if (FW4_Admin::$in_fieldset): ?>
	    	<a class="button" href="<?=$field['name']?>/call/"><?=strval($field)?></a>
	    <? else: ?>
    	<div class="input">
    		<a class="button" href="<?=$field['name']?>/call/"><?=strval($field)?></a>
    	</div>
    	<? endif;
    }
    
    public function function_call($field,$object,$data) {
		
		$classname = ucfirst($object['contentname']);
		$function_name = strval($field['name']);
		if (class_exists($classname) && method_exists($classname,$function_name)) {
			View_Loader::get_instance()->set_path(CONTENTPATH.$object['contentname']);
			$result = call_user_func_array($classname.'::'.$function_name, array($object,$data));
			
			if ($result) {
				echo view("head",array(
					"pages" => FW4_Admin::get_pages(),
					"title" => strval($field),
					"user" => FW4_User::get_user(),
					"site" => current_site()
				));
				
				echo '<h2>'.strval($field).'</h2>';
				
				echo '<div class="input">'.$result.'</div>';
		    	
		    	echo '<div class="controls">';
		    	echo '<a class="button save" href="'.preg_replace('/[^\/]+\/[^\/]+\/?$/', '', $_SERVER['REQUEST_URI']).'">'.l(array('nl'=>'Terug','fr'=>'Retour','en'=>'Back')).'</a>';
		    	if (isset($field['allow_print'])) echo '<a class="button right" href="#" onclick="window.print();return false;">'.l(array('nl' => 'Afdrukken','fr' => 'Imprimer','en' => 'Print','de' => 'Drucken')).'</a>';
		    	echo '</div>';
				
				echo view("foot",array(
					'scripts' => array()
				));
				
				exit;
			}
		}
		redirect($_SERVER['HTTP_REFERER']);
    }
    
    function get_structure($field,$fields) {
    	return '<structure></structure>';
    }
    
    public function edit($data,$field,$newdata,$olddata,$object) {
		return $data;
	}

}
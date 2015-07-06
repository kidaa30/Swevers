<?

class Model {

	protected $resultset = false;

	public function __construct($translate) {
		foreach ($translate as $translate_to => $translate_from) {
			if (!isset($this->$translate_to) && isset($this->$translate_from)) $this->$translate_to = $this->$translate_from;
		}
	}
	
	public function to_array() {
		$array = (array)$this;
		foreach ($array as $key => $value) {
			if (!is_string($value) && !is_int($value) && !is_null($value)) unset($array[$key]);
		}
		return $array;
	}
	
	public function __get($property) {
		if ($this->resultset && isset($this->id)) {
			$result = $this->resultset->_load_child($property,$this->id);
			if (is_object($result) || is_null($result)) return $result;
		}
		ini_set('memory_limit','128M');
		echo '<!--';
		var_dump(debug_backtrace());
		echo '-->';
		trigger_error('Undefined property: '.$property);
		return NULL;
	}
	
	public function parent() {
		$fieldname = $this->resultset->_get_parent_id_fieldname();
		if ($fieldname) return $this->resultset->_get_parent_with_id($this->$fieldname);
		return NULL;
	}
	
	public function _set_resultset($resultset) { $this->resultset = $resultset; }
	
	public function make_serializable() {
		$this->_set_resultset(false);
	}

}
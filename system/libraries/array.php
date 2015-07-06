<? 
/*
 * Inserts a new key/value before the key in the array.
 *
 * @param $key
 *   The key to insert before.
 * @param $array
 *   An array to insert in to.
 * @param $new_key
 *   The key to insert.
 * @param $new_value
 *   An value to insert.
 *
 * @return
 *   The new array if the key exists, FALSE otherwise.
 *
 * @see array_insert_after()
 */
function array_insert_before($key, array &$array, $new_key, $new_value) {
	if (array_key_exists($key, $array)) {
		$new = array();
		foreach ($array as $k => $value) {
			if ($k === $key) $new[$new_key] = $new_value;
			$new[$k] = $value;
		}
		return $new;
	}
	return FALSE;
}
 
/*
 * Inserts a new key/value after the key in the array.
 *
 * @param $key
 *   The key to insert after.
 * @param $array
 *   An array to insert in to.
 * @param $new_key
 *   The key to insert.
 * @param $new_value
 *   An value to insert.
 *
 * @return
 *   The new array if the key exists, FALSE otherwise.
 *
 * @see array_insert_before()
 */
function array_insert_after($key, array &$array, $new_key, $new_value) {
	if (array_key_exists($key, $array)) {
		$new = array();
		foreach ($array as $k => $value) {
			$new[$k] = $value;
			if ($k === $key) $new[$new_key] = $new_value;
		}
		return $new;
	}
	return FALSE;
}

function sort_by_value_for_key(&$array,$key) {
	
	uasort($array, function($a, $b) use ($key) {
    	return strnatcasecmp($a[$key], $b[$key]);
    });
	
}
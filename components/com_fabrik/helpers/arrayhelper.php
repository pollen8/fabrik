<?php

class FArrayHelper extends JArrayHelper
{

	/**
	 * update the data that gets posted via the form and stored by the form
	 * model. Used in elements to modify posted data see fabrikfileupload
	 * @param string $key (in key.dot.format to set a recursive array
	 * @param string $val
	 * @return null
	 */
	function setValue(&$array, $key, $val)
	{

		if (strstr($key, '.')) {

			$nodes = explode('.', $key);
			$count = count($nodes);
			$pathNodes = $count - 1;
			if ($pathNodes < 0) {
				$pathNodes = 0;
			}
			$ns = $array;
			for ($i = 0; $i <= $pathNodes; $i ++)
			{
				// If any node along the registry path does not exist, create it
				//if (!isset($this->_formData[$nodes[$i]])) { //this messed up for joined data
				if (!isset($ns[$nodes[$i]])) {
					$ns[$nodes[$i]] = array();
				}
				$ns = $ns[$nodes[$i]];
			}
			$ns = $val;

			$ns = $array;
			for ($i = 0; $i <= $pathNodes; $i ++)
			{
				// If any node along the registry path does not exist, create it
				//if (!isset($this->_formData[$nodes[$i]])) { //this messed up for joined data
				if (!isset($ns[$nodes[$i]])) {
					$ns[$nodes[$i]] = array();
				}
				$ns = $ns[$nodes[$i]];
			}
			$ns = $val;
		} else {
			$array[$key] = $val;
		}
	}

	/**
	 * Utility function to map an array to a stdClass object.
	 *
	 * @static
	 * @param	array	$array		The array to map.
	 * @param	string	$calss 		Name of the class to create
	 * @param bol recurse into each value and set any arrays to objects
	 * @return	object	The object mapped from the given array
	 * @since	1.5
	 */
	static function toObject(&$array, $class = 'stdClass', $recurse = true)
	{
		$obj = null;
		if (is_array($array))
		{
			$obj = new $class();
			foreach ($array as $k => $v)
			{
				if (is_array($v) && $recurse) {
					$obj->$k = JArrayHelper::toObject($v, $class);
				} else {
					$obj->$k = $v;
				}
			}
		}
		return $obj;
	}

	function array_key_diff($ar1, $ar2) {  // , $ar3, $ar4, ...
		// returns copy of array $ar1 with those entries removed
		// whose keys appear as keys in any of the other function args
		$aSubtrahends = array_slice(func_get_args(),1);
		foreach ($ar1 as $key => $val)
		foreach ($aSubtrahends as $aSubtrahend)
		if (array_key_exists($key, $aSubtrahend))
		unset ($ar1[$key]);
		return $ar1;
	}

	/**
	 * filters array of objects removing those when key does not match
	 * the value
	 * @param array of objects - passed by ref
	 * @param string key to search on
	 * @param string value of key to keep from array
	 * @return unknown_type
	 */
	function filter(&$array, $key, $value)
	{
		for ($i = count($array) -1; $i >= 0; $i --)
		{
			if ($array[$i]->$key !== $value) {
				unset($array[$i]);
			}
		}
	}

	/**
	 * get the first object in an array whose key = value
	 * @param array of objects
	 * @param string $key to search on
	 * @param string $value to search on
	 */

	function get($array, $key, $value)
	{
		for ($i = count($array) -1; $i >= 0; $i --)
		{
			if ($array[$i]->$key == $value) {
				return $array[$i];
			}
		}
		return false;
	}

	function extract($array, $key)
	{
		$return = array();
		foreach ($array as $object) {
			$return[] = $object->$key;
		}
		return $return;
	}

	/**
	 * @since	3.0.6
	 *
	 * Returns first key in an array, used if we aren't sure if array is assoc or
	 * not, and just want the first row.
	 *
	 * @param array $array
	 */

	function firstKey($array)
	{
		reset($array);
		return key($array);
	}

}
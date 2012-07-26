<?php

class Arr
{

	/**
	 * Remove keys from input array that are not in the whitelist
	 *
	 *     // Get the values "username", "password" from $_POST
	 *     $auth = Arr::filterKeys($_POST, array('username', 'password'));
	 * or
	 *     $auth = Arr::filterKeys($_POST, 'username', 'password');
	 *
	 * @param array $array
	 * @param mixed $keyWhitelist array or any number of strings as parameters
	 *
	 * @return array
	 */
	public static function filterKeys( array $array, $keyWhitelist )
	{
		if ( !is_array( $keyWhitelist ) ) {
			$keyWhitelist = func_get_args();
			unset( $keyWhitelist[0] );
		}

		foreach ( $array as $key => $_ ) {

			if ( !in_array( $key, $keyWhitelist ) ) {
				unset( $array[$key] );
			}

		}

		return $array;
	}

	/**
	 * Remove keys from input array that are in the blacklist
	 *
	 * instead of
	 *      $_ = $importedRow['id'];
	 *         unset( $importedRow['id'] );
	 *         $idMap[$_] = mysql::addRow( $table, $importedRow );
	 *
	 * use:
	 *      $idMap[$importedRow['id']] = mysql::addRow( $table, Arr::blacklistKeys( $importedRow, 'id' ) );
	 *
	 * @param array        $array
	 * @param array|string $keyBlacklist
	 * @param ...
	 *
	 * @return array
	 */

	public static function blacklistKeys( array $array, $keyBlacklist )
	{
		if ( !is_array( $keyBlacklist ) ) {
			$keyBlacklist = func_get_args();
			unset( $keyBlacklist[0] );
		}

		foreach ( $array as $key => $_ ) {

			if ( in_array( $key, $keyBlacklist ) ) {
				unset( $array[$key] );
			}

		}

		return $array;
	}

	/**
	 * Get the needed keys from the array - adding those that are not present
	 *
	 *     // Get the values "username", "password" from $_POST
	 *     $auth = Arr::extract($_POST, array('username', 'password'));
	 *
	 * @param array $array
	 * @param mixed $keys array or any number of strings as parameters
	 *
	 * @return array
	 */
	public static function extract( array $array, $keys )
	{
		if ( !is_array( $keys ) ) {
			$keys = func_get_args();
			unset( $keys[0] );
		}

		$found = array();
		foreach ( $keys as $key ) {
			$found[$key] = isset( $array[$key] ) ? $array[$key] : NULL;
		}

		return $found;
	}


	/**
	 * Get first array member or key
	 *
	 * @param array $array
	 * @param bool  $getKey
	 *
	 * @return mixed
	 */
	public static function first( $array, $getKey = FALSE )
	{
		if ( $getKey ) {
			$array = array_keys( $array );
		}
		return is_array( $array ) ? reset( $array ) : NULL;
	}

	public static function last( $array, $getKey = FALSE )
	{
		if ( $getKey ) {
			$array = array_keys( $array );
		}
		return is_array( $array ) ? end( $array ) : NULL;
	}


	/**
	 * shorthand for
	 *
	 * $value = isset($array[$key])? $array[$key] : $default;
	 *
	 * also accepts null instead of array
	 *
	 * @param null|array  $array
	 * @param int|string  $key
	 * @param mixed       $default
	 *
	 * @return mixed
	 */
	public static function get( $array, $key, $default = NULL )
	{
		return is_array( $array ) && array_key_exists( $key, $array ) ? $array[$key] : $default;
	}

	/**
	 * Retrieve a single key from an array. If the key does not exist in the array, NULL will be returned. Supports
	 * nested keys, pass as much keys as needed, used to avoid multiple isset checks for fear of E_NOTICE
	 *
	 * [!] difference from Arr::get(): suppports multiple keys, but does not support a default value
	 *
	 *     // Get the value "sorting" from $_GET, if it exists
	 *     $sorting = Arr::path($_GET, 'sorting');
	 *
	 *     // Get the value $_POST['data']['username']
	 *     $username = Arr::path($_POST, 'data', 'username');
	 * OR
	 *     // Get the value $_POST['data']['username']
	 *     $username = Arr::path($_POST, array('data', 'username'));
	 *
	 *
	 *     $a['a']['b']['c'] = 'd';
	 *        Arr::path($a,array('a','b'));
	 *     > array('c'=>'d')
	 *
	 * @static
	 *
	 * @param array        $array
	 * @param string|array $key
	 * @param string       $otherKeys more keys as needed
	 *
	 * @return mixed
	 */
	public static function path( $array, $key, $otherKeys = null )
	{
		if ( is_array( $key ) ) {
			// take the first array member as key and leave the others for further processing
			$_         = array_shift( $key );
			$otherKeys = $key;
			$key       = $_;
		}


		if ( !empty( $otherKeys ) ) {
			$argv = func_get_args();
			if ( count( $argv ) > 3 ) { // may be true first time, not in recursion
				if ( !is_array( $otherKeys ) ) {
					$otherKeys = array( $otherKeys );
				}
				unset( $argv[0], $argv[1], $argv[2] );

				$otherKeys += $argv;
			}

			if ( is_array( $otherKeys ) ) {
				$nextKey = array_shift( $otherKeys );

			} else {
				$nextKey   = $otherKeys;
				$otherKeys = null;
			}

			return isset( $array[$key] ) ? self::path( $array[$key], $nextKey, $otherKeys ) : null;
		}
		return isset( $array[$key] ) ? $array[$key] : null;
	}

	/**
	 * same as path, but unsets the value from the array
	 *
	 * @static
	 *
	 * @param      $array
	 * @param      $key
	 * @param null $otherKeys
	 *
	 * @return mixed
	 */
	public static function popPath( &$array, $key, $otherKeys = null )
	{
		if ( is_array( $key ) ) {
			// take the first array member as key and leave the others for further processing
			$_         = array_shift( $key );
			$otherKeys = $key;
			$key       = $_;
		}


		if ( !empty( $otherKeys ) ) {
			$argv = func_get_args();
			if ( count( $argv ) > 3 ) { // may be true first time, not in recursion
				if ( !is_array( $otherKeys ) ) {
					$otherKeys = array( $otherKeys );
				}
				unset( $argv[0], $argv[1], $argv[2] );

				$otherKeys += $argv;
			}

			if ( is_array( $otherKeys ) ) {
				$nextKey = array_shift( $otherKeys );

			} else {
				$nextKey   = $otherKeys;
				$otherKeys = null;
			}

			return isset( $array[$key] ) ? self::popPath( $array[$key], $nextKey, $otherKeys ) : null;
		}
		$ret = isset( $array[$key] ) ? $array[$key] : null;
		unset( $array[$key] );

		return $ret;
	}

	/**
	 * removes leafless nodes
	 *
	 * @static
	 *
	 * @param $array
	 *
	 * @return array
	 */
	public static function clearEmpty( $array )
	{
		if ( empty( $array ) ) return array();

		foreach ( $array as $key => $val ) {
			if ( !is_array( $val ) ) continue;

			if ( empty( $val ) ) {
				unset( $array[$key] );
			} else {
				$cleanVal = self::clearEmpty( $val );

				if ( empty( $cleanVal ) ) {
					unset( $array[$key] );
				} else {
					$array[$key] = $cleanVal;
				}
			}
		}


		return $array;
	}

	public static function setPath( & $array, $keys, $value )
	{
		// Set current $array to inner-most array path
		while ( count( $keys ) > 1 ) {
			$key = array_shift( $keys );

			if ( ctype_digit( $key ) ) {
				// Make the key an integer
				$key = (int)$key;
			}

			if ( !isset( $array[$key] ) ) {
				$array[$key] = array();
			}

			$array = & $array[$key];
		}

		// Set key on inner-most array
		$array[array_shift( $keys )] = $value;
	}

	/**
	 * Convert a multi-dimensional array into a single-dimensional array.
	 *
	 *     $array = array('set' => array('one' => 'something'), 'two' => 'other');
	 *
	 *     // Flatten the array
	 *     $array = Arr::flatten($array);
	 *
	 *     // The array will now be
	 *     array('one' => 'something', 'two' => 'other');
	 *
	 * [!!] The keys of array values will be discarded.
	 *
	 * @param   array   array to flatten
	 *
	 * @return  array
	 */
	public static function flatten( $array )
	{
		$flat = array();
		foreach ( $array as $key => $value ) {
			if ( is_array( $value ) ) {
				$flat += self::flatten( $value );
			}
			else {
				$flat[$key] = $value;
			}
		}
		return $flat;
	}

	/**
	 * Adds a value to the beginning of an associative array.
	 *
	 *     // Add an empty value to the start of a select list
	 *     Arr::unshift($array, 'none', 'Select a value');
	 *
	 * @param   array  $array array to modify
	 * @param   string $key array key name
	 * @param   mixed  $val array value
	 *
	 * @return  array
	 */
	public static function unshift( array & $array, $key, $val )
	{
		$array       = array_reverse( $array, TRUE );
		$array[$key] = $val;
		$array       = array_reverse( $array, TRUE );

		return $array;
	}

	/**
	 * Tests if an array is associative or not.
	 *
	 *     // Returns TRUE
	 *     Arr::is_assoc(array('username' => 'john.doe'));
	 *
	 *     // Returns FALSE
	 *     Arr::is_assoc('foo', 'bar');
	 *
	 * @param   array   array to check
	 *
	 * @return  boolean
	 */
	public static function isAssoc( array $array )
	{
		// Keys of the array
		$keys = array_keys( $array );

		// If the array keys of the keys match the keys, then the array must
		// not be associative (e.g. the keys array looked like {0:0, 1:1...}).
		return array_keys( $keys ) !== $keys;
	}


	/**
	 * convert to an XML document.
	 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
	 *
	 * @param array            $data
	 * @param string           $rootNodeName
	 * @param string           $numericName name given to numeric nodes
	 * @param SimpleXMLElement $xml
	 *
	 * @return string
	 */
	public static function toXml( $data, $rootNodeName = 'data', $numericName = 'unknownNode', $xml = null )
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if ( ini_get( 'zend.ze1_compatibility_mode' ) == 1 ) {
			ini_set( 'zend.ze1_compatibility_mode', 0 );
		}

		if ( $xml == null ) {
			$xml = simplexml_load_string( "<?xml version='1.0' encoding='utf-8'?><$rootNodeName />" );
		}

		foreach ( $data as $key => $value ) {
			if ( is_numeric( $key ) ) {
				$key = $numericName;
			}

			// replace anything not alpha numeric
			$key = preg_replace( '/[^a-z0-9\-\_\.\:]/i', '', $key );

			if ( is_array( $value ) ) {
				$node = $xml->addChild( $key );
				// recrusive call.
				self::toXml( $value, $rootNodeName, $numericName, $node );
			} else {
				$tmp    = $xml->addChild( $key );
				$tmp[0] = $value;
			}

		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}

	public static function childCount( $arr )
	{
		$count = 0;
		if ( is_array( $arr ) ) {
			foreach ( $arr as $v ) {
				$count += self::childCount( $v );
			}
		} else {
			$count++;
		}

		return $count;
	}


	/**
	 * Unserializes an XML string, returning a multi-dimensional associative array, optionally runs a callback on
	 * all non-array data
	 *
	 * Notes:
	 *  Root XML tags are stripped
	 *  Due to its recursive nature, unserialize_xml() will also support SimpleXMLElement objects and arrays as input
	 *  Uses simplexml_load_string() for XML parsing, see SimpleXML documentation for more info
	 *
	 * @static
	 *
	 * @param mixed    $input
	 * @param callback $callback
	 * @param bool     $_recurse used internally, do not pass any value
	 *
	 * @return array|false Returns false on all failure
	 */
	public static function fromXml( $input, $callback = NULL, $_recurse = FALSE )
	{
		// Get input, loading an xml string with simplexml if its the top level of recursion
		$data = ( ( !$_recurse ) && is_string( $input ) ) ? simplexml_load_string( $input ) : $input;

		// Convert SimpleXMLElements to array
		if ( $data instanceof SimpleXMLElement ) {
			$data = (array)$data;
		}

		// Recurse into arrays
		if ( is_array( $data ) ) foreach ( $data as &$item ) {
			$item = Arr::fromXml( $item, $callback, TRUE );
		}

		// Run callback and return
		return ( !is_array( $data ) && is_callable( $callback ) ) ? call_user_func( $callback, $data ) : $data;
	}

	public static function insertAtIndex( $array, $newElement, $index )
	{
		/*** get the start of the array ***/
		$start = array_slice( $array, 0, $index );
		/*** get the end of the array ***/
		$end = array_slice( $array, $index );
		/*** add the new element to the array ***/
		$start[] = $newElement;
		/*** glue them back together and return ***/
		return array_merge( $start, $end );
	}


	/**
	 * groups the values of an array by the specified pattern. Note that col=>* will NOT unset col from every row, it
	 * will be present in key and values
	 *
	 * @static
	 * @throws ErrException
	 *
	 * @param array  $array
	 * @param string $format
	 *  examples:
	 *      'key=>key2', 'key=>key2;key3', 'key=>*', 'key[]=>key2', 'key[key2]=>key3', 'key[key2][key3]=>key4',
	 *      'key[key2][key3][]=>*'
	 * @param bool   $keepKey
	 *
	 * @return array
	 */
	public static function makeHierarchy( array $array, $format, $keepKey = false )
	{
		preg_match( '#([^=\[]+)((?:\[(?:[^\]]*)\])*)=>(.+)#', $format, $matches );

		try {
			list( , $key, $braces, $columns ) = $matches;
		} catch ( Exception $e ) {
			throw new ErrException( 'Invalid format pattern', get_defined_vars() );
		}

		if ( $braces ) {
			preg_match_all( '#\[([^\]]*)\]#', $braces, $matches );
			$nestedKeys = $matches[1];
		}


		$values = $columns === '*' ? $columns : explode( ';', $columns );

		$hasMultipleValues = $values === '*' || isset( $values[1] );

		$rows = array();
		foreach ( $array as $row ) {
			if ( $hasMultipleValues ) {

				if ( $values === '*' ) {
					$result = $row;
				} else {
					foreach ( $values as $v ) {
						$result[$v] = $row[$v];
					}
				}

			} else {
				$result = $row[$columns];
			}

			if ( $braces ) {
				isset( $rows[$row[$key]] ) or $rows[$row[$key]] = array();

				$cont = &$rows[$row[$key]];

				foreach ( $nestedKeys as $nestedKey ) {

					if ( $values === '*' ) {
						if ( !$keepKey ) {
							unset( $result[$nestedKey] );
						}
					}

					if ( $nestedKey ) {
						isset( $cont[$row[$nestedKey]] ) or $cont[$row[$nestedKey]] = array();
						$cont = &$cont[$row[$nestedKey]];
					} else {
						$cont[] = array();
						$cont   = &$cont[Arr::last( $cont, TRUE )];
					}
				}


				$cont = $result;
			} else {
				$rows[$row[$key]] = $result;

			}


		}
		return $rows;

	}

	/**
	 * Recursive version of [array_map](http://php.net/array_map), applies the
	 * same callback to all elements in an array, including sub-arrays.
	 *
	 *     // Apply "strip_tags" to every element in the array
	 *     $array = Arr::map('strip_tags', $array);
	 *
	 * [!!] Unlike `array_map`, this method requires a callback and will only map
	 * a single array.
	 *
	 * @param mixed $callback callback applied to every element in the array
	 * @param array $array  array to map
	 * @param bool  $applyToKeys
	 *
	 * @return  array
	 */
	public static function map( $callback, $array, $applyToKeys = FALSE )
	{
		if ( $applyToKeys ) {
			$newArr = array();
		}
		foreach ( $array as $key => $val ) {
			if ( is_array( $val ) ) {
				if ( $applyToKeys ) {
					$newArr[call_user_func( $callback, $key )] = self::map( $callback, $val, $applyToKeys );
				} else {
					$array[$key] = self::map( $callback, $val );
				}
			}
			else {
				if ( $applyToKeys ) {
					$newArr[call_user_func( $callback, $key )] = $val;
				} else {
					$array[$key] = call_user_func( $callback, $val );
				}
			}
		}
		if ( $applyToKeys ) {
			return $newArr;
		} else {
			return $array;
		}
	}


	/**
	 * Merges one or more arrays recursively and preserves all keys.
	 * Note that this does not work the same as [array_merge_recursive](http://php.net/array_merge_recursive)!
	 *
	 *     $john = array('name' => 'john', 'children' => array('fred', 'paul', 'sally', 'jane'));
	 *     $mary = array('name' => 'mary', 'children' => array('jane'));
	 *
	 *     // John and Mary are married, merge them together
	 *     $john = Arr::merge($john, $mary);
	 *
	 *     // The output of $john will now be:
	 *     array('name' => 'mary', 'children' => array('fred', 'paul', 'sally', 'jane'))
	 *
	 * @param   array $a1 initial array
	 * @param   array $a2 array to merge, accepts any number of arrays
	 *
	 * @return  array
	 */
	public static function merge( array $a1, array $a2 )
	{
		$result = array();
		for ( $i = 0, $total = func_num_args(); $i < $total; $i++ ) {
			// Get the next array
			$arr = func_get_arg( $i );

			// Is the array associative?
			$assoc = Arr::isAssoc( $arr );

			foreach ( $arr as $key => $val ) {
				if ( isset( $result[$key] ) ) {
					if ( is_array( $val ) AND is_array( $result[$key] ) ) {
						if ( Arr::isAssoc( $val ) ) {
							// Associative arrays are merged recursively
							$result[$key] = Arr::merge( $result[$key], $val );
						}
						else {
							// Find the values that are not already present
							$diff = array_diff( $val, $result[$key] );

							// Indexed arrays are merged to prevent duplicates
							$result[$key] = array_merge( $result[$key], $diff );
						}
					}
					else {
						if ( $assoc ) {
							// Associative values are replaced
							$result[$key] = $val;
						}
						elseif ( !in_array( $val, $result, TRUE ) ) {
							// Indexed values are added only if they do not yet exist
							$result[] = $val;
						}
					}
				}
				else {
					// New values are added
					$result[$key] = $val;
				}
			}
		}

		return $result;
	}


	public static function renameKey( array &$arr, $oldKey, $newKey )
	{
		$offset = self::searchKey( $arr, $oldKey );
		if ( $offset !== FALSE ) {
			$keys          = array_keys( $arr );
			$keys[$offset] = $newKey;
			$arr           = array_combine( $keys, $arr );
		}
	}

	public static function searchKey( $arr, $key )
	{
		$foo = array( $key => NULL );
		return array_search( key( $foo ), array_keys( $arr ), TRUE );
	}

	/**
	 * array_unique for multi-arrays
	 *
	 * @param $array
	 *
	 * @return array
	 */
	public static function unique( $array )
	{
		return array_map( "unserialize", array_unique( array_map( "serialize", $array ) ) );
	}
}

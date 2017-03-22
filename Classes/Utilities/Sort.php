<?php
namespace TableMigrations\Utilities;

class Sort{


	/**
	 * Sort an array by a subfield
	 * 
	 * @param  array $data     array
	 * @param  string $field   name
	 * @param  string $order   ASC / DESC
	 * @return array
	 */
	public static function byField( $data, $field, $order = null ){

		//return false if the data is empty, or not of the valid type:
		if( empty( $data ) || ( !is_array( $data ) && !is_object( $data ) ) )
			return false;

		$key = array_keys( $data );
		$key = $key[0];
		$notationStart = "['";
		$notationEnd = "']";

		if( is_object( $data[$key] ) ){
			$notationStart = '->';
			$notationEnd = '';
		}
	
		if( $order == null || $order == 'ASC' ){
	  		$code = "return strnatcmp(\$a".$notationStart.$field.$notationEnd.", \$b".$notationStart.$field.$notationEnd.");";
	  	}else if( $order == 'DESC' ){
	  		$code = "return strnatcmp(\$b".$notationStart.$field.$notationEnd.", \$a".$notationStart.$field.$notationEnd.");";
		}
	
		uasort( $data, create_function( '$a,$b', $code ) );
		return $data;
	}


	/**
	 * Prepend all values in an array
	 * 
	 * @param  array $array
	 * @param  string $prepend
	 * @return array
	 */
	public static function prependValues( $array, $prepend = '' ){

		return preg_filter('/^/', $prepend, $array );
	}


	/**
	 * Append all values in an array
	 * 
	 * @param  array $array
	 * @param  string $append
	 * @return array
	 */
	public static function appendValues( $array, $append = '' ){

		return preg_filter('/$/', $append, $array );
	}

	/**
	 * Flatten a multi dimensional array
	 * 
	 * @param  Array $array
	 * 
	 * @return Array
	 */
	public static function flatten( $array )
	{
	    $result = array();

	    foreach( $array as $key => $value ) {
	        
	        if( is_array( $value ) ) {
	        	
	            $result = $result + static::flatten( $value );
	        
	        }else{
	        	
	            $result[ $key ] = $value;
	        
	        }
	    }

	    return $result;
	}


	/**
	 * Get the first item in an array
	 * 
	 * @param  array $array
	 * @return mixed
	 */
	public static function first( array $array ){
		return reset( $array );

	}

	/**
	 * Returns the first key in an array.
	 *
	 * @param  array $array
	 * @return int|string
	 */
	public static function firstKey( array $array ){
		reset( $array );
		return key( $array );
	
	}

	/**
	 * Returns the last element in an array.
	 *
	 * @param  array $array
	 * @return mixed
	 */
	public static function last( array $array ){
	    return end($array);

	}

	/**
	 * Returns the last key in an array.
	 *
	 * @param  array $array
	 * @return int|string
	 */
	public static function lastKey( array $array ){
	    end($array);
	    return key($array);
	
	}

	/**
	 * Accepts an array, and returns an array of values from that array as
	 * specified by $field. For example, if the array is full of objects
	 * and you call Sort::array_pluck($array, 'name'), the function will
	 * return an array of values from $array[]->name.
	 *
	 * @param  array   $array            An array
	 * @param  string  $field            The field to get values from
	 * @param  boolean $preserve_keys    Whether or not to preserve the
	 *                                   array keys
	 * @param  boolean $remove_nomatches If the field doesn't appear to be set,
	 *                                   remove it from the array
	 * @return array
	 */
	public static function pluck( array $array, $field, $preserve_keys = true, $remove_nomatches = true ){
	    $new_list = array();

	    foreach ($array as $key => $value) {
	        if (is_object($value)) {
	            if (isset($value->{$field})) {
	                if ($preserve_keys) {
	                    $new_list[$key] = $value->{$field};
	                } else {
	                    $new_list[] = $value->{$field};
	                }
	            } elseif (!$remove_nomatches) {
	                $new_list[$key] = $value;
	            }
	        } else {
	            if (isset($value[$field])) {
	                if ($preserve_keys) {
	                    $new_list[$key] = $value[$field];
	                } else {
	                    $new_list[] = $value[$field];
	                }
	            } elseif (!$remove_nomatches) {
	                $new_list[$key] = $value;
	            }
	        }
	    }

	    return $new_list;
	}
}
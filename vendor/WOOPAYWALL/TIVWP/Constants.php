<?php
/**
 * Constants
 *
 * @since 1.9.0
 *
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP;

/**
 * Class Constants
 *
 * @since 1.9.0
 */
class Constants {

	/**
	 * Checks if the constant was defined with define( 'name', 'value ).
	 *
	 * @since 1.9.0
	 *
	 * @param string $name The name of the constant.
	 *
	 * @return bool
	 */
	public static function is_defined( $name ) {
		if ( ! is_string( $name ) ) {
			return false;
		}

		return defined( $name );
	}

	/**
	 * Attempts to get the constant with the constant() function.
	 * If that hasn't been set, returns default.
	 *
	 * @param string $name The name of the constant.
	 *
	 * @return mixed
	 */
	public static function get_constant( $name, $default = false ) {

		return self::is_defined( $name ) ? constant( $name ) : $default;
	}

	/**
	 * Checks if a "constant" has been set and has the value of true.
	 *
	 * @param string $name The name of the constant.
	 *
	 * @return bool
	 */
	public static function is_true( $name ) {
		return self::is_defined( $name ) && self::get_constant( $name );
	}
}

<?php
/**
 * Expiration Units.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Expiration;

/**
 * Class Units
 *
 * @package WOOPAYWALL\Expiration
 */
class Units {

	/**
	 * Used to force plural form.
	 *
	 * @var int
	 */
	const FORCE_PLURAL = 10;

	/**
	 * Get all units (lazy-load).
	 *
	 * @return array
	 */
	public static function get_all() {

		/**
		 * Expiration units.
		 *
		 * @var array $all_units
		 */
		static $all_units;

		/**
		 * Setup units names, translatable.
		 *
		 * 'months' => \_nx_noop( 'month', 'months', 'Expiration Units', 'paywall-for-woocommerce' ),
		 */
		if ( ! $all_units ) {
			$all_units = array(
				'hours' => \_nx_noop( 'hour', 'hours', 'Expiration Units', 'paywall-for-woocommerce' ),
				'days'  => \_nx_noop( 'day', 'days', 'Expiration Units', 'paywall-for-woocommerce' ),
			);
		}

		return $all_units;
	}

	/**
	 * Return translated units name.
	 *
	 * @param string $units The units code.
	 * @param int    $count To translate as singular or plural.
	 *
	 * @return string
	 */
	public static function get_units_name( $units, $count = 1 ) {
		$all_units = self::get_all();

		return \translate_nooped_plural( $all_units[ $units ], $count );
	}

	/**
	 * Returns the units in the form key => translated name.
	 *
	 * @param int $count To translate as singular or plural.
	 *
	 * @return array
	 */
	public static function get_units( $count = 1 ) {

		$all_units = self::get_all();

		array_walk( $all_units,
			function ( &$item ) use ( $count ) {
				$item = \translate_nooped_plural( $item, $count );
			}
		);

		return $all_units;
	}
}

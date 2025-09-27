<?php
/**
 * Handle data for the current customers session
 *
 * @since   1.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend;

/**
 * Class Session
 */
class Session {

	/**
	 * Session key: product IDs.
	 *
	 * @var string
	 */
	const KEY_PURCHASED = 'paywall_purchased';

	/**
	 * Session key: order IDs.
	 *
	 * @since 2.1.0
	 *
	 * @var string
	 */
	const KEY_ORDERS = 'paywall_orders';

	/**
	 * Add product ID to the list of purchased products.
	 *
	 * @param int $product_id Product ID.
	 */
	public static function add_to_purchased( $product_id ) {
		self::append( self::KEY_PURCHASED, $product_id );
	}

	/**
	 * Add order ID to the list of orders made in this session.
	 *
	 * @since 2.1.0
	 *
	 * @param int $order_id Order ID.
	 */
	public static function add_to_orders( $order_id ) {
		self::append( self::KEY_ORDERS, $order_id );
	}

	/**
	 * Append to session array.
	 *
	 * @since 2.1.0
	 * @since 3.0.0 Fix: do not append if already there.
	 *
	 * @param string $key   Key.
	 * @param mixed  $value Value.
	 */
	public static function append( $key, $value ) {

		if ( ! \WC()->session ) {
			return;
		}

		$values = \WC()->session->get( $key, array() );

		if ( ! in_array( $value, $values, true ) ) {
			$values[] = $value;

			\WC()->session->set( $key, $values );
		}
	}

	/**
	 * Return the list of orders IDs made in this session.
	 *
	 * @since 2.1.0
	 * @since 3.0.5 Fix the `Call to a member function get() on null` error when there is no session.
	 * @return int[]
	 */
	public static function get_order_ids() {

		if ( ! \WC()->session ) {
			return array();
		}

		return \WC()->session->get( self::KEY_ORDERS, array() );
	}

	/**
	 * Check if product ID is in the list of purchased products.
	 *
	 * @since   1.0.0
	 * @since   3.1.0 Added filter allowing 3rd party to add/modify the Product ID to check.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public static function is_purchased( $product_id ) {

		if ( ! \WC()->session ) {
			return false;
		}

		$purchased_product_ids = \WC()->session->get( self::KEY_PURCHASED );

		if ( ! is_array( $purchased_product_ids ) || count( $purchased_product_ids ) < 1 ) {
			return false;
		}

		/**
		 * Filter to modify the Product ID to check.
		 * Example: WPML would return IDs of all translations.
		 *
		 * @since 3.1.0
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return int[] Array of Product IDs.
		 */
		$ids_to_check = (array) \apply_filters( 'woopaywall_ids_to_check', $product_id );

		if ( array_intersect( $ids_to_check, $purchased_product_ids ) ) {
			return true;
		}

		return false;
	}
}

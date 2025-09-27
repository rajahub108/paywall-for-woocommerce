<?php
/**
 * Paywall Order information.
 *
 * @since 3.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Order;

use WOOPAYWALL\Dependencies\TIVWP\WC\Order\Info as TIVWP_WC_Order_Info;
use WOOPAYWALL\Dependencies\TIVWP\WC\DTLocal;
use WOOPAYWALL\Expiration\ExpireAfter;
use WOOPAYWALL\Log;
use WOOPAYWALL\MetaSet\MetaSetPaywall;

/**
 * Class Order\Info
 *
 * @since 3.0.0
 */
class Info extends TIVWP_WC_Order_Info {

	/**
	 * Order key parameter name in URLs.
	 *
	 * @type string
	 * @since 3.7.0
	 */
	const ORDER_KEY_PARAMETER = 'woopaywall_order_key';

	/**
	 * Get ExpireAfter of the product in this order.
	 *
	 * @since 1.5.0
	 * @since 3.9.0 HPOS compatibility: use order->get_meta().
	 *
	 * @param \WC_Product_Paywall $product Product instance.
	 *
	 * @return ExpireAfter
	 */
	public function get_expire_after( $product ) {

		$meta_expire_after = $this->order->get_meta( Meta::make_meta_key( $product->get_id() ) );

		if ( ExpireAfter::is_valid_data( $meta_expire_after ) ) {
			// Expiration of this product is stored in the order.
			$expire_after = new ExpireAfter( $meta_expire_after );
		} else {
			// Return the default expiration for this product.
			$expire_after = $product->get_expire_after();
		}

		return $expire_after;
	}

	/**
	 * Order expiration timestamp in the local timezone.
	 *
	 * @since 3.3.0
	 * @since 3.9.0 HPOS compatibility: use order->get_meta().
	 *
	 * @return DTLocal|null
	 */
	public function get_expires_on() {

		$expires_on = null;

		$meta_value = $this->order->get_meta( MetaSetPaywall::META_KEYS['EXPIRE_ON']['ID'] );

		if ( $meta_value ) {
			try {
				// This meta is saved in the local time zone, not UTC,
				// so we need to pass the zone to the constructor.
				$expires_on = new DTLocal( $meta_value, new \DateTimeZone( \wc_timezone_string() ) );
			} catch ( \Exception $exception ) {
				Log::error( $exception );
			}
		}

		return $expires_on;
	}

	/**
	 * Returns true if order has expiration override meta, and it is in the past.
	 *
	 * @since 3.3.0
	 *
	 * @return bool
	 */
	public function is_expired() {

		$expires_on = $this->get_expires_on();
		if ( ! $expires_on ) {
			return false;
		}

		return $expires_on->is_in_the_past();
	}

	/**
	 * Returns true if the order has a specific product.
	 *
	 * @since 3.7.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public function has_product( $product_id ) {

		$order_items = $this->get_items();
		foreach ( $order_items as $order_item ) {
			if ( $product_id === $order_item->get_product_id() ) {
				return true;
			}
		}

		return false;
	}
}

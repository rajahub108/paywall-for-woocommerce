<?php
/**
 * Order metas.
 *
 * @since 2.0.0
 *
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Order;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\Log;
use WOOPAYWALL\Expiration;

/**
 * Class Meta
 *
 * @package WOOMC\Order
 */
class Meta extends Hookable {

	/**
	 * Meta keys prefix.
	 *
	 * @var string
	 */
	const PREFIX = '_woopaywall_';

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_action(
			'woocommerce_checkout_update_order_meta',
			array( $this, 'action__woocommerce_checkout_update_order_meta' )
		);
	}

	/**
	 * Generate meta key for given product ID.
	 *
	 * @param int $product_id The product ID.
	 *
	 * @return string
	 */
	public static function make_meta_key( $product_id ) {
		return self::PREFIX . Expiration\GlobalSettings::ORDER_META_KEY . '_' . $product_id;
	}

	/**
	 * Add order metas at checkout.
	 *
	 * @since    2.0.0
	 * @since    3.9.0 HPOS compatibility: use order->update_meta_data().
	 *
	 * @param int $order_id The order ID.
	 *
	 * @internal action.
	 */
	public function action__woocommerce_checkout_update_order_meta( $order_id ) {

		try {
			$order_info  = new Info( $order_id );
			$order_items = $order_info->get_items();
			foreach ( $order_items as $order_item ) {
				$product_id = $order_item->get_product_id();
				$product    = \WC_Product_Paywall::wc_get_product( $product_id );

				if ( $product && $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) ) {
					$order_info->getOrder()->update_meta_data(
						self::make_meta_key( $product_id ),
						// Store only the data, not serialized object in meta.
						$product->get_expire_after()->getData()
					);
				}
			}
		} catch ( \Exception $exception ) {
			Log::error( $exception );
		}
	}
}

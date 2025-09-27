<?php
/**
 * Frontend downloads.
 *
 * @since 2.1.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend\Product;

use WOOPAYWALL\Frontend\Session;
use WOOPAYWALL\Order\Info;
use WOOPAYWALL\Settings\Controller as SettingsController;
use WOOPAYWALL\Template;

/**
 * Class Downloads
 *
 * @package WOOPAYWALL\Frontend\Product
 */
class Downloads {

	/**
	 * Options table key.
	 *
	 * @var string
	 */
	const OPTION_SHOW_ON_PRODUCT_PAGE = 'downloads_show_on_product_page';

	/**
	 * Show download buttons.
	 *
	 * @since 2.1.0
	 *
	 * @param \WC_Product_Paywall $product The product.
	 */
	public static function show_buttons( $product ) {

		if ( 'yes' !== SettingsController::get_option( self::OPTION_SHOW_ON_PRODUCT_PAGE, 'yes' ) ) {
			// Disabled in settings.
			return;
		}

		if ( ! $product->has_file() ) {
			// No downloads in this product.
			return;
		}

		$product_id = $product->get_id();

		/**
		 * To generate secure download URLs, we need an order ID.
		 * Find the most recent paid order by the current user, which has this product.
		 */

		$last_order_id_with_this_product = 0;

		$current_user_id = \get_current_user_id();

		if ( $current_user_id ) {
			// User with account..get orders from DB.
			// TODO: make this a method of CurrentUser.
			$args = array(
				'status'        => \wc_get_is_paid_statuses(),
				'customer_id'   => $current_user_id,
				'return'        => 'ids',
				'limit'         => '-1',
				'orderby'       => 'id',
				'order'         => 'DESC',
				'no_found_rows' => true,
				'cache_results' => false,
			);

			$order_ids = \wc_get_orders( $args );
		} else {
			// Guest user. Get orders from the session.
			$order_ids = Session::get_order_ids();
		}

		foreach ( $order_ids as $order_id ) {
			$order_info  = new Info( $order_id );
			$order_items = $order_info->get_items();
			foreach ( $order_items as $order_item ) {
				if ( $product_id === $order_item->get_product_id() ) {
					$last_order_id_with_this_product = $order_id;
					break 2;
				}
			}
		}

		if ( $last_order_id_with_this_product ) {

			$order = \wc_get_order( $last_order_id_with_this_product );
			if ( $order ) {

				$downloads = $order->get_downloadable_items();
				if ( $downloads ) {

					$template_name = 'paywall-download-buttons.php';
					$template_args = array( 'downloads' => $downloads, 'product_id' => $product_id );

					Template\Controller::load( $template_name, $template_args );
				}
			}
		}
	}
}

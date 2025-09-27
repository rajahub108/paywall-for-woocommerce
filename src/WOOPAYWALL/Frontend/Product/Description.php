<?php
/**
 * Product description.
 *
 * @since 1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend\Product;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;

/**
 * Class Description
 *
 * @package WOOPAYWALL\Frontend\Product
 */
class Description extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_filter( 'woocommerce_product_tabs', array( $this, 'hide' ), App::HOOK_PRIORITY_LATE );
	}

	/**
	 * Hide the product description tab until paid - if specified in Product Data.
	 *
	 * @param array $tabs The product tabs.
	 *
	 * @return array
	 */
	public function hide( $tabs ) {

		$product = \WC_Product_Paywall::wc_get_product();
		if ( $product && $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) && ! $product->is_ok_to_view() && $product->is_need_to_hide_description() ) {
			unset( $tabs['description'] );
		}

		return $tabs;
	}
}

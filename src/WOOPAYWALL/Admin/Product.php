<?php
/**
 * Product in admin area.
 *
 * @since 1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Admin;

use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class Product
 *
 * @package WOOPAYWALL\Admin
 */
class Product extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_action( 'woocommerce_product_quick_edit_save', array( $this, 'handle_quick_edit_changes' ) );
	}

	/**
	 * WC shows and handles Quick Edit prices only for 'simple' and 'external' products.
	 * In our quick-edit.js, we show the price fields, and here we process the changes.
	 *
	 * @param \WC_Product $product The product object.
	 */
	public function handle_quick_edit_changes( $product ) {

		$nonce  = 'woocommerce_quick_edit_nonce';
		$action = 'woocommerce_quick_edit_nonce';
		if ( ! isset( $_POST[ $nonce ] ) ) {
			return;
		}
		$post_nonce = \sanitize_text_field( \wp_unslash( $_POST[ $nonce ] ) );
		if ( ! \wp_verify_nonce( $post_nonce, $action ) ) {
			return;
		}

		if ( ! $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) ) {
			return;
		}

		$is_need_to_save = false;

		foreach ( array( '_regular_price', '_sale_price' ) as $prop ) {
			if ( isset( $_POST[ $prop ] ) ) {
				$product->{"set$prop"}( \sanitize_text_field( \wp_unslash( $_POST[ $prop ] ) ) );
				$is_need_to_save = true;
			}
		}

		if ( $is_need_to_save ) {
			$product->save();
		}
	}
}

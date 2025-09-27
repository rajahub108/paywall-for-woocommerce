<?php
/**
 * Alter Add To Cart text.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Alter;

use WOOPAYWALL\App;
use WOOPAYWALL\ProductState;

/**
 * Class AddToCartText
 *
 * @package WOOPAYWALL\Alter
 */
class AddToCartText extends AbstractAlter {

	/**
	 * Settings section ID.
	 *
	 * @return string
	 */
	public static function get_section_id() {
		return 'alter_add_to_cart_text';
	}

	/**
	 * Hook priority.
	 *
	 * @var int
	 */
	const HOOK_PRIORITY = App::HOOK_PRIORITY_LATE;

	/**
	 * Paywall text for a specific product state.
	 *
	 * @param string $product_state State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	protected static function get_paywall_text( $product_state ) {

		if ( ProductState::PAID === $product_state ) {
			$text = \_x( 'View', 'alter', 'paywall-for-woocommerce' );
		} elseif ( ProductState::IN_CART === $product_state ) {
			$text = \_x( 'Checkout', 'alter', 'paywall-for-woocommerce' );
		} elseif ( ProductState::OUT_OF_STOCK === $product_state ) {
			$text = \__( 'Out of stock', 'woocommerce' );
		} elseif ( ProductState::NOT_PURCHASABLE === $product_state ) {
			$text = \__( 'Read more', 'woocommerce' );
		} else {
			$text = \_x( 'Premium content', 'alter', 'paywall-for-woocommerce' );
		}

		return $text;
	}

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_filter(
			'woopaywall_alter_add_to_cart_text',
			array( $this, 'filter__woopaywall_alter_text' ),
			self::HOOK_PRIORITY,
			2
		);
	}
}

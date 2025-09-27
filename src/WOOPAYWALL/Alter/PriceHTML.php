<?php
/**
 * Alter Price HTML.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Alter;

use WOOPAYWALL\App;
use WOOPAYWALL\ProductState;

/**
 * Class PriceHTML
 *
 * @package WOOPAYWALL\Alter
 */
class PriceHTML extends AbstractAlter {

	/**
	 * Settings section ID.
	 *
	 * @return string
	 */
	public static function get_section_id() {
		return 'alter_price_html';
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
	 * @return string|false False means "do not change".
	 * @noinspection PhpUnused
	 */
	protected static function get_paywall_text( $product_state ) {

		if ( ProductState::PAID === $product_state ) {
			$text = \_x( 'Paid', 'alter', 'paywall-for-woocommerce' );
		} elseif ( ProductState::IN_CART === $product_state ) {
			$text = \_x( 'In cart', 'alter', 'paywall-for-woocommerce' );
		} elseif ( ProductState::OUT_OF_STOCK === $product_state ) {
			$text = \__( 'Out of stock', 'woocommerce' );
		} elseif ( ProductState::NOT_PURCHASABLE === $product_state ) {
			$text = '&nbsp;';
		} else {
			$text = false;
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
			'woopaywall_alter_price_html',
			array( $this, 'filter__woopaywall_alter_text' ),
			self::HOOK_PRIORITY,
			2
		);
	}

	/**
	 * Settings field choices for a specific product state.
	 *
	 * @param string $product_state State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return array
	 */
	public static function get_field_options( $product_state ) {
		return array_merge(
			parent::get_field_options( $product_state ),
			array(
				self::OPTION_VALUE['BLANK'] => _x(
					'Replace with a blank string', 'alter', 'paywall-for-woocommerce' ),
			) );
	}
}

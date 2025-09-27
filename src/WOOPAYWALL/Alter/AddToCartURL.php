<?php
/**
 * Alter Add To Cart URL.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Alter;

use WOOPAYWALL\App;
use WOOPAYWALL\ProductState;
use WOOPAYWALL\Settings\Controller as SettingsController;
use WOOPAYWALL\Settings\Section;

/**
 * Class AddToCartURL
 *
 * @package WOOPAYWALL\Alter
 */
class AddToCartURL extends AbstractAlter {

	/**
	 * Settings section ID.
	 *
	 * @return string
	 */
	public static function get_section_id() {
		return 'alter_add_to_cart_url';
	}

	/**
	 * Hook priority.
	 *
	 * @var int
	 */
	const HOOK_PRIORITY = App::HOOK_PRIORITY_LATE;

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_filter(
			'woopaywall_alter_add_to_cart_url',
			array( $this, 'filter__woopaywall_alter_add_to_cart_url' ),
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

		$options = array( self::OPTION_VALUE['NO_CHANGE'] => Section::text_do_not_change() );

		if ( ProductState::IN_CART === $product_state ) {
			$options[ self::OPTION_VALUE['URL_CHECKOUT'] ] = \__( 'Redirect to the Checkout page', 'paywall-for-woocommerce' );
		} elseif ( in_array( $product_state,
			array(
				ProductState::PAID,
				ProductState::AVAILABLE,
				ProductState::OUT_OF_STOCK,
			),
			true ) ) {
			$options[ self::OPTION_VALUE['URL_PRODUCT'] ] = \__( 'Redirect to the product page', 'paywall-for-woocommerce' );
		}

		return $options;
	}

	/**
	 * Default field option for a specific product state.
	 *
	 * @param string $product_state State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return string
	 */
	public static function get_default_option_value( $product_state = '' ) {

		if ( ProductState::IN_CART === $product_state ) {
			$value = self::OPTION_VALUE['URL_CHECKOUT'];
		} else {
			$value = self::OPTION_VALUE['URL_PRODUCT'];
		}

		return $value;
	}


	/**
	 * Alter Add To Cart URL.
	 *
	 * @param false              $altered False = do not alter.
	 * @param \WC_Product_Paywall $product Product instance.
	 *
	 * @return false|string If a string returned, it will be used as the URL.
	 */
	public function filter__woopaywall_alter_add_to_cart_url( $altered, $product ) {

		if ( false !== $altered ) {
			// Someone already altered. Pass-through.
			return $altered;
		}

		$settings_choice = $this->get_settings_choice( $product );

		if ( self::OPTION_VALUE['URL_CHECKOUT'] === $settings_choice ) {
			$altered = $product->get_checkout_url();
		} elseif ( self::OPTION_VALUE['URL_PRODUCT'] === $settings_choice ) {
			$altered = $product->get_permalink();
		} elseif ( self::OPTION_VALUE['URL_CART'] === $settings_choice ) {
			$altered = \wc_get_cart_url();
		}

		return $altered;
	}

	/**
	 * If button for available products goes to the product URL, we need to disable AJAX add-to-cart.
	 *
	 * @return bool
	 */
	public static function need_to_disable_ajax_add_to_cart() {

		$product_state   = ProductState::AVAILABLE;
		$settings_choice = SettingsController::get_option(
			self::make_option_key( $product_state ),
			self::get_default_option_value()
		);

		if ( self::OPTION_VALUE['URL_PRODUCT'] === $settings_choice ) {
			return true;
		}

		return false;
	}
}

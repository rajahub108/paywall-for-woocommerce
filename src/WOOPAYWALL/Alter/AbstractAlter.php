<?php
/** Abstract "Alter".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Alter;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\ProductState;
use WOOPAYWALL\Settings\Controller as SettingsController;
use WOOPAYWALL\Settings\Section;

/**
 * Class AbstractAlter
 *
 * @package WOOPAYWALL\Alter
 */
abstract class AbstractAlter extends Hookable implements InterfaceAlter {

	/**
	 * Options table values.
	 *
	 * @var string
	 */
	const OPTION_VALUE = array(
		'NO_CHANGE'    => 'no_change',
		'BLANK'        => 'blank',
		'PAYWALL_TEXT' => 'paywall_text',
		'URL_PRODUCT'  => 'url_product',
		'URL_CART'     => 'url_cart',
		'URL_CHECKOUT' => 'url_checkout',
	);

	/**
	 * Options table key.
	 *
	 * @param string $product_state Product state.
	 *
	 * @return string
	 */
	public static function make_option_key( $product_state ) {
		return static::get_section_id() . '_' . strtolower( $product_state );
	}

	/**
	 * Default field option for a specific product state.
	 *
	 * @param string $product_state [Optional] State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return string
	 */
	public static function get_default_option_value( $product_state = '' ) {
		return static::OPTION_VALUE['PAYWALL_TEXT'];
	}

	/**
	 * Settings field choices for a specific product state.
	 *
	 * @param string $product_state State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return array
	 */
	public static function get_field_options( $product_state ) {

		return array(
			self::OPTION_VALUE['NO_CHANGE']    => Section::text_do_not_change(),
			self::OPTION_VALUE['PAYWALL_TEXT'] =>
				sprintf( // Translators: %s - placeholder for text to display.
					\_x( 'Replace with "%s"', 'alter', 'paywall-for-woocommerce' ),
					static::get_paywall_text( $product_state ) ),
		);
	}

	/**
	 * Settings field title for a specific product state.
	 *
	 * @param string $product_state State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return string
	 */
	public static function get_field_title( $product_state ) {

		if ( ProductState::PAID === $product_state ) {
			$title = \__( 'For purchased and not yet expired products:', 'paywall-for-woocommerce' );
		} elseif ( ProductState::IN_CART === $product_state ) {
			$title = \__( 'For products already the Cart:', 'paywall-for-woocommerce' );
		} elseif ( ProductState::OUT_OF_STOCK === $product_state ) {
			$title = \__( 'For out-of-stock products:', 'paywall-for-woocommerce' );
		} elseif ( ProductState::AVAILABLE === $product_state ) {
			$title = \__( 'For products available for purchasing:', 'paywall-for-woocommerce' );
		} else {
			$title = '';
		}

		return $title;
	}

	/**
	 * Paywall text for a specific product state.
	 *
	 * @param string $product_state State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return string|false False means "do not change".
	 */
	protected static function get_paywall_text( $product_state ) {
		return $product_state;
	}

	/**
	 * Returns the settings choice for the product in its current state.
	 *
	 * @param \WC_Product_Paywall $product Product instance.
	 *
	 * @return string
	 */
	protected static function get_settings_choice( $product ) {
		return SettingsController::get_option(
			static::make_option_key( $product->get_state() ),
			static::get_default_option_value()
		);
	}

	/**
	 * Alter text.
	 *
	 * @param false              $altered False = do not alter.
	 * @param \WC_Product_Paywall $product Product instance.
	 *
	 * @return false|string If a string returned, it will be used instead of the original text/HTML.
	 */
	public function filter__woopaywall_alter_text( $altered, $product ) {

		if ( false !== $altered ) {
			// Someone already altered. Pass-through.
			return $altered;
		}

		/**
		 * Do not alter text for zero-priced products that are not yet paid/in cart/checkout.
		 *
		 * @since 3.0.3
		 */
		if ( 0 >= (float) $product->get_price() && ProductState::AVAILABLE === $product->get_state() ) {
			return false;
		}

		$settings_choice = static::get_settings_choice( $product );

		if ( static::OPTION_VALUE['PAYWALL_TEXT'] === $settings_choice ) {
			$altered = static::get_paywall_text( $product->get_state() );
		} elseif ( static::OPTION_VALUE['BLANK'] === $settings_choice ) {
			$altered = '&nbsp;';
		}

		return $altered;
	}
}

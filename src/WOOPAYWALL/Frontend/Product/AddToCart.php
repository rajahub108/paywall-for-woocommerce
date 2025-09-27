<?php
/**
 * Add-to-cart methods.
 *
 * @since 2.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend\Product;

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;
use WOOPAYWALL\Settings\Controller as SettingsController;
use WOOPAYWALL\Settings\Section;

/**
 * Class Frontend\Product\AddToCart
 */
class AddToCart extends Hookable {

	/**
	 * Options table key.
	 *
	 * @var string
	 */
	const OPTION_ACTION = 'add_to_cart_action';

	/**
	 * Form action.
	 *
	 * @var string
	 */
	const ACTION_NO_CHANGE = 'no_change';

	/**
	 * Form action.
	 *
	 * @var string
	 */
	const ACTION_CART = 'cart';

	/**
	 * Form action.
	 *
	 * @var string
	 */
	const ACTION_CHECKOUT = 'checkout';

	/**
	 * Default form action.
	 *
	 * @var string
	 */
	const DEFAULT_ACTION = self::ACTION_CHECKOUT;

	/**
	 * Form action.
	 *
	 * @var array|null
	 */
	protected static $action = null;

	/**
	 * Lazy getter for $action.
	 *
	 * @return array
	 */
	public static function getAction() {

		if ( null === self::$action ) {
			self::$action = array(
				self::ACTION_NO_CHANGE => Section::text_do_not_change(),
				self::ACTION_CART      => \__( 'Redirect to the Cart page', 'paywall-for-woocommerce' ),
				self::ACTION_CHECKOUT  => \__( 'Redirect to the Checkout page', 'paywall-for-woocommerce' ),
			);
		}

		return self::$action;
	}

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_filter(
			'woocommerce_add_to_cart_form_action',
			array( $this, 'filter__woocommerce_add_to_cart_form_action' ),
			App::HOOK_PRIORITY_LATE
		);
	}

	/**
	 * Change the single product page `add-to-cart` form behavior:
	 * - stay on the same page
	 * - go to Cart page
	 * - go to Checkout page
	 *
	 * @file woocommerce/templates/single-product/add-to-cart/simple.php
	 *
	 * @param $url
	 *
	 * @return string
	 */
	public function filter__woocommerce_add_to_cart_form_action( $url ) {

		$product = \wc_get_product();

		if ( $product instanceof AbstractPaywallProductSimple ) {

			$form_action = SettingsController::get_option( self::OPTION_ACTION, self::DEFAULT_ACTION );

			if ( self::ACTION_CHECKOUT === $form_action ) {
				$url = \wc_get_checkout_url();
			} elseif ( self::ACTION_CART === $form_action ) {
				$url = \wc_get_cart_url();
			}
		}

		return $url;
	}
}

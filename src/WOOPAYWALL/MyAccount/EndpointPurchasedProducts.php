<?php
/**
 * Endpoint PurchasedProducts
 *
 * @since 3.0.0-rc.1
 * Copyright (c) 2021, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\MyAccount;

use WOOPAYWALL\Frontend\Shortcode;

/**
 * Class EndpointPurchasedProducts
 *
 * @package WOOPAYWALL\MyAccount
 */
class EndpointPurchasedProducts extends AbstractEndpoint {

	/**
	 * Static: ID
	 *
	 * @return string
	 */
	public static function id() {
		return 'purchased-products';
	}

	/**
	 * Static: title
	 *
	 * @return string
	 */
	public static function title() {
		return \__( 'Purchased products', 'paywall-for-woocommerce' );
	}

	/**
	 * Insert tab in My Account before this one.
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	protected function insert_before() {
		return 'downloads';
	}

	/**
	 * Display the My Account tab content.
	 *
	 * @see \woocommerce_account_content()
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function action__endpoint_content() {
		Shortcode::echo_shortcode_purchased_products();
	}
}

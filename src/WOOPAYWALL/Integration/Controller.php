<?php
/**
 * Integration controller.
 *
 * @since 2.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Integration;

use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class Integration\Controller
 */
class Controller extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {
		$this->multilingual_extensions();
		$this->various_extensions();
	}

	/**
	 * Multilingual.
	 *
	 * @since 3.1.0
	 */
	protected function multilingual_extensions() {

		/**
		 * Plugin Name: WPML Multilingual CMS
		 * Plugin URI: https://wpml.org/
		 */
		if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
			$wpml = new WPML();
			$wpml->setup_hooks();
		}
	}

	/**
	 * Various.
	 */
	protected function various_extensions() {

		/**
		 * Plugin Name: WooCommerce Name Your Price
		 * Plugin URI: https://woocommerce.com/products/name-your-price/
		 */
		if ( class_exists( 'WC_Name_Your_Price', false ) ) {
			$name_your_price = new WCNameYourPrice();
			$name_your_price->setup_hooks();
		}
	}
}

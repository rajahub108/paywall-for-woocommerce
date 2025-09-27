<?php
/**
 * Settings section "SectionAddToCart".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Frontend\Product\AddToCart;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionAddToCart
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionAddToCart extends AbstractSection {

	/**
	 * Constructor.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {

		$this->set_id( 'add_to_cart' );
		$this->set_title( \__( 'Add-to-cart behavior for Paywall products', 'paywall-for-woocommerce' ) );

		$this->set_priority( Controller::get_priority( $this->get_id() ) );
	}

	/**
	 * Add fields to the section.
	 *
	 * @param array $settings The "All Settings" array.
	 * @noinspection PhpUnused
	 */
	protected function add_fields( &$settings ) {

		if ( 'yes' === \get_option( 'woocommerce_cart_redirect_after_add' ) ) {
			$desc = '<mark>' . sprintf( // Translators: %s is a related settings name (text from Woo).
					\__( 'This setting is ignored because "%s" is checked in the Products tab.', 'paywall-for-woocommerce' ),
					\__( 'Redirect to the cart page after successful addition', 'woocommerce' )
				) . '</mark>';
		} else {
			$desc = '';
		}

		$settings[] =
			array(
				'id'      => Controller::make_option_key( AddToCart::OPTION_ACTION ),
				'type'    => Controller::get_select_type(),
				'title'   => \__( 'When a product added to Cart:', 'paywall-for-woocommerce' ),
				'desc'    => $desc,
				'default' => AddToCart::DEFAULT_ACTION,
				'options' => AddToCart::getAction(),
			);
	}
}

<?php
/**
 * Settings section "SectionAlterAddToCartURL".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Alter\AddToCartURL;
use WOOPAYWALL\ProductState;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionAlterAddToCartText
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionAlterAddToCartURL extends AbstractSection {

	/**
	 * Constructor.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {

		$this->set_id( AddToCartURL::get_section_id() );
		$this->set_title( \__( 'Add-to-cart button URLs for Paywall products', 'paywall-for-woocommerce' ) );
		$this->set_desc(
			\__( 'For better shopping experience with Paywall products, the buttons on Shop pages can be redirected depending on the product state: available, already in the cart, etc.', 'paywall-for-woocommerce' ) . ' <br>' . __( 'When you modify these settings, do not forget to adjust the button texts accordingly.', 'paywall-for-woocommerce' ) );

		$this->set_priority( Controller::get_priority( $this->get_id() ) );
	}

	/**
	 * Add fields to the section.
	 *
	 * @param array $settings The "All Settings" array.
	 * @noinspection PhpUnused
	 */
	protected function add_fields( &$settings ) {
		foreach (
			array(
				ProductState::PAID,
				ProductState::IN_CART,
				ProductState::AVAILABLE,
				ProductState::OUT_OF_STOCK,
			) as $product_state
		) {
			$settings[] =
				array(
					'id'      => Controller::make_option_key( AddToCartURL::make_option_key( $product_state ) ),
					'type'    => Controller::get_select_type(),
					'title'   => AddToCartURL::get_field_title( $product_state ),
					'default' => AddToCartURL::get_default_option_value( $product_state ),
					'options' => AddToCartURL::get_field_options( $product_state ),
				);
		}
	}
}

<?php
/**
 * Settings section "SectionAlterAddToCartText".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Alter\AddToCartText;
use WOOPAYWALL\ProductState;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionAlterAddToCartText
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionAlterAddToCartText extends AbstractSection {

	/**
	 * Intro constructor.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {

		$this->set_id( AddToCartText::get_section_id() );
		$this->set_title( \__( 'Add-to-cart button texts for Paywall products', 'paywall-for-woocommerce' ) );

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
					'id'      => Controller::make_option_key( AddToCartText::make_option_key( $product_state ) ),
					'type'    => Controller::get_select_type(),
					'title'   => AddToCartText::get_field_title( $product_state ),
					'default' => AddToCartText::get_default_option_value(),
					'options' => AddToCartText::get_field_options( $product_state ),
				);
		}
	}
}

<?php
/**
 * Settings section "SectionAlterPriceHTML".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Alter\PriceHTML;
use WOOPAYWALL\ProductState;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionAlterPriceHTML
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionAlterPriceHTML extends AbstractSection {

	/**
	 * Constructor.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {

		$this->set_id( PriceHTML::get_section_id() );

		$this->set_title( \__( 'Alter displayed prices for Paywall products', 'paywall-for-woocommerce' ) );

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
				/**
				 * Not applicable:
				 * ProductState::STATE_AVAILABLE,
				 */
				ProductState::OUT_OF_STOCK,
			) as $product_state
		) {
			$settings[] =
				array(
					'id'      => Controller::make_option_key( PriceHTML::make_option_key( $product_state ) ),
					'type'    => Controller::get_select_type(),
					'title'   => PriceHTML::get_field_title( $product_state ),
					'default' => PriceHTML::get_default_option_value(),
					'options' => PriceHTML::get_field_options( $product_state ),
				);
		}
	}
}

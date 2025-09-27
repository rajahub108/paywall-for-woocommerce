<?php
/**
 * Settings section "Expiration".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Settings\Controller;
use WOOPAYWALL\Expiration;

/**
 * Class SectionExpiration
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionExpiration extends AbstractSection {

	/**
	 * Constructor.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {

		$this->set_id( 'expiration' );

		$this->set_title( \__( 'Default expiration settings', 'paywall-for-woocommerce' ) );

		$this->set_desc( \__( 'This setting applies to the purchases made by registered users. Guest customers will be able to access the paid products until the expiration of their WooCommerce session.', 'paywall-for-woocommerce' ) );

		$this->set_priority( Controller::get_priority( $this->get_id() ) );
	}

	/**
	 * Add fields to the section.
	 *
	 * @param array $settings The "All Settings" array.
	 * @noinspection PhpUnused
	 */
	protected function add_fields( &$settings ) {
		$settings[] =
			array(
				'id'                => Controller::make_option_key( Expiration\GlobalSettings::OPTION_VALUE ),
				'title'             => \__( 'Paywall purchases expire after', 'paywall-for-woocommerce' ),
				'desc'              => \__( '1 hour to 365 days; 0 - never expire', 'paywall-for-woocommerce' ),
				'type'              => 'number',
				'default'           => Expiration\ExpireAfter::DEFAULT_VALUE,
				'custom_attributes' => array(
					'min'  => 0,
					'max'  => Expiration\GlobalSettings::MAX_VALUE,
					'step' => 1,
				),
				'css'               => 'width: 5em; text-align: right',
			);

		$field_id   = Controller::make_option_key( Expiration\GlobalSettings::OPTION_UNITS );
		$settings[] =
			array(
				'id'      => $field_id,
				'title'   => '',
				'default' => Expiration\ExpireAfter::DEFAULT_UNITS,
				'type'    => Controller::get_select_type( $field_id ),
				'options' => Expiration\Units::get_units( Expiration\Units::FORCE_PLURAL ),
			);

		$settings[] = array(
			'id'      => Controller::make_option_key( Expiration\GlobalSettings::OPTION_SHOW_AT_CHECKOUT ),
			'title'   => \__( 'Show default expiration at checkout?', 'paywall-for-woocommerce' ),
			'desc'    => \__( 'If checked, the default expiration will be shown on the Checkout page.', 'paywall-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		$settings[] = array(
			'id'      => Controller::make_option_key( Expiration\GlobalSettings::OPTION_SHOW_ON_SINGLE_PRODUCT ),
			'title'   => \__( 'Show custom expiration on Paywall product pages?', 'paywall-for-woocommerce' ),
			'desc'    => \__( 'If checked, the product-specific expiration will be shown on the single product page, before purchase.', 'paywall-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);
	}
}

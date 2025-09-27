<?php
/**
 * Settings section "SectionDownloads".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Frontend\Controller as FrontendController;
use WOOPAYWALL\Frontend\Product\Downloads;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionProductOptions
 *
 * @since   3.0.0
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionProductOptions extends AbstractSection {

	/**
	 * Section ID.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	const ID = 'product_options';

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {

		$this->set_id( self::ID );
		$this->set_title( \__( 'Product options', 'paywall-for-woocommerce' ) );

		$this->set_priority( Controller::get_priority( $this->get_id() ) );
	}

	/**
	 * Add fields to the section.
	 *
	 * @since        3.0.0
	 * @since        3.5.0 Added 'Sold individually?' option.
	 * @since        3.6.0 Added OPTION_TWEAK_CART_CHANGE_MESSAGES.
	 *
	 * @param array $settings The "All Settings" array.
	 *
	 * @noinspection PhpMissingParentCallCommonInspection
	 */
	protected function add_fields( &$settings ) {

		$settings[] = array(
			'id'      => Controller::make_option_key( Downloads::OPTION_SHOW_ON_PRODUCT_PAGE ),
			'title'   => \__( 'Show download buttons?', 'paywall-for-woocommerce' ),
			'desc'    => \__( 'When a Paywall product has downloads, the download buttons will be shown on the product page after purchase.', 'paywall-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => 'yes',
		);

		$settings[] = array(
			'id'      => Controller::make_option_key( Controller::OPTION_FORCE_SOLD_INDIVIDUALLY ),
			'title'   => \__( 'Force `Sold individually`?', 'paywall-for-woocommerce' ),
			'desc'    => \__( 'If checked, then "Sold individually" is set for all Paywall products (Recommended).', 'paywall-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => Controller::OPTION_FORCE_SOLD_INDIVIDUALLY_DEFAULT,
		);

		$settings[] = array(
			'id'      => Controller::make_option_key( \WC_Product_Paywall::OPTION_TWEAK_CART_CHANGE_MESSAGES ),
			'title'   => \__( 'Show Paywall messages on Cart changes?', 'paywall-for-woocommerce' ),
			'desc'    => \__( 'If checked, show Paywall-specific messages when a Paywall product is added to the Cart (Recommended).', 'paywall-for-woocommerce' ),
			'type'    => 'checkbox',
			'default' => \WC_Product_Paywall::OPTION_TWEAK_CART_CHANGE_MESSAGES_DEFAULT,
		);

		$settings[] =
			array(
				'id'      => Controller::make_option_key( FrontendController::OPTION_ORDER_PAGE_BUTTONS ),
				'type'    => Controller::get_select_type(),
				'title'   => \__( 'Show "Purchased Products" on the "Order received" page?', 'paywall-for-woocommerce' ),
				'default' => FrontendController::OPTION_ORDER_PAGE_BUTTONS_BEFORE,
				'options' => array(
					FrontendController::OPTION_ORDER_PAGE_BUTTONS_BEFORE => \__( 'Before order details', 'paywall-for-woocommerce' ),
					FrontendController::OPTION_ORDER_PAGE_BUTTONS_AFTER  => \__( 'After order details', 'paywall-for-woocommerce' ),
					FrontendController::OPTION_ORDER_PAGE_BUTTONS_NONE   => \__( 'Do not show', 'paywall-for-woocommerce' ),
				),
			);
	}
}

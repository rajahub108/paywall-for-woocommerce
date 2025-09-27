<?php
/**
 * Global Expiration settings.
 *
 * @since 2.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Expiration;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;
use WOOPAYWALL\Frontend\Alert;
use WOOPAYWALL\Settings\Controller as SettingsController;

/**
 * Class Expiration/GlobalSettings
 */
class GlobalSettings extends Hookable {

	/**
	 * Maximum expiration value.
	 *
	 * @var int
	 */
	const MAX_VALUE = 365;

	/**
	 * Options table key.
	 *
	 * @var string
	 */
	const OPTION_VALUE = 'expiration_value';

	/**
	 * Options table key.
	 *
	 * @var string
	 */
	const OPTION_UNITS = 'expiration_units';

	/**
	 * Options table key.
	 *
	 * @var string
	 */
	const OPTION_SHOW_AT_CHECKOUT = 'expiration_show_at_checkout';

	/**
	 * Options table key.
	 *
	 * @var string
	 */
	const OPTION_SHOW_ON_SINGLE_PRODUCT = 'expiration_show_on_single_product';

	/**
	 * To save the expiration settings at checkout in order meta.
	 *
	 * @var string
	 */
	const ORDER_META_KEY = 'expire_after';

	/**
	 * Expiration settings.
	 *
	 * @var ExpireAfter
	 */
	protected $expire_after;

	/**
	 * Expiration constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->expire_after = new ExpireAfter();
	}

	/**
	 * Getter: Expiration settings.
	 *
	 * @return ExpireAfter
	 */
	public function getExpireAfter() {
		return $this->expire_after;
	}

	/**
	 * Returns true if expiration value is set to > 0.
	 *
	 * @return bool
	 */
	public function is_expiration_set() {
		return $this->expire_after->getValue() > 0;
	}

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		$this->load_expiration_settings();

		if ( $this->is_expiration_set() && SettingsController::get_option( self::OPTION_SHOW_AT_CHECKOUT ) ) {
			\add_action(
				'woocommerce_review_order_before_payment',
				array( $this, 'show_at_checkout' ),
				App::HOOK_PRIORITY_LATE
			);
		}
	}

	/**
	 * Load expiration settings from Options.
	 */
	protected function load_expiration_settings() {
		$this->expire_after->setValue(
			SettingsController::get_option( self::OPTION_VALUE, ExpireAfter::DEFAULT_VALUE ) );

		$this->expire_after->setUnits(
			SettingsController::get_option( self::OPTION_UNITS, ExpireAfter::DEFAULT_UNITS ) );
	}

	/**
	 * Print expiration terms on Checkout.
	 */
	public function show_at_checkout() {
		Alert::checkout_expiration_terms(
			$this->expire_after->getUnits(),
			$this->expire_after->getValue()
		);
	}

	/**
	 * Print expiration terms on Single Product page.
	 *
	 * @param \WC_Product_Paywall|\WC_Product_Pwpass $product The product instance.
	 *
	 * @todo Move to Alerts.
	 *
	 */
	public function show_on_single_product_page( $product ) {
		if (
			'yes' === SettingsController::get_option( self::OPTION_SHOW_ON_SINGLE_PRODUCT )
			&& $product->is_purchasable()
			&& $product->has_custom_expiration()
		) {
			$expire_after = $product->get_expire_after();
			Alert::single_product_expiration_terms( $expire_after->getUnits(), $expire_after->getValue() );
		}
	}
}

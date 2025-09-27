<?php
/**
 * Guest
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\User;

use WOOPAYWALL\Frontend\Session;

/**
 * Class Guest
 *
 * @package WOOPAYWALL\User
 */
class Guest extends AbstractUser {

	/**
	 * Construct Guest as a {@see \WC_Customer} without user ID and in the `is_session` mode.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {
		parent::__construct( 0, true );
	}

	/**
	 * Populate the {@see $purchased_products}.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function load_purchased_products() {

		$this->purchased_products = array();

		$order_ids = Session::get_order_ids();
		foreach ( $order_ids as $order_id ) {
			$this->load_products_from_order( $order_id );
		}
	}

	/**
	 * Returns true if the user can view the hidden parts of the product (purchased and not expired yet).
	 * A faster alternative to the method in parent class.
	 *
	 * @deprecated
	 * @since 3.4.0 Disabled because it does not check the product expiration.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 * public function can_view_product( $product_id ) {
	 * return Session::is_purchased( $product_id );
	 * }
	 */
}

<?php
/**
 * Customer
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\User;

use WOOPAYWALL\Dependencies\TIVWP\WC\Order\Query;

/**
 * Class Customer
 *
 * @package WOOPAYWALL\User
 */
class Customer extends AbstractUser {

	/**
	 * Default constructor: Customer as a {@see \WC_Customer}, with a User ID.
	 */

	/**
	 * Populate the {@see $purchased_products}.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function load_purchased_products() {

		$this->purchased_products = array();

		$order_query = new Query();

		$order_query
			->set_sorting_by_id_desc()
			->restrict_to_paid()
			->restrict_to_user_id( $this->get_id() );

		$orders = $order_query->get_orders();

		foreach ( $orders as $order ) {
			$this->load_products_from_order( $order );
		}
	}
}

<?php
/**
 * My Account.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\MyAccount;

use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class MyAccount\Controller
 */
class Controller extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {
		( new EndpointPurchasedProducts() )->setup_hooks();
	}
}

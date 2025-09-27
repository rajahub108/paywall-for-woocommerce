<?php
/**
 * "Alter" controller.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Alter;

use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class Controller
 *
 * @package WOOPAYWALL\Alter
 */
class Controller extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		$alter_price_html = new PriceHTML();
		$alter_price_html->setup_hooks();

		$add_to_cart_text = new AddToCartText();
		$add_to_cart_text->setup_hooks();

		( new AddToCartURL() )->setup_hooks();
	}
}

<?php
/**
 * Admin area controller.
 *
 * @since 1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Admin;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\Order\OrderNotes;

/**
 * Class Controller
 *
 * @package WOOPAYWALL\Admin
 */
class Controller extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		$product = new Product();
		$product->setup_hooks();

		$scripting = new Scripting();
		$scripting->setup_hooks();

		/**
		 * Include Multi-currency in the WooCommerce status report.
		 *
		 * @since 2.1.0
		 */
		$status_report = new StatusReport();
		$status_report->setup_hooks();

		/**
		 * Add our tools to the WooCommerce "Tools" tab.
		 *
		 * @since 2.1.0
		 */
		$tools = new Tools();
		$tools->setup_hooks();

		( new OrderNotes() )->setup_hooks();
	}
}

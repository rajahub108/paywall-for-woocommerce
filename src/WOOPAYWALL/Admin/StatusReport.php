<?php
/**
 * WooCommerce Status Report.
 *
 * @since 2.1.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Admin;

use WOOPAYWALL\Dependencies\TIVWP\WC\AbstractStatusReport;
use WOOPAYWALL\Settings\Controller as SettingsController;

/**
 * Class StatusReport
 *
 * @package WOOPAYWALL\Admin
 */
class StatusReport extends AbstractStatusReport {

	/**
	 * Render the report.
	 *
	 * @internal action.
	 * @noinspection PhpUnused
	 */
	public function action__woocommerce_system_status_report() {

		$label         = 'Paywall';
		$option_prefix = SettingsController::OPTIONS_PREFIX;

		$this->do_report( $label, $option_prefix );
	}
}

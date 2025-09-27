<?php
/**
 * Logging to WooCommerce Status->Logs
 *
 * @since 2.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL;

use WOOPAYWALL\Dependencies\TIVWP\Logger\Log as TIVWPLog;
use WOOPAYWALL\Settings\Controller;
use WOOPAYWALL\Settings\Sections\SectionLogging;

/**
 * WC_Logger wrapper.
 */
class Log extends TIVWPLog {

	/**
	 * Log source.
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	protected static function source() {
		return 'Paywall-for-WooCommerce';
	}

	/**
	 * Log level defined in the application settings.
	 *
	 * @return string
	 */
	protected static function threshold() {
		return Controller::get_option( SectionLogging::OPTION_LOG_LEVEL, \WC_Log_Levels::ERROR );
	}
}

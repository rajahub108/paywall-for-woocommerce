<?php
/**
 * Cache controller.
 *
 * @since 3.5.0
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Cache;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\Settings\Controller as SettingsController;

/**
 * Class CacheController
 *
 * @since 3.5.0
 */
class CacheController extends Hookable {

	/**
	 * Setup hooks.
	 *
	 * @since 3.5.0
	 */
	public function setup_hooks() {

		if ( 'yes' !== SettingsController::get_option( CacheAbstract::OPTION_CACHE_BUSTER_ENABLED, CacheAbstract::OPTION_CACHE_BUSTER_ENABLED_DEFAULT ) ) {
			// Disabled in settings.
			return;
		}

		if ( class_exists( '\WP_Rocket\Plugin', false ) ) {
			( new CacheWPRocket() )->setup_hooks();
		} elseif ( function_exists( 'wpsc_add_cookie' ) ) {
			( new CacheWPSuperCache() )->setup_hooks();
		} else {
			( new CacheGeneral() )->setup_hooks();
		}
	}
}

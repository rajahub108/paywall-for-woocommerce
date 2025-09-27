<?php
/**
 * Cache: WPSuperCache
 *
 * @since 3.5.0
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Cache;

use WOOPAYWALL\Dependencies\TIVWP\Env;

/**
 * Class CacheWPSuperCache
 *
 * @since 3.5.0
 */
class CacheWPSuperCache extends CacheAbstract {

	/**
	 * Add hash to URLs.
	 * Not needed for this caching implementation.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	protected function add_hash_to_urls() {
	}

	/**
	 * Setup backend redirect.
	 * Not needed for this caching implementation.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	protected function setup_backend_redirect() {
	}

	/**
	 * Setup actions and filters.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function setup_hooks() {
		parent::setup_hooks();

		/**
		 * Cache vary by our cookie.
		 *
		 * @since 3.5.0
		 */
		\do_action( 'wpsc_add_cookie', self::HASH_NAME );

		\add_action( 'init', array( $this, 'maybe_disable_cache' ) );
	}

	/**
	 * Disable cache if:
	 * 1. Do not cache pages when our hash is in the URL.
	 * 2. Do not cache pages for first visits (no cookie set).
	 * So, new visitors always get new pages and not cached.
	 *
	 * @since 3.5.0
	 */
	public function maybe_disable_cache() {
		if ( Env::get_http_get_parameter( self::HASH_NAME ) || empty( $_COOKIE[ self::HASH_NAME ] ) ) {
			$this->disable_cache();
		}
	}

	protected function disable_cache() {
		global $cache_enabled;
		$cache_enabled = false;
	}
}

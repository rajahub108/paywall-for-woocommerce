<?php
/**
 * Cache: WP Rocket
 *
 * @since 3.5.0
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Cache;

use WOOPAYWALL\Dependencies\TIVWP\Env;

/**
 * Class CacheWPRocket
 *
 * @since 3.5.0
 */
class CacheWPRocket extends CacheAbstract {

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

		if ( Env::in_wp_admin() ) {
			/**
			 * Do when the settings saved.
			 *
			 * @see \WC_Admin_Settings::save
			 */
			\add_action( 'tivwp_settings_tab_created', function ( $tab_id ) {
				\add_action(
					'woocommerce_update_options_' . $tab_id,
					array( $this, 'action__flush_rocket' )
				);
			} );

			\add_filter(
				'rocket_cache_dynamic_cookies',
				array( $this, 'filter__rocket_cache_cookies' )
			);
			\add_filter(
				'rocket_cache_mandatory_cookies',
				array( $this, 'filter__rocket_cache_cookies' )
			);
		}
	}

	/**
	 * When we save our settings, regenerate WP Rocket configs and clear the cache.
	 *
	 * @since 3.5.0
	 * @return void
	 */
	public function action__flush_rocket() {

		// Update the WP Rocket .htaccess rules.
		if ( function_exists( 'flush_rocket_htaccess' ) ) {
			\flush_rocket_htaccess( true );
		}

		// Update the WP Rocket config file.
		if ( function_exists( 'rocket_generate_config_file' ) ) {
			\rocket_generate_config_file();
		}

		// Clear WP Rocket cache.
		if ( function_exists( 'rocket_clean_domain' ) ) {
			\rocket_clean_domain();
		}
	}

	/**
	 * Add our cookie to the "special treatment" by WPRocket.
	 *
	 * @url https://docs.wp-rocket.me/article/1313-create-different-cache-files-with-dynamic-and-mandatory-cookies
	 *
	 * @param string[] $cookies Cookies list.
	 *
	 * @return string[]
	 * @internal Filter.
	 */
	public function filter__rocket_cache_cookies( $cookies ) {

		$cookies[] = self::HASH_NAME;

		return $cookies;
	}
}

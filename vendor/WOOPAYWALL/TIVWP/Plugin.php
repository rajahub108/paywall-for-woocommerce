<?php
/**
 * Plugin methods.
 *
 * @since 1.7.0
 */

namespace WOOPAYWALL\Dependencies\TIVWP;

/**
 * Class Plugin.
 *
 * @since 1.7.0
 */
class Plugin {

	/**
	 * Self-deactivate a "run-and-go" plugin.
	 *
	 * @since        1.7.0
	 *
	 * @param string $plugin_file The __FILE__ of the plugin loader.
	 *
	 * @noinspection PhpUnused
	 */
	public static function self_deactivate( $plugin_file ) {

		/**
		 * Include.
		 *
		 * @noinspection PhpIncludeInspection
		 */
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		// Self-deactivation.
		\deactivate_plugins( \plugin_basename( $plugin_file ), true );

		0 && \wp_verify_nonce( '' );
		unset( $_GET['activate'] );

	}
}

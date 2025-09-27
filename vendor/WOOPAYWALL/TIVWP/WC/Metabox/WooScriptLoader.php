<?php
/**
 * Load WooCommerce admin scripts and styles.
 *
 * @since  1.6.0
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Metabox;

use Automattic\Jetpack\Constants;
use WOOPAYWALL\Dependencies\TIVWP\InterfaceHookable;

/**
 * Class WooScriptLoader
 */
class WooScriptLoader implements InterfaceHookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		/**
		 * Add our CPT to the list of Woo screens so it loads its stuff.
		 * // \add_filter( 'woocommerce_screen_ids', function ( $screen_ids ) {
		 * //    // TODO
		 * //    $screen_ids[] = CPTA4WCS::CPT_KEY;
		 * //    $screen_ids[] = CPTPowerMail::CPT_KEY;
		 * //    return $screen_ids;
		 * // } );
		 */

		/**
		 * A hack.
		 *
		 * @see  \WC_Admin_Assets::admin_scripts
		 *
		 * @todo Why do we need to register this? Woo does it already, but selects do not work without the below call.
		 */
		\add_action( 'admin_enqueue_scripts', function () {

			$suffix  = Constants::is_true( 'SCRIPT_DEBUG' ) ? '' : '.min';
			$version = Constants::get_constant( 'WC_VERSION' );

			\wp_register_script( 'wc-enhanced-select', \WC()->plugin_url() . '/assets/js/admin/wc-enhanced-select' . $suffix . '.js', array(
				'jquery',
				'selectWoo',
			), $version, true );

		} );
	}
}

<?php
/**
 * WooCommerce Paywall Settings
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings;

use WOOPAYWALL\App;

defined( 'ABSPATH' ) || exit;

/**
 * Class Tab
 *
 * @package WOOPAYWALL\Settings
 */
class Tab extends \WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 */
	public function __construct() {
		$this->id    = App::TAB_SLUG;
		$this->label = \_x( 'Paywall', 'Settings tab title', 'paywall-for-woocommerce' );

		parent::__construct();

		\add_action( 'woocommerce_settings_' . $this->id, function () {
			$url_assets = App::instance()->plugin_dir_url() . 'assets';
			\wp_enqueue_script(
				'woopw-panel',
				$url_assets . '/js/panel.min.js',
				array(),
				PAYWALL_FOR_WOOCOMMERCE_VERSION,
				true
			);
			\wp_enqueue_style(
				'woopw-panel',
				$url_assets . '/css/panel.min.css',
				array(),
				PAYWALL_FOR_WOOCOMMERCE_VERSION
			);
		} );

		/**
		 * Act after the settings tab is created.
		 *
		 * @since 3.5.0
		 *
		 * @param string $tab_id The tab ID.
		 */
		\do_action( 'tivwp_settings_tab_created', $this->id );
	}
}

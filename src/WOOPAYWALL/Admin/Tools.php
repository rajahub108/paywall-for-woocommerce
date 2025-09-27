<?php
/**
 * WooCommerce Tools.
 *
 * @since 2.1.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Admin;

use WOOPAYWALL\Dependencies\TIVWP\WC\AbstractTools;
use WOOPAYWALL\Settings\Controller as SettingsController;

/**
 * Class Tools
 *
 * @package WOOPAYWALL\Admin
 */
class Tools extends AbstractTools {

	/**
	 * Button(s) on the WooCommerce > Status > Tools page.
	 *
	 * @see Tools::reset_all_settings()
	 *
	 * @param array $tools All tools array.
	 *
	 * @return array
	 * @noinspection PhpUnused
	 */
	public function tools( $tools ) {

		$add_tools = array(
			'reset_multicurrency_settings' => array(
				'name'     => \__( 'Reset Paywall settings', 'paywall-for-woocommerce' ),
				'button'   => \__( 'Reset', 'paywall-for-woocommerce' ),
				'desc'     => sprintf(
					'<strong class="red">%1$s</strong> %2$s',
					\__( 'Note:', 'woocommerce' ),
					\__( 'This tool will reset all Paywall settings to default. This action cannot be reversed.', 'paywall-for-woocommerce' )
				),
				'callback' => array( $this, 'reset_all_settings' ),
			),
		);

		return array_merge( $tools, $add_tools );
	}

	/**
	 * Delete all options from the database.
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function reset_all_settings() {

		/**
		 * WPDB.
		 *
		 * @global  \wpdb $wpdb
		 */
		global $wpdb;

		$option_like = SettingsController::make_option_key( '%' );

		/**
		 * Silence.
		 *
		 * @noinspection SqlResolve
		 */
		$rows_affected = $wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->options WHERE option_name LIKE %s", $option_like ) );

		if ( false === $rows_affected ) {
			$message = \esc_html__( 'There was an error resetting Paywall (or it was already reset).', 'paywall-for-woocommerce' );
		} else {
			$message = sprintf( /* Translators: %d - number of records */
				\esc_html__( 'Paywall settings have been reset. Number of records deleted: %d.', 'paywall-for-woocommerce' ),
				$rows_affected
			);
		}

		return $message;
	}
}

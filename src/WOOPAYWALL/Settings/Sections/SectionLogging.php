<?php
/**
 * Settings section "SectionLogging".
 *
 * @since 3.2.0
 * Copyright (c) 2021, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Log;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionLogging
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionLogging extends AbstractSection {

	/**
	 * Section ID.
	 *
	 * @var string
	 */
	const ID = 'logging';

	/**
	 * Option key.
	 *
	 * @var string
	 */
	const OPTION_LOG_LEVEL = 'log_level';

	/**
	 * Constructor.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {

		$this->set_id( self::ID );
		$this->set_title( \__( 'Logging settings', 'paywall-for-woocommerce' ) );

		$this->set_priority( Controller::get_priority( $this->get_id() ) );
	}

	/**
	 * Add fields to the section.
	 *
	 * @param array $settings The "All Settings" array.
	 *
	 * @noinspection PhpUnused
	 */
	protected function add_fields( &$settings ) {

		$link_view_logs =
			'<p style="font-style: normal; padding-left: 8px">' .
			'<a class="tivwp-external-link" href="' . \admin_url( 'admin.php?page=wc-status&tab=logs' ) . '">' .
			\esc_html__( 'View logs', 'paywall-for-woocommerce' ) .
			'</a>' .
			'</p>';

		$settings[] = array(
			'id'       => Controller::make_option_key( self::OPTION_LOG_LEVEL ),
			'title'    => \esc_html__( 'Log level', 'paywall-for-woocommerce' ),
			'desc'     => '<br>' . $link_view_logs,
			'type'     => 'select',
			'css'      => 'min-width:350px;',
			'class'    => 'wc-enhanced-select',
			'default'  => Controller::get_option( self::OPTION_LOG_LEVEL, \WC_Log_Levels::ERROR ),
			'desc_tip' => \esc_html__( 'What to write to the log file.', 'paywall-for-woocommerce' ),
			'options'  => array(
				Log::LOG_LEVEL_NONE   => \esc_html__( 'Nothing', 'paywall-for-woocommerce' ),
				\WC_Log_Levels::ERROR => \esc_html__( 'Error conditions', 'paywall-for-woocommerce' ),
				\WC_Log_Levels::INFO  => \esc_html__( 'Informational messages', 'paywall-for-woocommerce' ),
				\WC_Log_Levels::DEBUG => \esc_html__( 'Debug-level messages', 'paywall-for-woocommerce' ),
				Log::LOG_LEVEL_TRACE  => \esc_html__( 'Debug with tracing', 'paywall-for-woocommerce' ),
			),
		);
	}
}

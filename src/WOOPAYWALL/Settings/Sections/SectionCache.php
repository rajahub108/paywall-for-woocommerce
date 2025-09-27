<?php
/**
 * Settings section "SectionCache".
 *
 * @since 3.4.1
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Cache\CacheAbstract;
use WOOPAYWALL\Dependencies\TIVWP\Constants;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionCache
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionCache extends AbstractSection {

	/**
	 * Section ID.
	 *
	 * @var string
	 */
	const ID = 'cache';

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->set_id( self::ID );
		$this->set_title( \__( 'Cache', 'paywall-for-woocommerce' ) );

		$this->set_priority( Controller::get_priority( $this->get_id() ) );
	}

	/**
	 * Add fields to the section.
	 *
	 * @param array $settings The "All Settings" array.
	 */
	protected function add_fields( &$settings ) {

		$desc = \__( 'If a page caching is used, cache buster is needed for not logged-in visitors to see their versions of the pages, and not cached by someone else.', 'paywall-for-woocommerce' );
		if ( Constants::is_true( 'WP_CACHE' ) ) {
			$desc .=
				'<p class="wp-ui-text-notification">' .
				\__( 'The WP_CACHE constant is set. Please check the page caching settings. You might need the cache buster for Paywall to operate correctly.', 'paywall-for-woocommerce' ) .
				'</p>';
		}

		$settings[] = array(
			'id'      => Controller::make_option_key( CacheAbstract::OPTION_CACHE_BUSTER_ENABLED ),
			'title'   => \__( 'Enable cache buster?', 'paywall-for-woocommerce' ),
			'desc'    => $desc,
			'type'    => 'checkbox',
			'default' => CacheAbstract::OPTION_CACHE_BUSTER_ENABLED_DEFAULT,
		);
	}
}

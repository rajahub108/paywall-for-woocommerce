<?php
/**
 * Settings section "Intro".
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\App;
use WOOPAYWALL\Settings\Controller;

/**
 * Class SectionIntro
 *
 * @package WOOPAYWALL\Settings\Sections
 */
class SectionIntro extends AbstractSection {

	/**
	 * Constructor.
	 *
	 * @noinspection PhpUnused
	 */
	public function __construct() {

		$this->set_id( 'intro' );

		$plugin_data = \get_plugin_data( App::instance()->plugin_file );

		$section_title = implode( ' ', array(
			$plugin_data['Name'],
			$plugin_data['Version'],
			/* translators: %s: Plugin author name. */
			sprintf( \__( 'by %s' ), $plugin_data['AuthorName'] ),
		) );

		$this->set_title( $section_title );

		$loco_translate_url = \add_query_arg( array(
			's'    => 'Loco+Translate',
			'tab'  => 'search',
			'type' => 'term',
		), \admin_url( 'plugin-install.php' ) );

		$desc = implode(
			' <br/>',
			array(
				'<div class="howto">' .
				\__( 'Thank you for installing the Paywall extension! We appreciate your business!', 'paywall-for-woocommerce' ),
				sprintf( /* Translators: placeholders for HTML "a" tag linking 'here' to the Support page. */
					\__( 'Please configure the settings using the instructions below. Should you need help, please contact our technical support by clicking %1$shere%2$s.', 'paywall-for-woocommerce' ),
					'<a class="tivwp-external-link" href="' . App::instance()->getUrlSupport() . '">',
					'</a>'
				),
				'',
				sprintf( /* Translators: placeholders for HTML "a" tag linking 'here' to the Support page. */
					__( 'To change the texts that Paywall extension outputs, or to translate them to another language, you can use %1$sLoco Translate%2$s or a similar plugin.', 'paywall-for-woocommerce' ),
					'<a class="tivwp-external-link" href="' . $loco_translate_url . '">',
					'</a>'
				),
				'</div>',
			)
		);
		$this->set_desc( $desc );

		$this->set_priority( Controller::get_priority( $this->get_id() ) );
	}
}

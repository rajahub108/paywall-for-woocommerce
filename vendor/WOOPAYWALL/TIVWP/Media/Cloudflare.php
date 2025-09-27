<?php
/**
 * Embed Cloudflare streams.
 *
 * @since   1.4.0
 * @link    https://developers.cloudflare.com/stream/player-and-playback/player-embed
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;

/**
 * Class Cloudflare
 *
 * @since   1.4.0
 */
class Cloudflare extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @since   1.4.0
	 *
	 * @var string
	 */
	const TYPE = 'cloudflare';

	/**
	 * Is this my type of the URL?
	 *
	 * @since   1.4.0
	 *
	 * @param string $url The URL.
	 *
	 * @return bool
	 */
	public static function is_my_url( $url ) {
		return false !== stripos( $url, '.videodelivery.net' );
	}

	/**
	 * Return sanitized URL.
	 *
	 * @since   1.4.0
	 *
	 * @return string
	 */
	public function get_sanitized_url() {

		$url = $this->getUrl();

		/**
		 * Replace https://watch.videodelivery.net/...
		 * with https://iframe.videodelivery.net/...
		 */
		$url = str_ireplace( 'watch.', 'iframe.', $url );

		// https://developers.cloudflare.com/stream/player-and-playback/player-embed#basic-options
		$params = array(
			'autoplay' => '0',
			'controls' => '1',
			'loop'     => '0',
			'muted'    => '0',
			'preload'  => 'none',
		);

		/**
		 * Filter to adjust the Cloudflare URL parameters.
		 *
		 * @since   1.4.0
		 * @param string[] $params The parameters.
		 */
		$params = \apply_filters( 'tivwp_cloudflare_url_parameters', $params );

		// Remove empty parameters. Cloudflare only checks for the parameter presence, not its value.
		$params = array_filter( $params );

		return \add_query_arg( $params, $url );
	}

	/**
	 * Default style for the embed HTML.
	 *
	 * @since   1.4.0
	 * @return string
	 */
	public function get_css() {
		return parent::get_css() . 'height:100%;position:absolute;top:0;left:0;';
	}

	/**
	 * Generate embed HTML.
	 *
	 * @since   1.4.0
	 * @return string The HTML.
	 */
	public function get_embed_html() {
		$this->load_js();

		return HTML::make_tag(
			'div',
			array(
				'class'      => 'tivwp-media fitvidsignore',
				'style'      => 'padding:56.25% 0 0 0;position:relative',
				'data-class' => $this->get_css_class(),
				'data-type'  => $this->get_type(),
				'data-css'   => $this->get_css(),
				'data-url'   => $this->get_sanitized_url(),
				'data-allow' => 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture',
			),
			$this->msg_loading()
		);
	}
}

<?php
/**
 * Generic oEmbed.
 *
 * @since 1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;

/**
 * Class Generic
 */
class Generic extends AbstractMedia {

	/**
	 * Type of the media.
	 */
	const TYPE = 'generic';

	/**
	 * Default style for the embed HTML.
	 *
	 * @since   1.12.1
	 * @return string
	 */
	public function get_css() {
		return parent::get_css() . 'position:absolute;top:0;left:0;';
	}

	/**
	 * Generate embed HTML.
	 *
	 * @return string The HTML.
	 *
	 * List of providers {@see \WP_oEmbed::__construct}
	 */
	public function get_embed_html() {

		$url = $this->getUrl();

		$embed_html = \wp_oembed_get( $url );

		$this->load_js();

		$div_args = array(
			'class'      => 'tivwp-media fitvidsignore',
			'style'      => 'padding:56.25% 0 0 0;position:relative',
			'data-class' => $this->get_css_class(),
			'data-type'  => $this->get_type(),
			'data-css'   => $this->get_css(),
		);

		if ( $embed_html ) {
			$div_args['data-embed'] = $embed_html;

			if ( preg_match( '#https?://(www\.)?tiktok\.com/.*/video/.*#i', $url ) ) {
				// For TikTok, we only need to remove their script from oEmbed and launch it ourselves.
				$this->load_helper_js( 'tiktok', 'https://www.tiktok.com/embed.js' );

				return HTML::replace_tag_in_string( $embed_html, 'script' );

			}

		} else {
			$div_args['data-url'] = $this->getUrl();
		}

		return HTML::make_tag(
			'div',
			$div_args,
			$this->msg_loading()
		);
	}

	protected function load_helper_js( $id, $url ) {
		// PHPCS does not like nulls in enqueue.
		static $ver = null;

		\wp_enqueue_script( "tivwp-embed-$id", $url, array(), $ver, true );
	}
}

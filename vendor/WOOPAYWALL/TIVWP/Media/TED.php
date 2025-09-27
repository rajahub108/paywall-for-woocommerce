<?php
/**
 * TED
 *
 * @since 1.12.1
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;

/**
 * Class TED
 *
 * @since 1.12.1
 */
class TED extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @since   1.12.1
	 *
	 * @var string
	 */
	const TYPE = 'ted';

	/**
	 * URL for embedding
	 *
	 * @since 1.12.1
	 * @var string
	 */
	const EMBED_URL = 'https://embed.ted.com/';

	/**
	 * Override is_my_url().
	 *
	 * @since 1.12.1
	 * @inheritDoc
	 */
	public static function is_my_url( $url ) {
		return preg_match( '#//(www\.|embed\.)?ted\.com/talks/.*#i', $url );
	}

	/**
	 * Override get_sanitized_url().
	 *
	 * @since 1.12.1
	 * @inheritDoc
	 */
	public function get_sanitized_url() {
		return preg_replace( '#.*ted\.com/#i', self::EMBED_URL, $this->getUrl() );
	}

	/**
	 * Default style for the embed HTML.
	 *
	 * @since   1.12.1
	 * @return string
	 */
	public function get_css() {
		return parent::get_css() . 'height:100%;position:absolute;top:0;left:0;';
	}

	/**
	 * Generate embed HTML.
	 *
	 * @since   1.12.1
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
			),
			$this->msg_loading()
		);
	}

}

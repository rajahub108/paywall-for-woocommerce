<?php
/**
 * Issuu
 *
 * @since 1.12.1
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Log;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;

/**
 * Class Issuu
 *
 * @since 1.12.1
 */
class Issuu extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @since   1.12.1
	 *
	 * @var string
	 */
	const TYPE = 'issuu';

	/**
	 * URL for embedding
	 * Example: https://e.issuu.com/embed.html?u=rwpzoo&d=wild_april_2023_2&p=1
	 *
	 * @since 1.12.1
	 * @var string
	 */
	const EMBED_URL = 'https://e.issuu.com/embed.html';

	/**
	 * Override is_my_url().
	 *
	 * @since 1.12.1
	 * @inheritDoc
	 */
	public static function is_my_url( $url ) {
		return preg_match( '#https?://(www\.)?issuu\.com/.+/docs/.+#i', $url );
	}

	/**
	 * Override get_sanitized_url().
	 * Example: https://issuu.com/rwpzoo/docs/wild_april_2023_2
	 * https://issuu.com/rwpzoo/docs/wild_april_2023_2/3 - starting from page 3
	 *
	 * @since 1.12.1
	 * @inheritDoc
	 */
	public function get_sanitized_url() {
		$url = $this->getUrl();
		$url = filter_var( $url, FILTER_SANITIZE_URL );
		$url = filter_var( $url, FILTER_VALIDATE_URL );

		if ( ! preg_match( '#issuu.com/(.+)/docs/([^/]+)(?:/(\d+))?#', $url, $matches ) ) {
			Log::error( new Message( array( 'Invalid media URL', $url ) ) );

			return '';
		}

		$params = array(
			'u' => $matches[1],
			'd' => $matches[2],
			'hideIssuuLogo'=>'true',
			'hideShareButton'=>'true',
		);
		if ( isset( $matches[3] ) ) {
			$params['pageNumber'] = $matches[3];
		}

		/**
		 * Filter to adjust the Issuu URL parameters.
		 *
		 * @since   1.12.1
		 *
		 * @param string[] $params The parameters.
		 */
		$params = \apply_filters( 'tivwp_issuu_url_parameters', $params );

		return \add_query_arg( $params, self::EMBED_URL );
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

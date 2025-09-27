<?php
/**
 * Embed YouTube.
 *
 * @since   1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Log;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;

/**
 * Class YouTube
 *
 * @since   1.0.0
 */
class YouTube extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @since   1.0.0
	 *
	 * @var string
	 */
	const TYPE = 'youtube';

	/**
	 * YouTube short URL domain.
	 *
	 * @since 1.1.0
	 *
	 * @var string
	 */
	const SHORT_URL_DOMAIN = 'youtu.be';

	/**
	 * YouTube URL for embedding.
	 *
	 * @since 1.1.0
	 * @since 1.12.0 Removed 'no-cookie' to support '?start='; Use as URL, not domain.
	 *
	 * @var string
	 */
	const EMBED_URL = 'https://www.youtube.com/embed/';

	/**
	 * Is this a short YouTube URL?
	 *
	 * @since 1.1.0
	 *
	 * @param string $url The URL.
	 *
	 * @return bool
	 */
	protected static function is_short_url( $url ) {
		return false !== stripos( $url, self::SHORT_URL_DOMAIN );
	}

	/**
	 * Is this my type of the URL?
	 *
	 * @since   1.0.0
	 *
	 * @param string $url The URL.
	 *
	 * @return bool
	 */
	public static function is_my_url( $url ) {
		return false !== stripos( $url, self::TYPE ) || self::is_short_url( $url );
	}

	/**
	 * Return type of the media.
	 *
	 * @since   1.0.0
	 *
	 * @return string
	 */
	public function get_type() {
		return self::TYPE;
	}

	/**
	 * Return sanitized URL.
	 *
	 * @since   1.0.0
	 * @since   1.1.0 Handle YouTube short URLs.
	 * @since   1.12.0 Handle start parameter and "shorts" videos.
	 * @return string
	 */
	public function get_sanitized_url() {

		$url = $this->getUrl();
		$url = filter_var( $url, FILTER_SANITIZE_URL );
		$url = filter_var( $url, FILTER_VALIDATE_URL );
		if ( ! $url ) {
			Log::error( new Message( array( 'Invalid media URL', $url ) ) );

			return '';
		}

		$parsed_url = parse_url( $url );
		$path       = $parsed_url['path'];
		$params     = array();
		$video_id   = '';

		if ( isset( $parsed_url['query'] ) ) {
			$query = $parsed_url['query'];
			parse_str( $query, $params );

			if ( isset( $params['t'] ) ) {
				// In embeds, '?t=' becomes '?start='.
				$params['start'] = $params['t'];
				unset( $params['t'] );
			}

			if ( isset( $params['index'] ) ) {
				// Remove 'index' from playlist.
				// https://www.youtube.com/watch?v=XXXXX&list=XXXXX&index=3
				unset( $params['index'] );
			}
		}

		if ( isset( $params['v'] ) ) {
			// https://www.youtube.com/watch?v=6UHkpR_fDHo&t=30
			$video_id = $params['v'];
			unset( $params['v'] );
		} elseif ( preg_match( '/^\/(embed|shorts)\/(.+)/', $path, $matches ) ) {
			// https://www.youtube.com/embed/6UHkpR_fDHo?start=30
			// https://www.youtube.com/shorts/-DqaBgnMra0
			$video_id = $matches[2];
		} elseif ( self::is_short_url( $url ) ) {
			// https://youtu.be/6UHkpR_fDHo
			$video_id = ltrim( $path, '/' );
		}

		$url = self::EMBED_URL . $video_id;

		// https://developers.google.com/youtube/player_parameters
		$params['modestbranding'] = '1';
		$params['playsinline']    = '0';

		/**
		 * Filter to adjust the YouTube URL parameters.
		 *
		 * @since   1.12.1
		 *
		 * @param string[] $params The parameters.
		 */
		$params = \apply_filters( 'tivwp_youtube_url_parameters', $params );

		return \add_query_arg( $params, $url );
	}

	/**
	 * Default style for the embed HTML.
	 *
	 * @since   1.0.0
	 * @return string
	 */
	public function get_css() {
		return parent::get_css() . 'height:100%;position:absolute;top:0;left:0;';
	}

	/**
	 * Generate embed HTML.
	 *
	 * @since   1.0.0
	 * @since   1.6.0 Added `fitvidsignore class.
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
				'data-allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share',
			),
			$this->msg_loading()
		);
	}

}

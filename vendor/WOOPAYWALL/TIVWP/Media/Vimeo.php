<?php
/**
 * Embed Vimeo.
 *
 * @since   1.1.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 *
 * @todo    Can use WP own viewer:
 * <code>
 *    \add_filter( 'wp_video_shortcode', function ( $output, $atts, $video, $post_id, $library ) {
 *        $output = str_replace( '?loop=0&#038;_=1', '?loop=0&#038;_=1&portrait=0&title=0', $output );
 *
 *        return $output;
 *    }, 10, 5 );
 *
 * // In get_embed_html() :
 * return \wp_video_shortcode( array(
 *    'src' => 'https://vimeo.com/424357917',
 * ) );
 *      </code>
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;

/**
 * Class Vimeo
 */
class Vimeo extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @since   1.1.0
	 *
	 * @var string
	 */
	const TYPE = 'vimeo';

	/**
	 * Is this my type of the URL?
	 *
	 * @since   1.1.0
	 *
	 * @param string $url The URL.
	 *
	 * @return bool
	 */
	public static function is_my_url( $url ) {
		return false !== stripos( $url, self::TYPE );
	}

	/**
	 * Return sanitized URL.
	 *
	 * @since   1.1.0
	 *
	 * @return string
	 */
	public function get_sanitized_url() {

		$url = $this->getUrl();

		/**
		 * Replace https://vimeo.com/424357917
		 * with https://player.vimeo.com/video/424357917
		 *
		 * @since 1.1.1 Regex adjusted to match only URLs with trailing \d+
		 *        so the URLs like `https://vimeo.com/event/244910/embed` are not changed.
		 */
		$re    = '~https?://(?:www\.)?vimeo\.com/(\d+)~i';
		$subst = 'https://player.vimeo.com/video/\\1';
		$url   = preg_replace( $re, $subst, $url );


		// https://vimeo.zendesk.com/hc/en-us/articles/360001494447-Using-Player-Parameters
		// quality = [240p, 360p, 540p, 720p, 1080p, 2k, 4k, auto]
		$params = array(
			'autopause'   => '1',
			'autoplay'    => '0',
			'background'  => '0',
			'byline'      => '0', // 1
			'color'       => '00adef',
			'controls'    => '1',
			'dnt'         => '0',
			'fun'         => '0',
			'loop'        => '0',
			'muted'       => '0',
			'playsinline' => '0',
			'portrait'    => '0', // 1
			'quality'     => 'auto',
			'speed'       => '0',
			'title'       => '0', // 1
			'transparent' => '1',
		);

		/**
		 * Filter to adjust the Vimeo URL parameters.
		 *
		 * @since   1.1.0
		 *
		 * @param string[] $params The parameters.
		 */
		$params = \apply_filters( 'tivwp_vimeo_url_parameters', $params );

		return \add_query_arg( $params, $url );
	}

	/**
	 * Default style for the embed HTML.
	 *
	 * @since   1.1.0
	 *
	 * @return string
	 */
	public function get_css() {
		return parent::get_css() . 'height:100%;position:absolute;top:0;left:0;';
	}

	/**
	 * Generate embed HTML.
	 *
	 * @since   1.1.0
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
			),
			$this->msg_loading()
		);
	}

}

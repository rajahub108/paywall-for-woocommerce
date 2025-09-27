<?php
/**
 * URLs to video files (mp4, etc.)
 *
 * @since 1.1.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;

/**
 * Class Video
 *
 * @since 1.1.0
 */
class Video extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	const TYPE = 'video';

	/**
	 * My URL extensions.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	const EXT = array(
		'flv',
		'm4v',
		'mov',
		'mp4',
		'mpeg',
		'oga',
		'ogg',
		'ogv',
		'ogv',
		'webm',
		'webma',
		'webmv',
	);

	/**
	 * Generate embed HTML.
	 *
	 * @since 1.1.0
	 * @since 1.12.3 Use "video" HTML tag instead of `wp_video_shortcode`.
	 *
	 * @return string The HTML.
	 */
	public function get_embed_html() {
		$this->load_js();

		$video_attributes_default = array(
			'controls' => '1',
			'class'    => $this->get_css_class(),
			'preload'  => 'metadata',
			'style'    => 'position:absolute;width:100%;top:0;left:0;z-index:99998',
		);

		/**
		 * Filter to adjust the Video attributes.
		 *
		 * @since   1.13.0
		 *
		 * @param string[] $video_attributes_default The default attributes.
		 */
		$video_attributes = \apply_filters( 'tivwp_video_attributes', $video_attributes_default );

		$video_attributes['src'] = $this->getUrl();

		return HTML::make_tag(
			'div',
			array(
				'class' => 'tivwp-video fitvidsignore',
				'style' => 'padding:56.25% 0 0 0;position:relative',
			),
			HTML::make_tag(
				'video',
				$video_attributes
			) );
	}
}

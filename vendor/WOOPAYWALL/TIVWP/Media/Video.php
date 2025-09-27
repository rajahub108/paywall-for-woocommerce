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

		return HTML::make_tag(
			'div',
			array(
				'class' => 'tivwp-media fitvidsignore',
				'style' => 'padding:56.25% 0 0 0;position:relative',
			),
			HTML::make_tag(
				'video',
				array(
					'controls' => '1',
					'src'      => $this->getUrl(),
					'class'    => $this->get_css_class(),
					'style'    => 'position:absolute;width:100%;top:0;left:0;z-index:99998',
				)
			) );

	}
}

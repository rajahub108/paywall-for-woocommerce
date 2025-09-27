<?php
/**
 * URLs to audio files (mp3, etc.)
 *
 * @since   1.4.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;

/**
 * Class Audio
 */
class Audio extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @var string
	 */
	const TYPE = 'audio';

	/**
	 * My URL extensions.
	 *
	 * @see wp_get_ext_types
	 * @see wp_get_audio_extensions
	 * @var string
	 */
	const EXT = array(
		'aac',
		'ac3',
		'aif',
		'aiff',
		'flac',
		'm3a',
		'm4a',
		'm4b',
		'mka',
		'mp1',
		'mp2',
		'mp3',
		'ogg',
		'oga',
		'ram',
		'wav',
		'wma',
	);

	/**
	 * Generate embed HTML.
	 *
	 * @since   1.4.0
	 * @since   1.12.4 Use "audio" HTML tag instead of `wp_video_shortcode`.
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
				'audio',
				array(
					'controls' => '1',
					'src'      => $this->getUrl(),
					'class'    => $this->get_css_class(),
					'style'    => 'position:absolute;width:100%;top:0;left:0;z-index:99998',
				)
			) );
	}
}

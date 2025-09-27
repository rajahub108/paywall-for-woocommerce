<?php
/**
 * Image media.
 *
 * @since   1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Media;

use WOOPAYWALL\Dependencies\TIVWP\HTML;

/**
 * Class Image
 *
 * @since   1.0.0
 */
class Image extends AbstractMedia {

	/**
	 * Type of the media.
	 *
	 * @since   1.0.0
	 */
	const TYPE = 'image';

	/**
	 * Is this my type of the URL?
	 *
	 * @since   1.0.0
	 * @param string $url The URL.
	 *
	 * @return bool
	 */
	public static function is_my_url( $url ) {

		/**
		 * Image types.
		 *
		 * @link https://developer.mozilla.org/en-US/docs/Web/Media/Formats/Image_types
		 */
		$image_extensions = array(
			'apng',
			'bmp',
			'gif',
			'jpg',
			'jpe',
			'jpeg',
			'jif',
			'jfif',
			'pjpeg',
			'pjp',
			'png',
			'svg',
			'webp',
		);
		$url_path         = \wp_parse_url( $url, PHP_URL_PATH );
		$pathinfo         = pathinfo( $url_path );

		return isset( $pathinfo['extension'] ) && in_array( $pathinfo['extension'], $image_extensions, true );
	}

	/**
	 * Generate embed HTML.
	 *
	 * @since   1.0.0
	 *
	 * @return string The HTML.
	 */
	public function get_embed_html() {

		/**
		 * Global Post.
		 *
		 * @global \WP_Post $post
		 */
		global $post;

		$img_attributes = array(
			'alt'   => $post ? $post->post_title : '',
			'id'    => $this->getId(),
			'src'   => $this->getUrl(),
			'class' => 'wp-post-image',
		);

		/**
		 * Filter for 3rd parties to alter the image attributes.
		 *
		 * @since   1.0.0
		 *
		 * @param array $img_attributes The attributes.
		 */
		$img_attributes = \apply_filters( 'tivwp_media_image_attributes', $img_attributes );

		$img_tag = HTML::make_tag( 'img', $img_attributes );

		$link_attributes = array(
			'href'   => $this->getUrl(),
			'target' => '_',
		);

		/**
		 * Filter for 3rd parties to alter the image link attributes.
		 *
		 * @since   1.0.0
		 * @param array $link_attributes_attributes The attributes.
		 */
		$link_attributes = \apply_filters( 'tivwp_media_image_link_attributes', $link_attributes );

		return HTML::make_tag( 'a', $link_attributes, $img_tag );
	}
}

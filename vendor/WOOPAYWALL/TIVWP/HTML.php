<?php
/**
 * HTML
 *
 * @since 1.12.1
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP;

/**
 * Class HTML
 *
 * @since 1.12.1
 */
class HTML {

	/**
	 * Generate attribute for HTML tag.
	 *
	 * @since 1.12.1
	 *
	 * @param string $name  Attribute name.
	 * @param string $value Attribute value.
	 *
	 * @return string
	 */
	public static function make_tag_attribute( $name, $value ) {
		return ' ' . \sanitize_key( $name ) . '="' . \esc_attr( $value ) . '"';
	}

	/**
	 * Generate HTML tag.
	 *
	 * @since        1.12.1
	 *
	 * @param string $tag_name   Tag name.
	 * @param array  $attributes List of Attributes.
	 * @param string $content    Content (eg between <h1> and </h1>).
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public static function make_tag( $tag_name, $attributes = array(), $content = '' ) {
		$tag = '<' . $tag_name;
		ksort( $attributes );
		foreach ( $attributes as $name => $value ) {
			$tag .= self::make_tag_attribute( $name, $value );
		}
		$tag .= $content ? '>' . $content . '</' . $tag_name . '>' : '/>';

		return $tag;
	}

	/**
	 * Replace HTML tag in a string.
	 *
	 * @since 1.12.1
	 *
	 * @param string $sz          String containing the tag.
	 * @param string $tag         Tag to replace.
	 * @param string $replacement Replacement (default='')
	 *
	 * @return array|string|string[]|null
	 */
	public static function replace_tag_in_string( $sz, $tag, $replacement = '' ) {
		return preg_replace( "/<$tag(.*)<\\/$tag>/", $replacement, $sz );
	}
}

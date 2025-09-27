<?php
/**
 * Template controller.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Template;

use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;
use WOOPAYWALL\Log;

/**
 * Class Controller
 *
 * @package WOOPAYWALL\Template
 */
class Controller {

	/**
	 * Retrieve the name of the highest priority template file that exists and require it.
	 *
	 * @param string $name           Template file name.
	 * @param array  $args           Optional. Additional arguments passed to the template.
	 *                               Default empty array.
	 * @param bool   $load_once      Whether to require_once or require. Default true.
	 *
	 * @return void
	 */
	public static function load( $name, $args = array(), $load_once = true ) {

		// Try locating template overrides.
		$template_file = \locate_template( array( $name ) );
		if ( ! $template_file ) {
			// If no overrides, use the internal template.
			$template_file = __DIR__ . DIRECTORY_SEPARATOR . $name;
		}

		if ( is_file( $template_file ) && is_readable( $template_file ) ) {
			\load_template( $template_file, $load_once, $args );
		} else {
			Log::error( new Message( array( 'Cannot load template', $template_file ) ) );
		}
	}

	/**
	 * Wraps {@see load()} in ob_ buffering and return the template-generated content.
	 *
	 * @param string $name           Template file name.
	 * @param array  $args           Optional. Additional arguments passed to the template.
	 *                               Default empty array.
	 * @param bool   $load_once      Whether to require_once or require. Default true.
	 *
	 * @return string
	 */
	public static function get_content( $name, $args = array(), $load_once = true ) {
		ob_start();
		self::load( $name, $args, $load_once );

		return ob_get_clean();
	}
}

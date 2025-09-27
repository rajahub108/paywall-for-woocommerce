<?php
/**
 * TinyMCEController
 *
 * @since 3.11.0
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\TinyMCE;

use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class TinyMCEController
 *
 * @since 3.11.0
 */
class TinyMCEController extends Hookable {

	/**
	 * Implement setup_hooks().
	 *
	 * @since 3.11.0
	 * @return void
	 */
	public function setup_hooks() {
		$this->buttons_init();
	}

	/**
	 * Add custom buttons in TinyMCE.
	 *
	 * @since 3.11.0
	 *
	 * @param array $mce_buttons First-row list of buttons.
	 */
	public function register_buttons( $mce_buttons ) {
		array_push( $mce_buttons, '|', 'woopw_show', 'woopw_hide' );

		return $mce_buttons;
	}

	/**
	 * Register button scripts.
	 *
	 * @since 3.11.0
	 *
	 * @param array $external_plugins An array of external TinyMCE plugins.
	 */
	public function add_buttons( $external_plugins ) {
		$external_plugins['woopw'] = \plugins_url( 'woopw_mce.min.js', __FILE__ );

		return $external_plugins;
	}

	/**
	 * Register buttons in init.
	 */
	public function buttons_init() {
		if ( ! \current_user_can( 'edit_posts' ) && ! \current_user_can( 'edit_pages' ) ) {
			return;
		}

		if ( 'true' === \get_user_option( 'rich_editing' ) ) {
			\add_filter( 'mce_external_plugins', array( $this, 'add_buttons' ) );
			\add_filter( 'mce_buttons', array( $this, 'register_buttons' ) );
		}
	}
}

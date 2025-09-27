<?php
/**
 * AdminPreview
 *
 * @since 4.0.0
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL;

use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class AdminPreview
 *
 * @since 4.0.0
 */
class AdminPreview extends Hookable {

	/**
	 * Admin preview query parameter.
	 *
	 * @since 2.2.0-beta.1
	 *
	 * @var string
	 */
	const ADMIN_PREVIEW_QUERY = 'paywall_status';

	/**
	 * Returns true if we are in the admin preview mode.
	 *
	 * @since 2.2.0-beta.1
	 * @since 3.10.0 Added null as a neutral value.
	 * @return bool|null
	 */
	public static function is_set() {

		// Only admins.
		if ( ! \current_user_can( 'manage_options' ) ) {
			return null;
		}

		/**
		 * The WP global.
		 *
		 * @global \WP $wp
		 */
		global $wp;

		if ( isset( $wp->query_vars[ self::ADMIN_PREVIEW_QUERY ] ) ) {
			return 'paid' === $wp->query_vars[ self::ADMIN_PREVIEW_QUERY ];
		}

		return null;
	}

	/**
	 * Filters the query variables allowed before processing.
	 * Add ADMIN_PREVIEW_QUERY to the query vars.
	 *
	 * @since 2.2.0-beta.1
	 *
	 * @param string[] $public_query_vars The array of allowed query variable names.
	 *
	 * @return string[]
	 */
	public function filter__query_vars( $public_query_vars ) {
		$public_query_vars[] = self::ADMIN_PREVIEW_QUERY;

		return $public_query_vars;
	}

	/**
	 * Implement setup_hooks.
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	public function setup_hooks() {

		\add_filter(
			'query_vars',
			array( $this, 'filter__query_vars' )
		);
	}
}

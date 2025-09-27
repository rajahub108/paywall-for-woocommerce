<?php
/**
 * AdminBar
 *
 * @since 3.10.0
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL;

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class AdminBar
 *
 * @since 3.10.0
 */
class AdminBar extends Hookable {

	/**
	 * Implement setup_hooks
	 *
	 * @inheritDoc
	 * @since 3.10.0
	 */
	public function setup_hooks() {
		\add_action( 'admin_bar_menu', array( $this, 'action__admin_bar_menu' ), App::HOOK_PRIORITY_LATE );
	}

	/**
	 * Method custom_admin_bar_menu.
	 *
	 * @since 3.10.0
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar The WP_Admin_Bar instance, passed by reference.
	 *
	 * @return void
	 */
	public function action__admin_bar_menu( $wp_admin_bar ) {

		// Only admins.
		if ( ! \current_user_can( 'manage_options' ) ) {
			return;
		}

		// Only on single product pages.
		if ( ! \is_product() ) {
			return;
		}

		// Only for Paywall products.
		global $product;
		if ( ! $product instanceof AbstractPaywallProductSimple ) {
			return;
		}

		// Add a parent menu item
		$wp_admin_bar->add_menu( array(
			'id'    => 'paywall',
			'title' => \__( 'Paywall', 'paywall-for-woocommerce' ),
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'paywall-view-paid',
			'parent' => 'paywall',
			'title'  => \__( 'Admin view as Paid', 'paywall-for-woocommerce' ),
			'href'   => \add_query_arg( AdminPreview::ADMIN_PREVIEW_QUERY, 'paid' ),
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'paywall-view-unpaid',
			'parent' => 'paywall',
			'title'  => \__( 'Admin view as Unpaid', 'paywall-for-woocommerce' ),
			'href'   => \add_query_arg( AdminPreview::ADMIN_PREVIEW_QUERY, 'unpaid' ),
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'paywall-view-reset',
			'parent' => 'paywall',
			'title'  => \__( 'Reset Admin view', 'paywall-for-woocommerce' ),
			'href'   => \remove_query_arg( AdminPreview::ADMIN_PREVIEW_QUERY ),
		) );
	}
}

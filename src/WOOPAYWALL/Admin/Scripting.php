<?php
/**
 * Admin scripting.
 *
 * @since   1.0.0
 * @package WOOPAYWALL\Admin
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Admin;

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;

/**
 * Class Scripting
 */
class Scripting extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		foreach ( array( 'post-new.php', 'post.php', 'edit.php' ) as $hook_suffix ) {
			\add_action( "admin_print_scripts-$hook_suffix", array( $this, 'js_globals' ) );
		}

		$hook_suffix = 'edit.php';
		\add_action( "admin_print_footer_scripts-$hook_suffix", array( $this, 'js_admin_quick_edit' ) );
	}

	/**
	 * Insert global var(s) to be used by our JS scripts.
	 * <code>
	 * var WC_Product_Paywall = {"product_type":"paywall"};
	 * </code>
	 * $post_type = 'product'
	 * $pagenow = 'post.php'
	 * $action = 'edit'
	 *
	 * @return void
	 */
	public function js_globals() {
		/**
		 * Globals.
		 *
		 * @var \WP_Post $post
		 * @var string   $action
		 */
		global $post, $action;
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		$data = array(
			'post_type'         => $post->post_type,
			'product_type'      => '',
			'action'            => $action ?? '',
			'is_our_object'     => false,
			'our_product_types' => array( \WC_Product_Paywall::PRODUCT_TYPE, \WC_Product_Pwpass::PRODUCT_TYPE ),
		);

		if ( 'product' === $post->post_type ) {
			$product = \wc_get_product( $post );
			if ( $product instanceof \WC_Product ) {
				$data['product_type']  = $product->get_type();
				$data['is_our_object'] = $product instanceof AbstractPaywallProductSimple;
			}
		}

		\wp_localize_script(
			'woocommerce_admin',
			'WC_Product_Paywall',
			$data
		);
	}

	/**
	 * Hide useless Quick Edit fields.
	 *
	 * @note         The JS must be embedded.
	 *
	 * @return void
	 * @todo         Add our fields.
	 */
	public function js_admin_quick_edit() {
		\wp_enqueue_script(
			'woopaywall-quick-edit',
			App::instance()->plugin_dir_url() . 'assets/js/quick-edit.min.js',
			array( 'jquery' ),
			App::$VER,
			false
		);
	}
}

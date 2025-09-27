<?php
/**
 * Shortcodes.
 *
 * @since 1.1.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;
use WOOPAYWALL\Template;

/**
 * Class Frontend\Shortcode
 */
class Shortcode extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_shortcode(
			'woopaywall_hide_after_purchase',
			array( $this, 'shortcode__woopaywall_hide_after_purchase' )
		);

		\add_shortcode(
			'woopaywall_show_after_purchase',
			array( $this, 'shortcode__woopaywall_show_after_purchase' )
		);

		\add_shortcode(
			'woopaywall_purchased_products',
			array( $this, 'shortcode__woopaywall_purchased_products' )
		);

		0 && \add_shortcode(
			'woopaywall_cache_buster',
			array( $this, 'shortcode__woopaywall_cache_buster' )
		);
	}

	/**
	 * Shortcode to hide content after purchase.
	 *
	 * @since        1.0.0
	 *
	 * @param array       $atts    Attributes (Unused).
	 * @param string|null $content The shortcode content.
	 *
	 * @return string|null
	 * @example
	 *         <code>
	 *         [woopaywall_hide_after_purchase]
	 *         Hide this!
	 *         [/woopaywall_hide_after_purchase]
	 *         </code>
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function shortcode__woopaywall_hide_after_purchase( $atts = array(), $content = null ) {

		$product = \WC_Product_Paywall::wc_get_product();
		if ( $product && $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) && $product->is_ok_to_view() ) {
			return '';
		}

		return \do_shortcode( $content );
	}

	/**
	 * Shortcode to show content after purchase.
	 *
	 * @since        2.0.0
	 *
	 * @param array       $atts    Attributes (Unused).
	 * @param string|null $content The shortcode content.
	 *
	 * @return string|null
	 * @example
	 *         <code>
	 *         [woopaywall_show_after_purchase]
	 *         Show this!
	 *         [/woopaywall_show_after_purchase]
	 *         </code>
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function shortcode__woopaywall_show_after_purchase( $atts = array(), $content = null ) {

		$product = \WC_Product_Paywall::wc_get_product();
		if ( $product && $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) && ! $product->is_ok_to_view() ) {
			return '';
		}

		return \do_shortcode( $content );
	}

	/**
	 * Shortcode to show the purchased products.
	 *
	 * @since        3.0.0
	 *
	 * @param array       $atts    Attributes (Unused).
	 * @param string|null $content The shortcode content.
	 *
	 * @return string|null
	 * @example
	 *         <code>
	 *         [woopaywall_purchased_products]
	 *         </code>
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function shortcode__woopaywall_purchased_products( $atts = array(), $content = null ) {

		$purchased_products = App::instance()->get_current_user()->getPurchasedProducts();

		if ( $purchased_products ) {

			/**
			 * Template.
			 *
			 * @see \WOOPAYWALL\Template\bookmark__template_purchased_products
			 */
			$template_name = 'paywall-purchased-products.php';
			$template_args = array( 'purchased_products' => $purchased_products );

			$out = Template\Controller::get_content( $template_name, $template_args );
		} else {
			$out = \__( 'No products found.', 'woocommerce' );
		}

		return $out;
	}

	/**
	 * Static method to do_shortcode_purchased_products.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public static function do_shortcode_purchased_products() {
		return \do_shortcode( '[woopaywall_purchased_products]' );
	}

	/**
	 * Static method to echo do_shortcode_purchased_products.
	 *
	 * @since 3.0.0
	 * @since 3.4.0 Allow FORM tag.
	 * @return void
	 */
	public static function echo_shortcode_purchased_products() {
		$allowed_html          = \wp_kses_allowed_html( 'post' );
		$allowed_html['form']  = array(
			'action' => true,
			'method' => true,
		);
		$allowed_html['input'] = array(
			'type'  => true,
			'id'    => true,
			'name'  => true,
			'value' => true,
		);
		echo \wp_kses( self::do_shortcode_purchased_products(), $allowed_html );
	}
}

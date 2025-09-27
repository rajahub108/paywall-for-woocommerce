<?php
/**
 * ProductCSS
 *
 * @since 2.0.0
 * @since Renamed from WooCommerce Blocks support
 *
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend;

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;

/**
 * Class Frontend\ProductCSS
 */
class ProductCSS extends Hookable {

	/**
	 * Prefix for CSS class names.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	const CSS_PREFIX = 'woopaywall';

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_filter(
			'woocommerce_post_class',
			array( $this, 'filter__woocommerce_post_class' ),
			App::HOOK_PRIORITY_LATE,
			2
		);

		\add_filter(
			'woocommerce_blocks_product_grid_item_html',
			array( $this, 'add_product_classes_to_grid' ),
			App::HOOK_PRIORITY_LATE,
			3
		);
	}

	/**
	 * Generate array of Paywall-specific product CSS classes.
	 *
	 * @since 4.0.0
	 *
	 * @param AbstractPaywallProductSimple $product Product object.
	 *
	 * @return string[]
	 */
	protected function make_paywall_classes( $product ) {

		$classes = array();

		if ( $product instanceof AbstractPaywallProductSimple ) {

			if ( $product->is_in_cart() ) {
				$classes[] = 'in-cart';
				$classes[] = static::CSS_PREFIX . '-in-cart';
			}
			if ( $product->is_active() ) {
				$classes[] = 'ok-to-view';
				$classes[] = static::CSS_PREFIX . '-active';
			}
			if ( ! $product->is_in_stock() ) {
				$classes[] = static::CSS_PREFIX . '-out-of-stock';
			}
			if ( ! $product->is_purchasable() ) {
				$classes[] = static::CSS_PREFIX . '-not-purchasable';
			}
		}

		return $classes;
	}

	/**
	 * Add classes to li class="product..." in the archive views.
	 *
	 * @since 2.0.0
	 *
	 * @param string[]                     $classes Array of CSS classes.
	 * @param AbstractPaywallProductSimple $product Product object.
	 *
	 * @return array
	 */
	public function filter__woocommerce_post_class( $classes, $product ) {

		$paywall_classes = $this->make_paywall_classes( $product );

		$classes = array_merge( $classes, $paywall_classes );

		return array_unique( $classes );
	}

	/**
	 * Add product classes to the blocks grid.
	 *
	 * @param string                          $html    The already rendered HTML.
	 * @param \stdClass                       $data    Unused.
	 * @param \WC_Product_Paywall|\WC_Product $product The product instance.
	 *
	 * @return string|string[]
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function add_product_classes_to_grid( $html, $data, $product ) {

		$classes = array(
			'wc-block-grid__product',
			'product-type-' . $product->get_type(),
			'post-' . $product->get_id(),
		);

		$paywall_classes = $this->make_paywall_classes( $product );

		if ( count( $paywall_classes ) ) {

			$classes = array_merge( $classes, $paywall_classes );

			$classes = array_unique( $classes );
		}

		return str_replace(
			'<li class="wc-block-grid__product',
			'<li class="' . implode( ' ', $classes ),
			$html
		);
	}
}

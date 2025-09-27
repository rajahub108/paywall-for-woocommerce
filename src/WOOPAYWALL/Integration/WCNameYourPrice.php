<?php
/**
 * Integration.
 * Plugin Name: Name Your Price
 * Plugin URI: https://woocommerce.com/products/name-your-price/
 *
 * @since 2.0.0
 */

namespace WOOPAYWALL\Integration;

use WOOPAYWALL\App;

/**
 * Class Integration\WCNameYourPrice
 */
class WCNameYourPrice extends AbstractIntegration {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_filter(
			'wc_nyp_simple_supported_types',
			array( $this, 'add_to_nyp_supported_types' )
		);

		// Not required. Changed our algorithm.
		0 && \add_action(
			'woocommerce_before_single_product',
			array( $this, 'restore_woocommerce_template_single_price' ),
			App::HOOK_PRIORITY_LATE
		);
	}

	/**
	 * Register PAYWALL as one of the NYP supported types.
	 *
	 * @since 2.0.0
	 *
	 * @param $supported_types
	 *
	 * @return array
	 */
	public function add_to_nyp_supported_types( $supported_types ) {
		$supported_types[] = \WC_Product_Paywall::PRODUCT_TYPE;

		return array_unique( $supported_types );
	}

	/**
	 * NYP removes standard price HTML on single product page and replaces it with own HTML.
	 * When Paywall product is already purchased, we use the standard HTML to print "Thank you!".
	 *
	 * @scope Paywall+NYP, OK to view.
	 */
	public function restore_woocommerce_template_single_price() {

		$product = \WC_Product_Paywall::wc_get_product();

		if (
			$product
			&& $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE )
			&& \WC_Name_Your_Price_Helpers::is_nyp( $product )
			&& $product->is_ok_to_view()
		) {
			\add_action(
				'woocommerce_single_product_summary',
				'woocommerce_template_single_price'
			);
		}
	}
}

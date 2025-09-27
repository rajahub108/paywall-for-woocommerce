<?php
/**
 * Integration.
 * Plugin Name: WPML Multilingual CMS
 * Plugin URI: https://wpml.org/
 *
 * @since 3.1.0
 */

namespace WOOPAYWALL\Integration;

/**
 * Class WPML
 */
class WPML extends AbstractIntegration {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {
		\add_filter( 'woopaywall_ids_to_check', array( $this, 'filter__woopaywall_ids_to_check' ) );
	}

	/**
	 * Given a product ID, return the array of IDs of all WPML translations of the product.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return int|int[]
	 */
	public function filter__woopaywall_ids_to_check( $product_id ) {

		global $wpml_post_translations;

		$wpml_ids = $wpml_post_translations->get_element_translations( $product_id );
		if ( is_array( $wpml_ids ) && count( $wpml_ids ) > 0 ) {
			$product_id = array_map( 'intval', array_unique( array_values( $wpml_ids ) ) );
		}

		return $product_id;
	}
}

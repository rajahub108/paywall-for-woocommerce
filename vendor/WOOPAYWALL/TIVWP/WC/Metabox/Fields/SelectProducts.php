<?php
/**
 * Metabox field: select products.
 *
 * @since  1.6.0
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Metabox\Fields;

use WOOPAYWALL\Dependencies\TIVWP\UniMeta\AbstractUniMeta;

/**
 * Class SelectProducts
 *
 * @since  1.6.0
 */
class SelectProducts implements InterfaceMetaboxField {

	/**
	 * Render the field.
	 *
	 * @since  1.6.0
	 * @since  1.9.0 Use UniMeta.
	 *
	 * @param array           $meta_field Meta Field definition.
	 * @param AbstractUniMeta $uni_meta   UniMeta object.
	 *
	 * @return string
	 */
	public static function render( $meta_field, $uni_meta ) {
		$field_id = $meta_field['id'];

		$selected = $uni_meta->get_meta( $field_id, true, 'edit' );
		$selected = (array) $selected;

		ob_start();
		?>
		<select multiple="multiple"
				id="<?php echo \esc_attr( $field_id ); ?>"
				name="<?php echo \esc_attr( $field_id ); ?>[]"
				class="wc-product-search"
				aria-label="<?php \esc_attr__( 'Select', 'woocommerce' ); ?>"
				style="width: 50%;"
				data-sortable="true"
				data-placeholder="<?php \esc_attr_e( 'Search for a product&hellip;', 'woocommerce' ); ?>"
				data-action="woocommerce_json_search_products"
				data-exclude="<?php echo \esc_attr( $uni_meta->get_id() ); ?>">
			<?php
			foreach ( $selected as $product_id ) {
				$product = \wc_get_product( $product_id );
				if ( $product ) {
					$value = $product_id;
					$text  = $product->get_formatted_name();
					?>
					<option value="<?php echo \esc_attr( $value ); ?>" selected>
						<?php echo \esc_html( \wp_strip_all_tags( $text ) ); ?>
					</option>
					<?php
				}
			}
			?>
		</select>
		<?php
		return ob_get_clean();
	}
}

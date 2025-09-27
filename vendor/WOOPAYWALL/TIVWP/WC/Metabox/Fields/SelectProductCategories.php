<?php
/**
 * Metabox field: select product categories.
 *
 * @since  1.6.0
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Metabox\Fields;

use WOOPAYWALL\Dependencies\TIVWP\UniMeta\AbstractUniMeta;

/**
 * Class SelectProductCategories
 *
 * @since  1.6.0
 */
class SelectProductCategories implements InterfaceMetaboxField {

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
				class="wc-category-search"
				aria-label="<?php \esc_attr__( 'Select', 'woocommerce' ); ?>"
				style="width: 50%;"
				data-sortable="true"
				data-placeholder="<?php \esc_attr_e( 'Search for a category&hellip;', 'woocommerce' ); ?>"
				data-action="woocommerce_json_search_categories">
			<?php
			foreach ( $selected as $slug ) {
				$category = \get_term_by( 'slug', $slug, 'product_cat' );
				if ( $category ) {
					$value = $category->slug;
					$text  = $category->name;
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

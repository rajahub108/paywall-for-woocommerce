<?php
/**
 * Metabox field: Select.
 *
 * @since  1.6.0
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Metabox\Fields;

use WOOPAYWALL\Dependencies\TIVWP\Logger\Log;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;
use WOOPAYWALL\Dependencies\TIVWP\UniMeta\AbstractUniMeta;

/**
 * Class Select
 *
 * @since  1.6.0
 */
class Select implements InterfaceMetaboxField {

	/**
	 * Is multiple select?
	 *
	 * @since 1.9.0
	 * @return bool
	 */
	protected static function is_multiple() {
		return false;
	}

	/**
	 * Conditionally print 'multiple="multiple"'.
	 *
	 * @since 1.9.0
	 * @return void
	 */
	protected static function e_multiple_attr() {
		echo static::is_multiple() ? 'multiple="multiple"' : '';
	}

	/**
	 * Conditionally print '[]' after the field name.
	 *
	 * @since 1.9.0
	 * @return void
	 */
	protected static function e_multiple_name_brackets() {
		echo static::is_multiple() ? '[]' : '';
	}

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

		try {
			if ( empty( $meta_field['options'] ) ) {
				throw new Message( "meta_field['options'] is empty" );
			}
		} catch ( Message $e ) {
			Log::error( $e );

			return '';
		}

		$field_id = $meta_field['id'];

		$default = isset( $meta_field['default'] ) ? $meta_field['default'] : '';

		$selected      = $uni_meta->get_meta( $field_id, true, 'edit', $default );
		$selected      = (array) $selected;
		$is_sequential = array_key_exists( 0, $meta_field['options'] );

		ob_start();
		?>
		<select <?php static::e_multiple_attr(); ?>
				id="<?php echo \esc_attr( $field_id ); ?>"
				name="<?php echo \esc_attr( $field_id ); ?><?php static::e_multiple_name_brackets(); ?>"
				class="wc-enhanced-select"
				aria-label="<?php \esc_attr__( 'Select', 'woocommerce' ); ?>"
				style="width: 50%;"
		>
			<?php
			foreach ( $meta_field['options'] as $value => $text ) {
				if ( $is_sequential ) {
					$value = $text;
				}
				?>
				<option value="<?php echo \esc_attr( $value ); ?>"
					<?php \selected( in_array( $value, $selected, true ) ); ?>>
					<?php echo \esc_html( \wp_strip_all_tags( $text ) ); ?>
				</option>
				<?php
			}
			?>
		</select>
		<?php
		return ob_get_clean();
	}
}

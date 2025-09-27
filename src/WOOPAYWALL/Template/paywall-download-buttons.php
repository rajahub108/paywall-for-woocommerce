<?php
/**
 * Template: Download Buttons.
 * This template can be overridden by copying it to yourtheme/paywall-download-buttons.php.
 *
 * @see \WOOPAYWALL\Frontend\Product\Downloads::show_buttons.
 * @since 2.1.0
 * @version 2.1.0
 *
 * @global array $args The template arguments array.
 *
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Template;

defined( 'ABSPATH' ) || exit;

if ( ! empty( $args['downloads'] ) && ! empty( $args['product_id'] ) ) {
	?>
	<h2 class="woocommerce-order-downloads__title">
		<?php \esc_html_e( 'Downloads', 'woocommerce' ); ?>
	</h2>
	<?php

	foreach ( $args['downloads'] as $download ) {
		if ( $download['product_id'] === $args['product_id'] ) {
			?>
			<a href="<?php echo \esc_url( $download['download_url'] ); ?>"
					class="woocommerce-MyAccount-downloads-file button alt"><?php \esc_html_e( $download['download_name'] ); ?></a>
			<?php
		}
	}
}

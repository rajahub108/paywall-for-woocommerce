<?php
/**
 * Admin notices.
 *
 * @since   1.0.0
 * Copyright (c) 2019. TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Admin;

use WOOPAYWALL\App;

/**
 * Class Notices
 *
 * @package WOOPAYWALL\Admin
 */
class Notices {

	/**
	 * Public access to the __CLASS__.
	 *
	 * @return string
	 */
	public static function get_class() {
		return __CLASS__;
	}

	/**
	 * Requirements not met.
	 *
	 * @noinspection PhpUnused
	 */
	public static function requirements() {
		?>
		<div class="notice notice-error is-dismissible error">
			<p>
				<?php
				printf( /* Translators: %1$s - Paywall for WooCommerce, %2$s - link to woocommerce.com, %3$s - required WooCommerce version */
					\esc_html( \__( '%1$s requires %2$s version %3$s+ to be installed and active.', 'paywall-for-woocommerce' ) ),
					\esc_html__( 'Paywall for WooCommerce', 'paywall-for-woocommerce' ),
					'<a href="https://woocommerce.com" target="_blank">'
					. \esc_html__( 'WooCommerce', 'woocommerce' ) . '</a>',
					\esc_html( App::WC_REQUIRES_AT_LEAST )
				);
				?>
			</p>
		</div>
		<?php
	}
}

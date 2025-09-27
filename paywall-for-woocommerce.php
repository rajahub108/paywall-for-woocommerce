<?php
/**
 * Plugin Name: Paywall for WooCommerce
 * Plugin URI: https://woocommerce.com/products/paywall/
 * Description: Pay to view premium content WooCommerce products.
 * Version: 4.1.1
 * Author: TIV.NET
 * Author URI: https://tivnet.com/
 * Text Domain: paywall-for-woocommerce
 * Domain Path: /languages/
 * Requires at least: 5.8
 * Requires PHP: 7.2
 *
 * Developer: Gregory K.
 * Developer URI: https://profiles.wordpress.org/tivnet/
 * WC requires at least: 6.9.0
 * WC tested up to: 8.2.2
 * Woo: 5253500:68d2f56f8bad12b0c68fbcef52ecd3c3
 *
 * Copyright: © 2023 TIV.NET INC.
 * License: GPL-3.0-or-later
 * License URI: https://spdx.org/licenses/GPL-3.0-or-later.html
 *
 * @noinspection PhpDefineCanBeReplacedWithConstInspection
 */

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use WOOPAYWALL\App;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Short-circuit special WP calls.
 *
 * @since 3.9.0
 */
if (
	// xmlrpc.php:13
	( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST )
	|| ( defined( 'WP_CLI' ) && WP_CLI )
	|| (
		! empty( $_SERVER['REQUEST_URI'] )
		&& preg_match( '/\/wp-(login|signup|trackback)\.php|favicon\.ico|\.txt/i', esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) )
) {
	return;
}

define( 'PAYWALL_FOR_WOOCOMMERCE_VERSION', '4.1.1' );

try {

	// Refuse to work below PHP 7.2.
	if ( PHP_VERSION_ID < 70200 ) {
		throw new Exception( 'Unsupported PHP version: ' . phpversion() );
	}

	/**
	 * Check if packaged correctly.
	 *
	 * @since 3.9.2
	 */
	foreach (
		array(
			__DIR__ . '/vendor/autoload.php',
			__DIR__ . '/vendor/WOOPAYWALL/TIVWP',
		) as $woopaywall_required_file
	) {
		if ( ! is_readable( $woopaywall_required_file ) ) {
			throw new Exception( "Not found: $woopaywall_required_file" );
		}
	}
	require_once __DIR__ . '/vendor/autoload.php';

	/**
	 * Declare compatibility with HPOS.
	 *
	 * @since 3.9.0
	 */
	add_action(
		'before_woocommerce_init',
		function () {
			if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
				FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__ );
			}
		}
	);

	App::instance()->configure( __FILE__ )->setup_hooks();

} catch ( Exception $woopaywall_load_exception ) {

	add_action( 'admin_init', function () use ( $woopaywall_load_exception ) {
		?>
		<div class="error">
			<p>Paywall for WooCommerce <?php echo esc_html( PAYWALL_FOR_WOOCOMMERCE_VERSION ); ?>
				cannot run.
				<br>
				<?php echo esc_html( $woopaywall_load_exception->getMessage() ); ?>
				<br>
				Please submit a <a target="_" href="https://woocommerce.com/my-account/tickets/">Support Ticket</a> and include this message.
			</p>
		</div>
		<?php
	} );

	return;
}

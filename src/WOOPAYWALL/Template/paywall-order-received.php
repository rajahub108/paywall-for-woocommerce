<?php
/**
 * Template: Purchased Products on checkout/order-received page.
 * This template can be overridden by copying it to your-theme/paywall-order-received.php.
 *
 * @since   3.0.0
 *
 * @global array $args The template arguments array.
 *
 * Copyright (c) 2021, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Template;

use WOOPAYWALL\Frontend\Shortcode;
use WOOPAYWALL\MyAccount\EndpointPurchasedProducts;

defined( 'ABSPATH' ) || exit;

if ( 0 ) {
	/**
	 * IDE Bookmark
	 *
	 * @noinspection PhpUnused
	 */
	function bookmark__template_order_received() {
	}
}
?>
<section class="woopaywall-order-details-purchased-products">
	<h2 class="woopaywall-order-details-purchased-products__title">
		<?php echo \esc_html( EndpointPurchasedProducts::title() ); ?>
	</h2>
	<?php Shortcode::echo_shortcode_purchased_products(); ?>

	<?php
	/**
	 * If logged-in user, display a message about My Account.
	 */
	?>
	<?php if ( \get_current_user_id() ) : ?>
		<div class="woocommerce-notices-wrapper woopaywall-link-to-purchased-products">
			<div class="woocommerce-info" role="alert">
				<a href="<?php echo \esc_url( EndpointPurchasedProducts::my_account_url() ); ?>"
						class="button wc-forward"><?php \esc_html_e( 'My account', 'woocommerce' ); ?></a>
				<?php \esc_html_e( 'You can also see this list on the Account page.', 'paywall-for-woocommerce' ); ?>
			</div>
		</div>
	<?php endif; ?>

	<hr>
</section>

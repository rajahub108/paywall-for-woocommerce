<?php
/**
 * Template: Purchased Products.
 * This template can be overridden by copying it to yourtheme/paywall-purchased-products.php.
 *
 * @since   3.0.0
 *
 * @global array $args The template arguments array.
 *
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Template;

use WOOPAYWALL\Dependencies\TIVWP\Constants;
use WOOPAYWALL\Dependencies\TIVWP\WC\DTLocal;
use WOOPAYWALL\Expiration\ExpireAfter;
use WOOPAYWALL\Order\Info;
use WOOPAYWALL\PurchasedProduct;

defined( 'ABSPATH' ) || exit;

if ( 0 ) {
	/**
	 * IDE Bookmark
	 *
	 * @noinspection PhpUnused
	 */
	function bookmark__template_purchased_products() {
	}
}

if ( empty( $args['purchased_products'] ) ) {
	return;
}

/**
 * Products.
 *
 * @var PurchasedProduct[] $purchased_products
 */
$purchased_products = $args['purchased_products'];

$columns = array(
	'product-name'  => \esc_html__( 'Title', 'woocommerce' ),
	'order-status'  => \esc_html__( 'Status', 'woocommerce' ),
	'expires-in'    => \esc_html__( 'Expires in', 'paywall-for-woocommerce' ),
	'order-actions' => '&nbsp;',
);

$texts = array(
	'order_status' => array(
		PurchasedProduct::STATUS_EXPIRED => \__( 'Expired :(', 'woocommerce' ),
		PurchasedProduct::STATUS_ACTIVE  => \__( 'Active', 'woocommerce' ),
	),

	'order_actions' => array(
		PurchasedProduct::STATUS_EXPIRED => \__( 'Order again', 'woocommerce' ),
		PurchasedProduct::STATUS_ACTIVE  => \__( 'View', 'woocommerce' ),
	),
);

$css_classes = array(
	'order_actions_link' => array(
		PurchasedProduct::STATUS_EXPIRED => 'woocommerce-button button',
		PurchasedProduct::STATUS_ACTIVE  => 'woocommerce-button button alt',
	),
);

?>
<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table woopaywall-table-my-products">
	<thead>
	<tr>
		<?php foreach ( $columns as $column_id => $column_name ) { ?>
			<th class="<?php echo \esc_attr( $column_id ); ?>">
				<span class="nobr"><?php echo \esc_html( $column_name ); ?></span>
			</th>
		<?php } ?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $purchased_products as $product_id => $purchased_product ) {
		$expiration_status = $purchased_product->get_expiration_status();
		?>
		<tr class="woocommerce-table__line-item woopaywall_<?php echo \esc_attr( $expiration_status ); ?>">
			<td class="woocommerce-table__product-name product-name">
				<?php echo \esc_html( $purchased_product->get_title() ); ?>
			</td>
			<td class="woocommerce-orders-table__status order-status">
				<?php echo \esc_html( $texts['order_status'][ $expiration_status ] ); ?>
			</td>
			<td class="woocommerce-orders-table__status order-status">
				<?php
				if ( $purchased_product->is_active() && $purchased_product->has_expiration() ) {
					echo \esc_html( ExpireAfter::get_formatted_interval(
						$purchased_product->get_expiration_interval() )
					);
				} else {
					echo '-';
				}

				$order_info = $purchased_product->getOrderInfo();
				$the_order  = $order_info->getOrder();

				$debug_info = Constants::is_true( 'PAYWALL_DEBUG' );
				if ( $debug_info ) {

					$date_paid  = $purchased_product->get_date_paid();
					$local      = new DTLocal( $date_paid );
					$value      = $purchased_product->get_expire_after()->getValue();
					$units      = $purchased_product->get_expire_after()->getUnits();
					$expires_on = ( new DTLocal( $date_paid ) )->modify( "+$value $units" );

					$debug_data = array(
						'Product ID'                => $product_id,
						'Product expiration'        => $purchased_product->get_expire_after()->get_expiration_string(),
						'Time zone'                 => \wc_timezone_string(),
						'order->get_id()'           => $the_order->get_id(),
						'order->get_status()'       => $the_order->get_status(),
						'order->get_date_paid()'    => $the_order->get_date_paid(),
						'order->get_date_created()' => $the_order->get_date_created(),
						"Paywall: 'anchor' date"    => $date_paid,
						'... DTLocal'               => $local,
						'Product expiration date'   => $expires_on,
						'Now'                       => new DTLocal(),
						'Order expiration override' => $order_info->get_expires_on(),
						'Order expired?'            => $order_info->is_expired() ? 'Yes' : 'No',
					);
					?>
					<table>
						<tr>
							<th>
								<?php echo \wp_kses_post( implode( '<br>', array_keys( $debug_data ) ) ); ?>
							</th>
							<td>
								<?php echo \wp_kses_post( implode( '<br>', array_values( $debug_data ) ) ); ?>
							</td>
						</tr>
					</table>
					<?php
				}
				?>
			</td>
			<td class="woocommerce-orders-table__cell-order-actions order-actions">
				<?php
				$product = $purchased_product->getProduct();
				if ( $product instanceof \WC_Product_Paywall && $purchased_product->is_active() ) {
					// Cache buster, just in case.
					$query_args = array( 't' => time() );
					// Order key for guest users.
					if ( ! \is_user_logged_in() ) {
						$query_args[ Info::ORDER_KEY_PARAMETER ] = $the_order->get_order_key();
					}
					$product_woopaywall_url = \add_query_arg(
						$query_args,
						$product->get_permalink()
					);
					?>
					<a href="<?php echo \esc_url( $product_woopaywall_url ); ?>"
							class="<?php echo \esc_attr( $css_classes['order_actions_link'][ $expiration_status ] ); ?>">
						<?php echo \esc_html( $texts['order_actions'][ $expiration_status ] ); ?>
					</a>
				<?php } ?>
			</td>
		</tr>
	<?php } ?>
	</tbody>
</table>

<?php
/**
 * Order email actions.
 *
 * @since 3.7.0
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Order;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;

/**
 * Class OrderEmail
 *
 * @since 3.7.0
 */
class OrderEmail extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @since 3.7.0
	 * @return void
	 */
	public function setup_hooks() {
		0 && \add_action(
			'woocommerce_email_order_details',
			array( $this, 'action__woocommerce_email_order_details' ),
			App::HOOK_PRIORITY_EARLY,
			4
		);

		\add_action(
			'woocommerce_order_item_meta_end',
			array( $this, 'action__woocommerce_order_item_meta_end' ),
			App::HOOK_PRIORITY_EARLY,
			4
		);
	}

	/**
	 * Action woocommerce_email_order_details.
	 *
	 * @since 3.7.0
	 *
	 * @param \WC_Order $order         The order object.
	 * @param bool      $sent_to_admin True if email is sent to admin.
	 * @param bool      $plain_text    True if that's the plain text email part.
	 * @param \WC_Email $email         The email object.
	 *
	 * @return void
	 */
	public function action__woocommerce_email_order_details( $order, $sent_to_admin, $plain_text, $email ) {

		if ( ! $sent_to_admin && isset( $email->id ) && 'customer_completed_order' === $email->id ) {
			echo $plain_text ? "\n" : '<p>';
			echo \esc_url( $order->get_checkout_order_received_url() );
			echo $plain_text ? "\n" : '</p>';
		}
	}

	/**
	 * Add `View` link to Paywall products.
	 *
	 * @since        3.7.0
	 * @since        3.8.1-rc.1 Made $plain_text parameter optional for those plugins that do not send it.
	 *
	 * @param int                    $item_id    Item ID - Unused.
	 * @param \WC_Order_Item_Product $item       Order item.
	 * @param \WC_Order              $order      Order.
	 * @param bool                   $plain_text True if that's the plain text email part.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function action__woocommerce_order_item_meta_end( $item_id, $item, $order, $plain_text = false ) {

		if ( $plain_text ) {
			return;
		}

		if ( 'completed' !== $order->get_status() ) {
			return;
		}

		$product = $item->get_product();
		if ( ! $product instanceof \WC_Product_Paywall ) {
			return;
		}

		// Cache buster, just in case.
		$query_args = array( 't' => time() );
		// Order key for guest users.
		if ( ! \is_user_logged_in() ) {
			$query_args[ Info::ORDER_KEY_PARAMETER ] = $order->get_order_key();
		}
		$product_woopaywall_url = \add_query_arg(
			$query_args,
			$product->get_permalink()
		);

		echo '<br>';
		echo '<span style="font-family:monospace;">';
		echo '[';
		echo '<a href="' . \esc_url( $product_woopaywall_url ) . '">';
		echo \esc_html( \__( 'View', 'woocommerce' ) );
		echo '</a>';
		echo ']';
		echo '</span>';
	}
}

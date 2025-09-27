<?php
/**
 * Order information.
 *
 * @since        1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 * @noinspection PhpUnused
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Order;

/**
 * Class Order\Info
 */
class Info {

	/**
	 * Order object.
	 *
	 * @var \WC_Order
	 */
	protected $order;

	/**
	 * Getter.
	 *
	 * @since 1.5.0
	 * @return \WC_Order
	 */
	public function getOrder() {
		return $this->order;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.5.0 Do not call {@see \wc_get_order()} if the parameter is an order object.
	 *
	 * @param \WC_Order|int $order Order object or order ID.
	 */
	public function __construct( $order ) {
		if ( $order instanceof \WC_Order ) {
			$this->order = $order;
		} else {
			$this->order = \wc_get_order( $order );
		}
	}

	/**
	 * Returns true if order status is considered "paid".
	 *
	 * @return bool
	 */
	public function is_paid() {
		return $this->is_correct_type() && $this->order->has_status( \wc_get_is_paid_statuses() );
	}

	/**
	 * Returns true if order type is "shop_order" (and not a Refund, for instance).
	 *
	 * @return bool
	 */
	public function is_correct_type() {
		return $this->order && 'shop_order' === $this->order->get_type();
	}

	/**
	 * Returns true when I am logged in and looking at someone else's order.
	 * NOTE: If not logged in then returns false.
	 *
	 * @return bool
	 */
	public function is_not_mine() {
		return ( $this->order && \is_user_logged_in() && $this->order->get_user_id() !== \get_current_user_id() );
	}

	/**
	 * Return an array of items/products within this order.
	 *
	 * @param string|string[] $types Types of line items to get (array or string).
	 *
	 * @return \WC_Order_Item_Product[]|\WC_Order_Item[] To avoid "polymorphic call" inspection warning.
	 */
	public function get_items( $types = 'line_item' ) {
		return $this->order ? $this->order->get_items(
		/**
		 * Hook woocommerce_purchase_order_item_types
		 *
		 * @since 1.0.0
		 */
			\apply_filters( 'woocommerce_purchase_order_item_types', $types ) ) : array();
	}

	/**
	 * Get order key.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 */
	public function get_order_key( $context = 'view' ) {
		return $this->order ? $this->order->get_order_key( $context ) : '';
	}

	/**
	 * Check if an order key is valid.
	 *
	 * @param string $key Order key.
	 *
	 * @return bool
	 * @noinspection PhpUnused
	 */
	public function key_is_valid( $key ) {
		return $this->order && $this->order->key_is_valid( $key );
	}

	/**
	 * Returns true if the order has a specific product.
	 *
	 * @since 1.8.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public function has_product( $product_id ) {

		$order_items = $this->get_items();
		foreach ( $order_items as $order_item ) {
			if ( $product_id === $order_item->get_product_id() ) {
				return true;
			}
		}

		return false;
	}
}

<?php
/**
 * Order Notes.
 *
 * @since 3.3.0
 * Copyright (c) 2021, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Order;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;

/**
 * Class OrderNotes
 *
 * @package WOOPAYWALL\Order
 */
class OrderNotes extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_action( 'tivwp_meta_updated', array( $this, 'action__tivwp_meta_updated' ), App::HOOK_PRIORITY_EARLY, 2 );

		\add_action( 'tivwp_meta_deleted', array( $this, 'action__tivwp_meta_deleted' ) );
	}

	/**
	 * Callback action__tivwp_meta_updated.
	 *
	 * @since 3.3.0
	 *
	 * @param array  $meta_field Meta Field.
	 * @param string $meta_value Meta value.
	 *
	 * @return void
	 */
	public function action__tivwp_meta_updated( $meta_field, $meta_value ) {

		if ( ! empty( $meta_field['order_note_updated'] ) ) {
			$order = \wc_get_order();
			if ( $order ) {
				$order->add_order_note( sprintf( $meta_field['order_note_updated'], $meta_value ) );
			}
		}
	}

	/**
	 * Callback action__tivwp_meta_deleted.
	 *
	 * @since 3.3.0
	 *
	 * @param array $meta_field Meta Field.
	 *
	 * @return void
	 */
	public function action__tivwp_meta_deleted( $meta_field ) {

		if ( ! empty( $meta_field['order_note_deleted'] ) ) {
			$order = \wc_get_order();
			if ( $order ) {
				$order->add_order_note( $meta_field['order_note_deleted'] );
			}
		}
	}
}

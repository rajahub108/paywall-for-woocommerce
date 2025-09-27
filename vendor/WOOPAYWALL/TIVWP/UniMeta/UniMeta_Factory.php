<?php
/**
 * UniMeta_Factory
 *
 * @since 1.9.0
 *
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\UniMeta;

use WOOPAYWALL\Dependencies\TIVWP\Logger\Log;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;

/**
 * Class UniMeta_Factory
 *
 * @since 1.9.0
 */
class UniMeta_Factory {

	/**
	 * Try getting object from ID.
	 *
	 * @since 1.9.0
	 *
	 * @param int $id Object ID.
	 *
	 * @return \WP_Post|\WC_Product|int Return ID if failed to get object.
	 */
	protected static function id2object( $id ) {
		try {

			$object = \get_post( $id );
			if ( ! $object instanceof \WP_Post ) {
				throw new Message( "get_post( $id ) failed" );
			}

			if ( 'product' === $object->post_type ) {
				$object = \wc_get_product( $object );
				if ( ! $object instanceof \WC_Product ) {
					throw new Message( "wc_get_product( $id ) failed" );
				}
			}

		} catch ( Message $e ) {
			Log::error( $e );

			return $id;
		}

		return $object;
	}

	/**
	 * Method get_object.
	 *
	 * @since 1.9.0
	 *
	 * @param int|\WC_Order|\WC_Product|\WP_Post $wp_object_or_id Object or ID.
	 *
	 * @return AbstractUniMeta|UniMeta_WC_Order
	 */
	public static function get_object( $wp_object_or_id ) {

		if ( is_int( $wp_object_or_id ) ) {
			$wp_object_or_id = self::id2object( $wp_object_or_id );
		}

		if ( $wp_object_or_id instanceof \WC_Order ) {
			return new UniMeta_WC_Order( $wp_object_or_id );
		} elseif ( $wp_object_or_id instanceof \WC_Product ) {
			return new UniMeta_WC_Product( $wp_object_or_id );
		} elseif ( $wp_object_or_id instanceof \WP_Post && 'product' === $wp_object_or_id->post_type ) {
			return new UniMeta_WC_Product( \wc_get_product( $wp_object_or_id ) );
		}

		// Default - post or page.
		return new UniMeta_WP_Post( $wp_object_or_id );
	}
}

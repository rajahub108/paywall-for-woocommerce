<?php
/**
 * UniMeta_WC_Product
 *
 * @since 1.9.0
 *
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\UniMeta;

/**
 * Class UniMeta_WC_Product
 *
 * @since 1.9.0
 */
class UniMeta_WC_Product extends AbstractUniMeta {

	/**
	 * Var wp_object.
	 *
	 * @since 1.9.0
	 *
	 * @var \WC_Product
	 */
	protected $wp_object;

	/**
	 * Implements get_meta
	 *
	 * @inheritDoc
	 */
	public function get_meta( $key, $single = true, $context = 'edit', $default = '' ) {
		$meta = $this->wp_object->get_meta( $key, $single, $context );

		return $meta ? $meta : $default;
	}

	/**
	 * Implements set_meta
	 *
	 * @inheritDoc
	 */
	public function set_meta( $key, $value ) {
		$this->wp_object->update_meta_data( $key, $value );
	}

	/**
	 * Implements save_object
	 *
	 * @inheritDoc
	 */
	public function save_object() {
		$this->wp_object->save();
	}

	/**
	 * Implements delete_meta
	 *
	 * @inheritDoc
	 */
	public function delete_meta( $key ) {
		$this->wp_object->delete_meta_data( $key );
	}

	/**
	 * Implements get_id
	 *
	 * @inheritDoc
	 */
	public function get_id() {
		return $this->wp_object->get_id();
	}

}

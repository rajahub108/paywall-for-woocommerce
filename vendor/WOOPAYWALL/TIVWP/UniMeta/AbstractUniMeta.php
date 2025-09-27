<?php
/**
 * AbstractUniMeta
 *
 * @since 1.9.0
 */

namespace WOOPAYWALL\Dependencies\TIVWP\UniMeta;

/**
 * Class AbstractUniMeta
 *
 * @since 1.9.0
 */
abstract class AbstractUniMeta {

	/**
	 * Var wp_object.
	 *
	 * @since 1.9.0
	 *
	 * @var \WP_Post|\WC_Product|\WC_Order
	 */
	protected $wp_object;

	/**
	 * Getter wp_object
	 *
	 * @since 1.10.0
	 *
	 * @return \WC_Order|\WC_Product|\WP_Post
	 */
	public function get_wp_object() {
		return $this->wp_object;
	}

	/**
	 * Get Meta by Key.
	 *
	 * @since 1.9.0
	 *
	 * @param string $key     Meta Key.
	 * @param bool   $single  return first found meta with key, or all with $key.
	 * @param string $context What the value is for. Valid values are view and edit.
	 * @param string $default Default value.
	 *
	 * @return mixed
	 */
	abstract public function get_meta( $key, $single = true, $context = 'view', $default = '' );

	/**
	 * Set Meta value.
	 *
	 * @since 1.9.0
	 *
	 * @param string $key   Meta Key.
	 * @param mixed  $value Value.
	 *
	 * @return void
	 */
	abstract public function set_meta( $key, $value );

	/**
	 * Delete Meta.
	 *
	 * @since 1.9.0
	 *
	 * @param string $key Meta Key.
	 *
	 * @return void
	 */
	abstract public function delete_meta( $key );

	/**
	 * Get object ID.
	 *
	 * @since 1.9.0
	 *
	 * @return int
	 */
	abstract public function get_id();

	/**
	 * Save the object. Default: no action.
	 *
	 * @since 1.9.0
	 * @return void
	 */
	public function save_object() {
	}

	/**
	 * List of deprecated methods.
	 *
	 * @since 1.10.0
	 * @return array
	 */
	protected function deprecated_methods() {
		return array();
	}

	/**
	 * Replace deprecated methods with new names.
	 *
	 * @since 1.10.0
	 * @param string $method_name Method name to check.
	 * @return string
	 */
	protected function un_deprecate( $method_name ) {
		$old_to_new = $this->deprecated_methods();
		return isset( $old_to_new[ $method_name ] ) ? $old_to_new[ $method_name ] : $method_name;
	}

	/**
	 * Constructor AbstractUniMeta
	 *
	 * @since 1.9.0
	 *
	 * @param \WC_Order|\WC_Product|\WP_Post $wp_object Object.
	 */
	public function __construct( $wp_object ) {
		$this->wp_object = $wp_object;
	}
}

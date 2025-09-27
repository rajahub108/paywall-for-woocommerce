<?php
/**
 * Abstract Data class.
 *
 * @since        1.11.0
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 * @noinspection PhpUnused
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Abstracts;

/**
 * Class AbstractData
 *
 * @since 1.11.0
 *
 */
abstract class AbstractData {

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 1.11.0
	 * @var array $data
	 */
	protected $data = array();

	/**
	 * Set to _data on construct, so we can track and reset data if needed.
	 *
	 * @since 1.11.0
	 * @var array
	 */
	protected $default_data = array();

	/**
	 * Set all props to default values.
	 *
	 * @since 1.11.0
	 */
	public function set_defaults() {
		$this->data = $this->default_data;
	}

	/**
	 * Get a data variable.
	 *
	 * @since 1.11.0
	 * @param string $key     Key to get.
	 * @param mixed  $default used if the variable isn't set.
	 *
	 * @return int|string value of the variable
	 */
	public function get( $key, $default = null ) {
		$key = \sanitize_key( $key );

		return isset( $this->data[ $key ] ) ? $this->data[ $key ] : $default;
	}

	/**
	 * Set a data variable.
	 *
	 * @since 1.11.0
	 * @param string $key   Key to set.
	 * @param mixed  $value Value to set.
	 *
	 * @return AbstractData
	 */
	public function set( $key, $value ) {
		$key = \sanitize_key( $key );

		$this->data[ $key ] = $value;

		return $this;
	}

	/**
	 * Returns all data for this object.
	 *
	 * @since        1.11.0
	 * @return array
	 * @noinspection PhpUnused
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Get ID
	 *
	 * @since 1.11.0
	 * @return int|string
	 */
	public function get_id() {
		return $this->get( 'id' );
	}

	/**
	 * Get Title
	 *
	 * @since 1.11.0
	 * @return string
	 */
	public function get_title() {
		return $this->get( 'title' );
	}

	/**
	 * Get Description
	 *
	 * @since 1.11.0
	 * @return string
	 */
	public function get_desc() {
		return $this->get( 'desc' );
	}

	/*
	|--------------------------------------------------------------------------
	| Setters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Set ID.
	 *
	 * @since 1.11.0
	 * @param int|string $value Value to set.
	 *
	 * @return AbstractData
	 */
	public function set_id( $value ) {
		return $this->set( 'id', $value );
	}

	/**
	 * Set Title.
	 *
	 * @since 1.11.0
	 * @param string $value Value to set.
	 *
	 * @return AbstractData
	 */
	public function set_title( $value ) {
		return $this->set( 'title', $value );
	}

	/**
	 * Set Description.
	 *
	 * @since 1.11.0
	 * @param string $value Value to set.
	 *
	 * @return AbstractData
	 */
	public function set_desc( $value ) {
		return $this->set( 'desc', $value );
	}

}

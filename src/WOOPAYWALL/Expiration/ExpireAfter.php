<?php
/**
 * ExpireAfter object type.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Expiration;

use WOOPAYWALL\Dependencies\TIVWP\WC\DTLocal;

/**
 * Class ExpireAfter
 *
 * @package WOOPAYWALL
 */
class ExpireAfter {

	/**
	 * Default expiration units.
	 *
	 * @var string
	 */
	const DEFAULT_UNITS = 'days';

	/**
	 * Default expiration value.
	 *
	 * @var int
	 */
	const DEFAULT_VALUE = 0;

	/**
	 * Data.
	 *
	 * @var array       $data  {
	 * @type string|int $value Default=0 means no expiration.
	 * @type string     $units Units.
	 *                         }
	 */
	protected $data = array(
		'value' => self::DEFAULT_VALUE,
		'units' => self::DEFAULT_UNITS,
	);

	/**
	 * ExpireAfter constructor.
	 *
	 * @param int|array $value {
	 *
	 * @type string|int $value Default=0 means no expiration.
	 * @type string     $units Units.
	 *                         }
	 *
	 * @param string    $units Units.
	 */
	public function __construct( $value = self::DEFAULT_VALUE, $units = self::DEFAULT_UNITS ) {

		if ( is_array( $value ) ) {
			// An array[value, units] is passed.
			if ( array_key_exists( 'value', $value ) && array_key_exists( 'units', $value ) ) {
				$this->setData( $value );
			}
		} else {
			// Normal parameters.
			$this->setValue( $value );
			$this->setUnits( $units );
		}
	}

	/**
	 * Getter.
	 *
	 * @return array
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Setter.
	 *
	 * @param array $data
	 */
	public function setData( $data ) {
		$this->data = $data;
	}

	/**
	 * Getter.
	 *
	 * @return int
	 */
	public function getValue() {
		return $this->data['value'];
	}

	/**
	 * Setter.
	 *
	 * @param int $value
	 */
	public function setValue( $value ) {
		$this->data['value'] = $value;
	}

	/**
	 * Getter.
	 *
	 * @return string
	 */
	public function getUnits() {
		return $this->data['units'];
	}

	/**
	 * Setter.
	 *
	 * @param string $units
	 */
	public function setUnits( $units ) {
		$this->data['units'] = $units;
	}

	/**
	 * Is expired since the specified date?
	 *
	 * @since 3.0.4 Fix: convert dates to local TZ before comparing.
	 *
	 * @param \WC_DateTime $date The DateTime object.
	 *
	 * @return bool
	 */
	public function is_expired_since( $date ) {

		if ( ! $this->getValue() ) {
			// Zero value means no expiration.
			return false;
		}

		try {
			// Set local timezone.
			$expires_on = new DTLocal( $date );
			// Add the expiration.
			$expires_on->modify( "+{$this->getValue()} {$this->getUnits()}" );

			return $expires_on->is_in_the_past();
		} catch ( \Exception $e ) {
			// Exception? Safer to say not expired.
			return false;
		}
	}

	/**
	 * Validate data.
	 *
	 * @param array $data  {
	 *
	 * @type int    $value Default=0 means no expiration.
	 * @type string $units Units.
	 *                     }
	 * @return bool
	 */
	public static function is_valid_data( $data ) {
		return is_array( $data ) && array_key_exists( 'value', $data ) && array_key_exists( 'units', $data );
	}

	/**
	 * Format DateInterval
	 *
	 * @since 3.2.0
	 *
	 * @param \DateInterval $interval
	 *
	 * @return string
	 */
	public static function get_formatted_interval( \DateInterval $interval ) {

		$out = array();
		if ( isset( $interval->days ) && $interval->days > 0 ) {
			$out[] = $interval->days;
			$out[] = Units::get_units_name( 'days', $interval->days );
		}
		$out[] = $interval->format( '%H:%I:%S' );

		return implode( ' ', $out );
	}

	/**
	 * Returns formatted expiration (value + units).
	 *
	 * @since 3.3.0
	 *
	 * @return string
	 */
	public function get_expiration_string() {
		$value = $this->getValue();

		return $value . ' ' . Units::get_units_name( $this->getUnits(), $value );
	}
}

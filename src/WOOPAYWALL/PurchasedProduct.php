<?php
/**
 * Purchased Product
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL;

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;
use WOOPAYWALL\Dependencies\TIVWP\WC\DTLocal;
use WOOPAYWALL\Order\Info;

/**
 * Class PurchasedProduct
 *
 * @package WOOPAYWALL
 */
class PurchasedProduct {

	/**
	 * Expiration status.
	 *
	 * @var string
	 */
	const STATUS_ACTIVE = 'active';

	/**
	 * Expiration status.
	 *
	 * @var string
	 */
	const STATUS_EXPIRED = 'expired';

	/**
	 * Product.
	 *
	 * @var \WC_Product_Paywall|\WC_Product_Pwpass
	 */
	protected $product;

	/**
	 * Order Info.
	 *
	 * @var Info
	 */
	protected $order_info;

	/**
	 * PurchasedProduct constructor.
	 *
	 * @param AbstractPaywallProductSimple $product    Product.
	 * @param Info                         $order_info Order Info.
	 */
	public function __construct( $product, Info $order_info ) {
		$this->product    = $product;
		$this->order_info = $order_info;
	}

	/**
	 * Getter.
	 *
	 * @return \WC_Product_Paywall|\WC_Product_Pwpass
	 */
	public function getProduct() {
		return $this->product;
	}

	/**
	 * Getter.
	 *
	 * @return Info
	 */
	public function getOrderInfo() {
		return $this->order_info;
	}

	/**
	 * Get Product title.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->getProduct()->get_title();
	}

	/**
	 * Get date paid.
	 *
	 * @since 3.2.0 Try various order metas to get the date.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return \WC_DateTime Date object.
	 */
	public function get_date_paid( $context = 'edit' ) {

		$order    = $this->getOrderInfo()->getOrder();
		$order_id = $order->get_id();

		$meta_keys = array(
			'date_paid',
			'date_completed',
			'date_created',
		);

		foreach ( $meta_keys as $meta_key ) {
			$method = "get_$meta_key";

			$date = $order->$method( $context );

			if ( $date ) {
				if ( 'date_paid' !== $meta_key ) {
					Log::debug( new Message( array( 'Returning as date_paid', $order_id, $meta_key ) ) );
				}

				return $date;
			}
			Log::debug( new Message( array( 'Missing order metadata', $order_id, $meta_key ) ) );
		}

		// If no order date set, return "Now". The product never expires in this case.
		$date = new \WC_DateTime( 'now' );
		Log::error( new Message( array( 'Returning as date_paid', $order_id, 'now' ) ) );

		return $date;
	}

	/**
	 * Get ExpireAfter of the product in this order.
	 *
	 * @return Expiration\ExpireAfter
	 */
	public function get_expire_after() {
		return $this->getOrderInfo()->get_expire_after( $this->getProduct() );
	}

	/**
	 * Product permalink.
	 *
	 * @return string
	 */
	public function get_permalink() {
		return $this->getProduct()->get_permalink();
	}

	/**
	 * Returns expiration status of the product (in this order, not global).
	 *
	 * @since 3.0.0
	 * @since 3.3.0 Order expiration overrides product expiration.
	 *
	 * @return string
	 */
	public function get_expiration_status() {

		if ( $this->getOrderInfo()->get_expires_on() ) {
			// Order expiration is set. It overrides the product expiration.
			return $this->getOrderInfo()->is_expired() ? self::STATUS_EXPIRED : self::STATUS_ACTIVE;
		}

		return $this->get_expire_after()->is_expired_since( $this->get_date_paid() )
			? self::STATUS_EXPIRED
			: self::STATUS_ACTIVE;
	}

	/**
	 * Returns true is expiration status is ACTIVE.
	 *
	 * @return bool
	 */
	public function is_active() {
		return self::STATUS_ACTIVE === $this->get_expiration_status();
	}

	/**
	 * Returns true if product has expiration (value not zero or order has expiration override).
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Consider order expiration.
	 * @return bool
	 */
	public function has_expiration() {
		return $this->getOrderInfo()->get_expires_on() || $this->get_expire_after()->getValue() > 0;
	}

	/**
	 * Get expiration as DateInterval.
	 *
	 * @since 3.2.0
	 * @since 3.3.0 Order expiration overrides product expiration.
	 * @since 4.0.0 Return null on exception.
	 * @return \DateInterval|null
	 */
	public function get_expiration_interval() {
		try {

			// Try order expiration first
			$expires_on = $this->getOrderInfo()->get_expires_on();

			if ( ! $expires_on ) {
				// Order expiration not set. Use the product expiration.
				$date_paid  = $this->get_date_paid();
				$local      = new DTLocal( $date_paid );
				$value      = $this->get_expire_after()->getValue();
				$units      = $this->get_expire_after()->getUnits();
				$expires_on = $local->modify( "+$value $units" );
			}

			$now = new DTLocal();

			return $now->diff( $expires_on );
		} catch ( \Exception $e ) {
			Log::error( $e );

			return null;
		}
	}
}

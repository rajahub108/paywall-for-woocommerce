<?php
/**
 * Abstract User.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\User;

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Dependencies\TIVWP\Env;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;
use WOOPAYWALL\Log;
use WOOPAYWALL\Order\Info;
use WOOPAYWALL\PurchasedProduct;

/**
 * Class AbstractUser
 *
 * @since   3.0.0
 *
 * @package WOOPAYWALL\User
 */
abstract class AbstractUser extends \WC_Customer {

	/**
	 * All Paywall products purchased by this user.
	 *
	 * @since 3.0.0
	 *
	 * @var PurchasedProduct[]
	 */
	protected $purchased_products = array();

	/**
	 * AbstractUser constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param int  $user_id    Customer ID.
	 * @param bool $is_session True if this is the customer session.
	 */
	public function __construct( $user_id = 0, $is_session = false ) {
		try {
			parent::__construct( $user_id, $is_session );
		} catch ( \Exception $e ) {
			Log::error( new Message( array( 'Customer initialization error', $user_id, $e->getMessage() ) ) );

			return;
		}

		$this->load_purchased_products();

		\add_action( 'woopaywall_load_purchased_products', array( $this, 'load_purchased_products' ) );
	}

	/**
	 * Getter for {@see $purchased_products}.
	 *
	 * @since 3.0.0
	 *
	 * @return PurchasedProduct[]
	 */
	public function getPurchasedProducts() {
		return $this->purchased_products;
	}

	/**
	 * Populate the {@see $purchased_products}.
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	abstract public function load_purchased_products();

	/**
	 * Populate the {@see $purchased_products} from Order Info.
	 *
	 * @since 3.0.0
	 *
	 * @param \WC_Order|int $order_instance_or_id Order instance or ID.
	 */
	protected function load_products_from_order( $order_instance_or_id ) {

		$order_info = new Info( $order_instance_or_id );

		if ( ! $order_info->is_correct_type() ) {
			return;
		}

		$order_items = $order_info->get_items();

		foreach ( $order_items as $order_item ) {
			$product_id = $order_item->get_product_id();
			if ( ! isset( $this->purchased_products[ $product_id ] ) ) {
				$product = \wc_get_product( $product_id );
				if (
					$product instanceof AbstractPaywallProductSimple
					&& 'publish' === $product->get_status()
				) {
					$this->purchased_products[ $product_id ] = new PurchasedProduct(
						$product, $order_info
					);
				}
			}
		}
	}

	/**
	 * Returns true is a valid order key provided in GET/POST.
	 * "Valid" means, there is a paid, not expired, etc... order matching the key.
	 *
	 * @since 3.7.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	protected function is_valid_order_key_provided( $product_id ) {

		$order_key = Env::http_get_or_post( Info::ORDER_KEY_PARAMETER );
		if ( ! $order_key ) {
			return false;
		}

		$order_id = \wc_get_order_id_by_order_key( $order_key );
		if ( ! $order_id ) {
			return false;
		}

		$order_info = new Info( $order_id );
		if ( ! $order_info->is_paid() || $order_info->is_expired() || $order_info->is_not_mine() ) {
			return false;
		}

		if ( ! $order_info->has_product( $product_id ) ) {
			return false;
		}

		$product = \wc_get_product( $product_id );
		if ( $product instanceof AbstractPaywallProductSimple) {
			$purchased_product = new PurchasedProduct( $product, $order_info );
			if ( $purchased_product->is_active() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if a "Paywall Pass" product was purchased and still active.
	 *
	 * @since 4.0.0
	 *
	 * @param PurchasedProduct[] $purchased_products Array of Purchased Products.
	 *
	 * @return bool
	 */
	public function is_any_pwpass_active( $purchased_products = array() ) {

		if ( ! $purchased_products ) {
			$purchased_products = $this->getPurchasedProducts();
		}

		foreach ( $purchased_products as $purchased_product ) {
			if ( $purchased_product->getProduct()->is_type( \WC_Product_Pwpass::PRODUCT_TYPE ) && $purchased_product->is_active() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if the user can view the hidden parts of the product (purchased and not expired yet).
	 *
	 * @since   3.0.0
	 * @since   3.1.0 Added filter allowing 3rd party to add/modify the Product ID to check.
	 * @since   3.4.0 Cache.
	 * @since   3.7.0 Added: Validate by order key.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return bool
	 */
	public function can_view_product( $product_id ) {

		static $cache = array();
		if ( isset( $cache[ $product_id ] ) ) {
			return $cache[ $product_id ];
		}

		// Default is cannot view, unless proved `true` below.
		$cache[ $product_id ] = false;

		if ( $this->is_valid_order_key_provided( $product_id ) ) {
			$cache[ $product_id ] = true;

			return $cache[ $product_id ];
		}

		$purchased_products = $this->getPurchasedProducts();
		if ( ! is_array( $purchased_products ) || count( $purchased_products ) < 1 ) {
			return $cache[ $product_id ];
		}

		/**
		 * Check if a "Paywall Pass" product was purchased and still active.
		 *
		 * @since 4.0.0
		 */
		if ( $this->is_any_pwpass_active( $purchased_products ) ) {
			$cache[ $product_id ] = true;

			return $cache[ $product_id ];
		}

		/**
		 * Filter to modify the Product ID to check.
		 * Example: WPML would return IDs of all translations.
		 *
		 * @since   3.1.0
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return int[] Array of Product IDs.
		 */
		$ids_to_check = (array) \apply_filters( 'woopaywall_ids_to_check', $product_id );

		foreach ( $ids_to_check as $id ) {
			if ( isset( $purchased_products[ $id ] ) ) {
				$purchased_product = $purchased_products[ $id ];
				if ( $purchased_product->is_active() ) {
					$cache[ $product_id ] = true;
					break;
				}
			}
		}

		return $cache[ $product_id ];
	}

	/**
	 * Get time to expire for a specified product.
	 *
	 * @since 3.0.0
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return \DateInterval|false
	 */
	public function get_time_to_expire( $product_id ) {

		$purchased_products = $this->getPurchasedProducts();
		if ( ! is_array( $purchased_products ) || count( $purchased_products ) < 1 ) {
			return false;
		}

		/**
		 * Filter to modify the Product ID to check.
		 * Example: WPML would return IDs of all translations.
		 *
		 * @since   3.1.0
		 *
		 * @param int $product_id Product ID.
		 *
		 * @return int[] Array of Product IDs.
		 */
		$ids_to_check = (array) \apply_filters( 'woopaywall_ids_to_check', $product_id );

		foreach ( $ids_to_check as $id ) {
			if ( isset( $purchased_products[ $id ] ) ) {
				$purchased_product = $purchased_products[ $id ];
				if ( $purchased_product->is_active() && $purchased_product->has_expiration() ) {
					return $purchased_product->get_expiration_interval();
				}
			}
		}

		return false;
	}
}

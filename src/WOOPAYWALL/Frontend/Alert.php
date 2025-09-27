<?php
/**
 * Frontend Alerts.
 *
 * @since        1.0.0
 * @package      WOOPAYWALL\Frontend
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend;

use WOOPAYWALL\Dependencies\TIVWP\Env;
use WOOPAYWALL\Dependencies\TIVWP\WC\Cart;
use WOOPAYWALL\App;
use WOOPAYWALL\Expiration;
use WOOPAYWALL\Order\Info;
use WOOPAYWALL\PurchasedProduct;

/**
 * Class Alert
 */
class Alert {

	/**
	 * Public access to the __CLASS__.
	 *
	 * @return string
	 */
	public static function get_class() {
		return __CLASS__;
	}

	/**
	 * Print start DIVs for Alert.
	 *
	 * @since 4.0.0
	 *
	 * @param string $our_class CSS class for the wrapper DIV.
	 * @param string $severity  Alert type: 'error', 'message', 'info'.
	 *
	 * @return void
	 */
	protected static function div_alert_start_e( $our_class = '', $severity = 'error' ) {

		$class_for_wrapper = 'woocommerce-notices-wrapper' . ( $our_class ? ' woopaywall-' . preg_replace( '/[^a-zA-Z0-9-\']/', '-', $our_class ) : '' );

		echo '<div class="' . \esc_attr( $class_for_wrapper ) . '">';
		echo '<div class="woocommerce-' . \esc_attr( $severity ) . '" role="alert">';
	}

	/**
	 * Method div_alert_end_e.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	protected static function div_alert_end_e() {
		echo '</div></div>';
	}

	/**
	 * Method alert_e.
	 *
	 * @since 4.0.0
	 *
	 * @param array{msg:string, our_class:string, severity:string, button_url:string, button_text:string} $args
	 *
	 * @return void
	 */
	protected static function alert_e( $args = array() ) {
		$params = array(
			'msg'         => '',
			'our_class'   => '',
			'severity'    => 'error',
			'button_url'  => '',
			'button_text' => '',
		);
		$params = array_merge( $params, $args );

		static::div_alert_start_e( $params['our_class'], $params['severity'] );
		if ( $params['button_text'] && $params['button_url'] ) {
			echo '<a href="' . \esc_url( $params['button_url'] ) . '" class="button wc-forward">' . \esc_html( $params['button_text'] ) . '</a>';
		}
		echo \esc_html( $params['msg'] );
		static::div_alert_end_e();
	}

	/**
	 * Already in Cart.
	 *
	 * @return void
	 */
	public static function already_in_cart() {
		static::alert_e( array(
			'our_class'   => __FUNCTION__,
			'msg'         => \__( 'Already in the shopping cart.', 'paywall-for-woocommerce' ),
			'button_url'  => \wc_get_checkout_url(),
			'button_text' => \__( 'Checkout', 'woocommerce' ),
		) );
	}


	/**
	 * A Pwpass product already in Cart.
	 *
	 * @return void
	 */
	public static function pwpass_in_cart() {
		static::alert_e( array(
			'our_class'   => __FUNCTION__,
			'msg'         => \__( 'Another Paywall Pass already in the shopping cart.', 'paywall-for-woocommerce' ),
			'button_url'  => \wc_get_checkout_url(),
			'button_text' => \__( 'Checkout', 'woocommerce' ),
		) );
	}

	/**
	 * A Pwpass product is active.
	 *
	 * @return void
	 */
	public static function pwpass_active() {
		static::alert_e( array(
			'our_class' => __FUNCTION__,
			'msg'       => \__( 'A Paywall Pass is active.', 'paywall-for-woocommerce' ),
			'severity'  => 'info',
		) );
	}

	/**
	 * Should log in.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public static function should_login() {
		if ( ! \is_user_logged_in() ) {
			static::alert_e( array(
				'msg'       => \__( 'ATTENTION: you should log in.', 'paywall-for-woocommerce' ),
				'our_class' => __FUNCTION__,
			) );
		}
	}

	/**
	 * Must log in to purchase.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public static function must_login_to_purchase() {
		if ( ! \is_user_logged_in() ) {
			static::alert_e( array(
				'msg'       => \__( 'You must be a registered customer to purchase this product.', 'paywall-for-woocommerce' ),
				'our_class' => __FUNCTION__,
			) );
		}
	}

	/**
	 * Thank you.
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public static function get_thanks() {
		ob_start();
		self::thanks();

		return ob_get_clean();
	}

	/**
	 * Thank you.
	 *
	 * @since 2.0.0
	 * @since 3.2.0 Show expiration time.
	 * @since 3.7.0 Use order key if provided.
	 *
	 * @param \WC_Product_Paywall|null $product Product (optional).
	 *
	 * @return void
	 */
	public static function thanks( $product = null ) {
		static::div_alert_start_e( __FUNCTION__, 'message' );

		\esc_html_e( 'Thank you for purchasing!', 'paywall-for-woocommerce' );

		if ( $product ) {
			echo '<br/>';

			/**
			 * 1. Get expiration time from the user session or past orders if logged in.
			 */
			$time_to_expire = App::instance()->get_current_user()->get_time_to_expire( $product->get_id() );

			if ( ! $time_to_expire instanceof \DateInterval ) {
				/**
				 * 2. If an order key provided in the URL, try getting the order from it.
				 *
				 * @since 3.7.0
				 * @since 3.9.1 Fix: check $purchased_product->has_expiration().
				 */

				$order_key = Env::http_get_or_post( Info::ORDER_KEY_PARAMETER );
				if ( $order_key ) {
					$order_id = \wc_get_order_id_by_order_key( $order_key );
					if ( $order_id ) {
						$order_info = new Info( $order_id );
						if (
							$order_info->is_paid() &&
							! $order_info->is_expired() &&
							! $order_info->is_not_mine() &&
							$order_info->has_product( $product->get_id() )
						) {
							$purchased_product = new PurchasedProduct( $product, $order_info );
							if ( $purchased_product->has_expiration() && $purchased_product->is_active() ) {
								$time_to_expire = $purchased_product->get_expiration_interval();
							}
						}
					}

				}
			}

			if ( $time_to_expire instanceof \DateInterval ) {
				echo \esc_html(
					sprintf( // Translators: %s holds the expiration days/hours/...
						\__( 'Access ends in %s', 'paywall-for-woocommerce' ),
						Expiration\ExpireAfter::get_formatted_interval( $time_to_expire ) )
				);
			}
		}

		static::div_alert_end_e();
	}

	/**
	 * On checkout page.
	 *
	 * @param \WC_Checkout $checkout The checkout object.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public static function checkout( $checkout ) {

		if ( \is_user_logged_in() ) {
			// No need to say anything.
			return;
		}

		if ( ! Cart::is_product_type_in_cart( \WC_Product_Paywall::PRODUCT_TYPE ) ) {
			// Not our business.
			return;
		}

		if ( $checkout->is_registration_required() && ! $checkout->is_registration_enabled() ) {
			// Nothing can be done at Checkout. See if registration is enabled on My Account.
			self::prompt_to_register_on_my_account_page();

			return;
		}

		if ( $checkout->is_registration_required() && $checkout->is_registration_enabled() ) {
			// Required and enabled. We are fine.

			return;
		}

		/**
		 * Enabled but not required. Check the box and explain why.
		 *
		 * @since 2.0.0 If expiration is set then the message below is invalid
		 */
		if ( ! App::instance()->getExpirationGlobalsettings()->is_expiration_set() ) {
			if ( ! $checkout->is_registration_required() && $checkout->is_registration_enabled() ) {
				\add_filter( 'woocommerce_create_account_default_checked', '__return_true', App::HOOK_PRIORITY_LATE );
				\add_action(
					'woocommerce_before_checkout_registration_form',
					array( __CLASS__, 'checkout_why_create_account' )
				);
			}
		}
	}

	/**
	 * Prompt to log in or register on My Account page.
	 *
	 * @return void
	 */
	public static function prompt_to_register_on_my_account_page() {

		if ( ! \is_user_logged_in() && 'yes' === \get_option( 'woocommerce_enable_myaccount_registration' ) ) {
			static::alert_e( array(
				'msg'         => \__( 'Login or register a new account:', 'paywall-for-woocommerce' ),
				'our_class'   => __FUNCTION__,
				'severity'    => 'message',
				'button_url'  => \wc_get_page_permalink( 'myaccount' ),
				'button_text' => \__( 'Go to My Account page', 'paywall-for-woocommerce' ),
			) );
		}
	}

	/**
	 * On checkout page, below the "Create an account?" checkbox.
	 *
	 * @param \WC_Checkout $checkout The checkout object.
	 *
	 * @return void
	 */
	public static function checkout_why_create_account( $checkout ) {
		if ( ! $checkout instanceof \WC_Checkout ) {
			return;
		}

		static::alert_e( array(
			'msg'      => \__( 'We recommend you to create an account so you can access the premium content you purchased anytime.', 'paywall-for-woocommerce' ),
			'severity' => 'info',
		) );
	}

	/**
	 * On checkout page, below the order totals.
	 *
	 * @since 2.0.0
	 *
	 * @param string $units Units.
	 * @param int    $value Value.
	 */
	public static function checkout_expiration_terms( $units, $value ) {

		if ( ! Cart::is_product_type_in_cart( \WC_Product_Paywall::PRODUCT_TYPE ) ) {
			// Not our business.
			return;
		}

		static::alert_e( array(
			'msg'      => sprintf( // Translators: %1$d %2$s -- N month(s)/day(s)/hour(s).
				\__( 'Registered customers keep access to premium products for up to %1$d %2$s after purchase.', 'paywall-for-woocommerce' ),
				$value,
				Expiration\Units::get_units_name( $units, $value )
			),
			'severity' => 'info',
		) );
	}

	/**
	 * On single product page, below the order totals.
	 *
	 * @since 2.0.0
	 *
	 * @param string $units Units.
	 * @param int    $value Value.
	 */
	public static function single_product_expiration_terms( $units, $value ) {

		if ( $value > 0 ) {
			$msg =
				sprintf( // Translators: %1$d %2$s -- N month(s)/day(s)/hour(s).
					\__( 'Registered customers keep access to this product for %1$d %2$s after purchase.', 'paywall-for-woocommerce' ),
					$value,
					Expiration\Units::get_units_name( $units, $value )
				);
		} else {
			$msg = \__( 'Registered customers have unlimited access to this product after purchase. No expiration.', 'paywall-for-woocommerce' );
		}

		static::alert_e( array(
			'msg'      => $msg,
			'severity' => 'info',
		) );
	}
}

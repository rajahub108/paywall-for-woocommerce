<?php
/**
 * Generic WP methods.
 *
 * @since        0.0.1
 *
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 * @noinspection PhpUnused
 */

namespace WOOPAYWALL\Dependencies\TIVWP;

/**
 * Class Env
 */
class Env {

	/**
	 * Check if doing AJAX call.
	 *
	 * @return bool
	 */
	public static function is_doing_ajax() {
		return Constants::is_true( 'DOING_AJAX' ) || self::is_doing_wc_ajax();
	}

	/**
	 * Check if doing WooCommerce AJAX call.
	 *
	 * @since 1.11.0 Additional (maybe useless) check for `wc-ajax` in GET.
	 * @return bool
	 */
	public static function is_doing_wc_ajax() {
		return Constants::is_true( 'WC_DOING_AJAX' ) || self::is_parameter_in_http_get( 'wc-ajax' );
	}

	/**
	 * Attempt to check if an AJAX call was originated from admin screen.
	 *
	 * @return bool
	 *
	 * @todo There should be other actions. See $core_actions_get in admin-ajax.php
	 *       Can also check $GLOBALS['_SERVER']['HTTP_REFERER']
	 *       and $GLOBALS['current_screen']->in_admin()
	 */
	public static function is_admin_doing_ajax() {
		return (
			self::is_doing_ajax() &&
			(
				self::is_http_post_action(
					array(
						'heartbeat',
						'inline-save',
						'save-widget',
						'customize_save',
						'woocommerce_load_variations',
						'ajax-tag-search',
						'wc_braintree_paypal_get_client_token',

						/**
						 * WC Checkout Add-ons AJAX actions.
						 *
						 * @see `\SkyVerge\WooCommerce\Checkout_Add_Ons\Admin\AJAX::__construct`
						 */
						'wc_checkout_add_ons_sort_add_ons',
						'wc_checkout_add_ons_enable_disable_add_on',
						'wc_checkout_add_ons_json_search_field',
						'wc_checkout_add_ons_save_order_items',

						/**
						 * WC: add/remove order items on "Edit Order" admin screen.
						 *
						 * @since 1.6.0
						 * @since 1.8.0 Removed 'woocommerce_add_order_item' to handle it in app code.
						 * 'woocommerce_add_order_item',
						 */
						'woocommerce_remove_order_item',
					)
				)
				|| self::is_http_get_action(
					array(
						'woocommerce_shipping_zone_methods_save_changes',
						'woocommerce_shipping_zone_methods_save_settings',
					)
				)
			)
		);
	}

	/**
	 * Do we have a certain 'action' in the HTTP POST?
	 *
	 * @param string|string[] $action The action to check.
	 *
	 * @return bool
	 */
	public static function is_http_post_action( $action ) {

		// PHPCS: WordPress.Security.NonceVerification.Missing is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );

		$action = (array) $action;

		return ( ! empty( $_POST['action'] ) && in_array( $_POST['action'], $action, true ) );
	}

	/**
	 * Do we have a certain 'action' in the HTTP GET?
	 *
	 * @param string|string[] $action The action to check.
	 *
	 * @return bool
	 */
	public static function is_http_get_action( $action ) {

		// PHPCS: WordPress.Security.NonceVerification.Recommended is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );

		$action = (array) $action;

		return ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], $action, true ) );
	}

	/**
	 * True if I am in the Admin Panel, logged in, not doing AJAX.
	 *
	 * @return bool
	 */
	public static function in_wp_admin() {
		return ( \is_admin() && ! self::is_doing_ajax() && \get_current_user_id() );
	}

	/**
	 * True if I am on a front page (not admin area), or doing AJAX from the front.
	 *
	 * @return bool
	 */
	public static function on_front() {
		return ! \is_admin() || ( self::is_doing_ajax() && ! self::is_admin_doing_ajax() );
	}

	/**
	 * Wrap debug_backtrace to avoid PHPCS warnings.
	 *
	 * @see   debug_backtrace()
	 * @since 1.1.1
	 *
	 * @param int $options [optional]
	 * @param int $limit   [optional]
	 *
	 * @return array
	 */
	public static function get_trace( $options = DEBUG_BACKTRACE_PROVIDE_OBJECT, $limit = 0 ) {
		static $fn = 'debug_backtrace';

		return $fn( $options, $limit );
	}

	/**
	 * Check if was called by a specific function (could be any levels deep).
	 *
	 * @param callable|string $method Function name or array(class,function).
	 *
	 * @return bool True if Function is in backtrace.
	 */
	public static function is_function_in_backtrace( $method ) {
		$function_in_backtrace = false;

		// Parse callable into class and function.
		if ( is_string( $method ) ) {
			$function_name = $method;
			$class_name    = '';
		} elseif ( is_array( $method ) && isset( $method[0], $method[1] ) ) {
			list( $class_name, $function_name ) = $method;
		} else {
			return false;
		}

		// Traverse backtrace and stop if the callable is found there.
		foreach ( self::get_trace() as $_ ) {
			if ( isset( $_['function'] ) && $_['function'] === $function_name ) {
				$function_in_backtrace = true;
				if ( $class_name && isset( $_['class'] ) && $_['class'] !== $class_name ) {
					$function_in_backtrace = false;
				}
				if ( $function_in_backtrace ) {
					break;
				}
			}
		}

		return $function_in_backtrace;
	}

	/**
	 * To call {@see is_function_in_backtrace()} with the array of parameters.
	 *
	 * @param callable[] $callables Array of callables.
	 *
	 * @return bool True if any of the pair is found in the backtrace.
	 */
	public static function is_functions_in_backtrace( array $callables ) {
		foreach ( $callables as $callable ) {
			if ( self::is_function_in_backtrace( $callable ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the method that initiated the filter.
	 *
	 * @return array|false
	 */
	public static function get_hook_caller() {

		$next_stop = false;
		foreach ( self::get_trace() as $_ ) {
			if ( $next_stop ) {
				return $_;
			}
			/**
			 * Find the {@see \apply_filters()} function,
			 * not the {@see \WP_Hook::apply_filters()} (thus, class must not present).
			 */
			if ( empty( $_['class'] ) && isset( $_['function'] ) && 'apply_filters' === $_['function'] ) {
				// Found. So, the next trace element is the caller.
				$next_stop = true;
			}
		}

		return false;
	}

	/**
	 * Returns the current URL.
	 * There is no method of getting the current URL in WordPress.
	 * Various snippets published on the Web use a combination of home_url and add_query_arg.
	 * However, none of them work when WordPress is installed in a subfolder.
	 * The method below looks valid. There is a theoretical chance of HTTP_HOST tampered, etc.
	 * However, the same line of code is used by the WordPress core,
	 * for example in {@see wp_admin_canonical_url()}, so we are going to use it, too
	 * *
	 * Note that #hash is always lost because it's a client-side parameter.
	 * We might add it using a JavaScript call.
	 *
	 * @noinspection HttpUrlsUsage
	 */
	public static function current_url() {
		return \set_url_scheme( 'http://' . self::http_host() . self::request_uri( '/' ) );
	}

	/**
	 * Returns the value of the specified parameter in the HTTP GET
	 *
	 * @param string $name The parameter name.
	 *
	 * @return string
	 * @deprecated 1.9.0 Use {@see get_http_get_parameter}
	 */
	public static function http_get( $name ) {
		// PHPCS: WordPress.Security.NonceVerification.Recommended is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );

		return empty( $_GET[ $name ] ) ? '' : \sanitize_title( \wp_unslash( $_GET[ $name ] ) );
	}

	/**
	 * Do we have a certain query parameter in the HTTP GET?
	 *
	 * @param string          $name   The parameter name.
	 * @param string|string[] $values The values to check.
	 *
	 * @return bool
	 */
	public static function is_http_get( $name, $values ) {
		$values = (array) $values;

		return in_array( self::get_http_get_parameter( $name ), $values, true );
	}

	/**
	 * Returns the value of the specified parameter in the HTTP POST
	 *
	 * @since      1.2.0
	 *
	 * @param string $name The parameter name.
	 *
	 * @return string
	 * @deprecated 1.9.0 Use {@see get_http_post_parameter}
	 *
	 */
	public static function http_post( $name ) {
		// PHPCS: WordPress.Security.NonceVerification.Recommended is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );

		return isset( $_POST[ $name ] )
			? \sanitize_text_field( \wp_unslash( $_POST[ $name ] ) )
			: '';
	}

	/**
	 * Returns the value of the specified parameter in the HTTP GET or POST
	 *
	 * @since 1.8.0
	 * @since 1.11.0 Refactored to use new functions.
	 *
	 * @param string $name The parameter name.
	 *
	 * @return string
	 */
	public static function http_get_or_post( $name ) {
		if ( self::is_parameter_in_http_get( $name ) ) {
			return self::get_http_get_parameter( $name );
		} elseif ( self::is_parameter_in_http_post( $name ) ) {
			return self::get_http_post_parameter( $name );
		} else {
			return '';
		}
	}

	/**
	 * Returns the value of the specified parameter in the HTTP POST array.
	 *
	 * @since 1.2.0
	 *
	 * @param string $name The parameter name.
	 * @param string $key  The array key.
	 *
	 * @return string
	 */
	public static function http_post_array( $name, $key ) {
		// PHPCS: WordPress.Security.NonceVerification.Recommended is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );

		return isset( $_POST[ $name ][ $key ] )
			? \sanitize_text_field( \wp_unslash( $_POST[ $name ][ $key ] ) )
			: '';
	}

	/**
	 * Returns sanitized $_SERVER['REQUEST_URI'].
	 *
	 * @since 1.3.0
	 * @since 1.5.0 Can specify default value.
	 *
	 * @param string $default Default to return when unset.
	 *
	 * @return string
	 */
	public static function request_uri( $default = '' ) {
		if ( ! isset( $_SERVER['REQUEST_URI'] ) ) {
			// Something abnormal.
			return $default;
		}

		return \esc_url_raw( \wp_unslash( $_SERVER['REQUEST_URI'] ) );
	}

	/**
	 * Returns sanitized $_SERVER['HTTP_HOST'].
	 *
	 * @since 1.5.0
	 *
	 * @param string $default Default to return when unset.
	 *
	 * @return string
	 */
	public static function http_host( $default = 'localhost' ) {
		if ( ! isset( $_SERVER['HTTP_HOST'] ) ) {
			// Something abnormal. Maybe WP-CLI.
			return $default;
		}

		return \sanitize_text_field( \wp_unslash( $_SERVER['HTTP_HOST'] ) );
	}

	/**
	 * Get the current admin page.
	 *
	 * @since 1.8.0
	 * @return string $page
	 */
	public static function pagenow() {
		/**
		 * Set in wp-includes/vars.php
		 */
		global $pagenow;

		return ( isset( $pagenow ) ? $pagenow : '' );
	}

	/**
	 * Check if the current $pagenow equals to...
	 *
	 * @since 1.8.0
	 *
	 * @param string|string[] $page Page(s) to check.
	 *
	 * @return bool
	 */
	public static function is_pagenow( $page ) {
		return in_array( self::pagenow(), (array) $page, true );
	}

	/**
	 * Get the plugin page ID in admin.
	 *
	 * @since      1.8.0
	 * @return string
	 * @example    On wp-admin/index.php?page=woothemes-helper, will return `woothemes-helper`.
	 */
	public static function plugin_page() {
		/**
		 * Set in wp-admin/admin.php
		 *
		 * @global string $plugin_page
		 */
		global $plugin_page;

		return ( isset( $plugin_page ) ? $plugin_page : '' );
	}

	/**
	 * Check if the current $plugin_page equals to...
	 *
	 * @since 1.8.0
	 *
	 * @param string|string[] $plugin_page Page(s) to check.
	 *
	 * @return bool
	 */
	public static function is_plugin_page( $plugin_page ) {
		return in_array( self::plugin_page(), (array) $plugin_page, true );
	}

	/**
	 * True if doing REST API request.
	 *
	 * @since 1.8.0
	 *
	 * @return bool
	 */
	public static function is_doing_rest() {

		if ( self::is_doing_ajax() ) {
			return false;
		}

		/**
		 * See:
		 * wp-includes\rest-api.php
		 * wp-includes\load.php
		 */
		if ( defined( 'REST_REQUEST' ) || \wp_is_json_request() ) {
			return true;
		}

		return false;
	}

	/**
	 * True if a parameter exists in $_GET.
	 *
	 * @since 1.9.0
	 * @since 1.11.0 Use array_key_exists; shut bogus nonce requirement.
	 *
	 * @param string $name The parameter name.
	 *
	 * @return bool
	 */
	public static function is_parameter_in_http_get( $name ) {
		// PHPCS: WordPress.Security.NonceVerification.Missing is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );
		return array_key_exists( $name, $_GET );
	}

	/**
	 * True if a parameter exists in $_POST.
	 *
	 * @since 1.9.0
	 * @since 1.11.0 Use array_key_exists; shut bogus nonce requirement.
	 *
	 * @param string $name The parameter name.
	 *
	 * @return bool
	 */
	public static function is_parameter_in_http_post( $name ) {
		// PHPCS: WordPress.Security.NonceVerification.Missing is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );
		return array_key_exists( $name, $_POST );
	}

	/**
	 * Get sanitized_value from array.
	 *
	 * @since 1.9.0
	 *
	 * @param array  $array The array.
	 * @param string $key   The key.
	 *
	 * @return array|string The value.
	 */
	protected static function get_sanitized_value( array $array, $key ) {
		$value = '';

		if ( array_key_exists( $key, $array ) ) {
			$value = $array[ $key ];
		}

		if ( '' !== $value ) {
			$value = \wp_unslash( $value );

			if ( is_string( $value ) ) {
				$value = \sanitize_text_field( $value );
			}
		}

		return $value;
	}

	/**
	 * Get a $_GET parameter value.
	 *
	 * @since 1.9.0
	 * @since 1.11.0 Shut bogus nonce requirement.
	 *
	 * @param string $key Parameter name.
	 *
	 * @return array|string
	 */
	public static function get_http_get_parameter( $key ) {
		// PHPCS: WordPress.Security.NonceVerification.Missing is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );
		return self::get_sanitized_value( $_GET, $key );
	}

	/**
	 * Get a $_POST parameter value.
	 *
	 * @since 1.9.0
	 * @since 1.11.0 Shut bogus nonce requirement.
	 *
	 * @param string $key Parameter name.
	 *
	 * @return array|string
	 */
	public static function get_http_post_parameter( $key ) {
		// PHPCS: WordPress.Security.NonceVerification.Missing is invalid in the context of this method.
		0 && \wp_verify_nonce( '' );
		return self::get_sanitized_value( $_POST, $key );
	}
}

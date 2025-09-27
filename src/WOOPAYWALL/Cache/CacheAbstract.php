<?php
/**
 * Cache buster. Needed for not logged-in visitors to see their versions of the pages,
 * and not cached by someone else.
 *
 * @since 3.4.0
 * Copyright (c) 2022, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Cache;

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;
use WOOPAYWALL\Dependencies\TIVWP\Constants;
use WOOPAYWALL\Dependencies\TIVWP\Env;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;
use WOOPAYWALL\Dependencies\TIVWP\WC\WCEnv;
use WOOPAYWALL\Frontend\Session;
use WOOPAYWALL\Log;

/**
 * Class CacheBuster.
 *
 * @since 3.4.0
 */
abstract class CacheAbstract extends Hookable {

	/**
	 * Options table key.
	 *
	 * @since 3.4.1
	 *
	 * @var string
	 */
	const OPTION_CACHE_BUSTER_ENABLED = 'cache_buster_enabled';

	/**
	 * Options table default.
	 *
	 * @since 3.4.1
	 *
	 * @var string
	 */
	const OPTION_CACHE_BUSTER_ENABLED_DEFAULT = 'no';

	/**
	 * Hash name
	 *
	 * @since 3.4.0
	 * @var string
	 */
	const HASH_NAME = 'TIVWP_hash';

	/**
	 * Empty hash value - when not needed.
	 *
	 * @since 3.4.0
	 * @var string
	 */
	const HASH_VALUE_NONE = 'default'; //'NONE';

	/**
	 * Hash value
	 *
	 * @since 3.4.0
	 * @var string
	 */
	protected static $hash_value = '';

	/**
	 * Getter for hash.
	 *
	 * @since 3.4.0
	 * @return string
	 */
	public static function get_hash() {
		return self::$hash_value;
	}

	/**
	 * Setter for hash.
	 *
	 * @since 3.4.0
	 *
	 * @param string $hash_value The hash value.
	 *
	 * @return void
	 */
	public static function set_hash( $hash_value ) {
		if ( self::$hash_value === $hash_value ) {
			Log::debug( new Message( array( 'hash', 'same', self::$hash_value ) ) );
		} else {
			Log::debug( new Message( array( 'HASH', 'SET', $hash_value, 'old=', self::$hash_value ) ) );
			self::$hash_value = $hash_value;
		}
	}

	/**
	 * Un-Setter for hash.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public static function unset_hash() {
		self::set_hash( self::HASH_VALUE_NONE );
	}

	/**
	 * Is hash set?
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	public static function is_hash() {
		return self::HASH_VALUE_NONE !== self::$hash_value;
	}

	/**
	 * Logged-in cookie name.
	 *
	 * @since 3.4.0
	 * @var string
	 */
	const LOGGED_IN_COOKIE_NAME = 'woopaywall_logged_in';

	/**
	 * Setup actions and filters.
	 *
	 * @since 3.4.0
	 * @since 3.4.1 Check if enabled in Settings.
	 * @return void
	 */
	public function setup_hooks() {


		if ( Env::in_wp_admin() ) {
			\add_filter(
				'woocommerce_attribute_show_in_nav_menus',
				array( $this, 'filter__woocommerce_attribute_show_in_nav_menus' ),
				App::HOOK_PRIORITY_LATE,
				2
			);

			return;
		}

		\add_action(
			'woocommerce_cart_item_removed',
			array( $this, 'action__on_cart_change' ),
			App::HOOK_PRIORITY_EARLY
		);

		\add_action(
			'wc_ajax_get_refreshed_fragments',
			array( $this, 'action__on_cart_change' ),
			9
		);

		\add_action(
			'wc_ajax_remove_from_cart',
			array( $this, 'action__on_cart_change' ),
			0
		);

		\add_action(
			'woocommerce_cart_loaded_from_session',
			array( $this, 'action__on_cart_change' )
		);
		\add_action(
			'woocommerce_add_to_cart',
			array( $this, 'action__on_cart_change' )
		);
		\add_action(
			'woocommerce_cart_emptied',
			array( $this, 'action__on_cart_change' )
		);
		\add_action(
			'woocommerce_cart_item_restored',
			array( $this, 'action__on_cart_change' )
		);

		if ( $this->is_request_to_ignore() ) {
			return;
		}

		\add_action( 'init', array( $this, 'action__init' ) );

		/**
		 * Load TIVWP JS library.
		 *
		 * @since 3.5.0
		 */
		\add_action( 'wp_enqueue_scripts', function () {
			\wp_enqueue_script(
				'tivwp',
				App::instance()->plugin_dir_url() . '/assets/js/TIVWP.js',
				array(),
				PAYWALL_FOR_WOOCOMMERCE_VERSION,
				false
			);
		} );

		$this->setup_frontend_redirect();
	}

	/**
	 * Setup frontend redirect.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	protected function setup_frontend_redirect() {
		\add_action( 'wp_footer',
			array( $this, 'action__wp_footer' ),
			App::HOOK_PRIORITY_LATE
		);
	}

	/**
	 * Setup backend redirect.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	protected function setup_backend_redirect() {
		\add_action(
			'template_redirect',
			array( $this, 'action__template_redirect' ),
			App::HOOK_PRIORITY_EARLY
		);
	}

	/**
	 * Main callback.
	 * Hooked to "init" to know whether the user is logged-in or not.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function action__init() {

		if ( \is_user_logged_in() ) {
			return;
		}

		$this->add_hash_to_urls();

		$this->setup_backend_redirect();
	}

	/**
	 * Add hash to URLs.
	 *
	 * @since 3.5.0
	 *
	 * @return void
	 */
	protected function add_hash_to_urls() {

		\add_filter(
			'page_link',
			array( $this, 'filter__page_link' ),
			App::HOOK_PRIORITY_LATE,
			3
		);

		\add_filter(
			'woopaywall_product_permalink',
			array( $this, 'filter__woopaywall_product_permalink' ),
			App::HOOK_PRIORITY_LATE
		);

		\add_filter(
			'woocommerce_loop_product_link',
			array( $this, 'filter__woocommerce_loop_product_link' ),
			App::HOOK_PRIORITY_LATE,
			2
		);

		\add_filter( 'term_link',
			array( $this, 'filter__term_link' ),
			App::HOOK_PRIORITY_LATE,
			3
		);
	}

	/**
	 * Callback to regenerate hash.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function action__on_cart_change() {

		try {
			$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? \wc_clean( \wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : 'NONE';

			$trace     = Env::get_trace();
			$hook_name = $trace[4]['args'][0];
			Log::debug( new Message( array( '- Hook', $hook_name, 'UA', $ua ) ) );
		} catch ( \Exception $exception ) {
			Log::error( $exception );
		}

		static $is_already_done = false;
		if ( $is_already_done ) {
			Log::debug( new Message( 'Already done.' ) );

			// return;
		}

		$this->generate_hash_value();
		$this->set_hash_cookie();
		$this->set_logged_in_cookie();
		$is_already_done = true;
	}

	/**
	 * Callback to redirect to URL with hash.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	public function action__template_redirect() {

		$this->maybe_redirect();
	}

	/**
	 * Callback to put JS that redirects cached page
	 * if the hash there doesn't match the current one.
	 *
	 * @since        3.4.0
	 */
	public function action__wp_footer() {

		// For both logged-in or not, but exclude pages.
		if ( ! $this->is_hash_required_for_the_current_page() ) {
			return;
		}

		/*@formatter:off*/
		?>
		<script id="tivwp_cache_buster">
			const TIVWP_HASH = "<?php echo \esc_js( self::get_hash() ); ?>", TIVWP_HASH_NAME = "<?php echo \esc_js( self::HASH_NAME ); ?>"; TIVWP.cacheBuster(TIVWP_HASH, TIVWP.getCookie(TIVWP_HASH_NAME)); jQuery(document.body).on("added_to_cart removed_from_cart", TIVWP.pageReload);
		</script>
		<?php
		/*@formatter:on*/
	}

	/**
	 * Callback to add hash to the Paywall product URLs.
	 *
	 * @see   filter__woocommerce_loop_product_link for the additional functionality.
	 *
	 * @since 3.4.0
	 *
	 * @param string $permalink The URL.
	 *
	 * @return string
	 */
	public function filter__woopaywall_product_permalink( $permalink ) {

		return $this->maybe_add_hash_to_url( $permalink );
	}

	/**
	 * Callback to add hash to the page link.
	 *
	 * @param string $link    The page's permalink.
	 * @param int    $post_id The ID of the page.
	 * @param bool   $sample  Is it a sample permalink.
	 *
	 * @todo Filter to add pages manually.
	 * @return string
	 */
	public function filter__page_link( $link, $post_id, $sample ) {
		if ( ! $sample && $this->is_hash_required_for_page( \get_post( $post_id ) ) ) { // HPOS OK.
			$link = $this->maybe_add_hash_to_url( $link );
		}

		return $link;
	}

	/**
	 * Callback to add hash to the Paywall product URLs in WooCommerce loops.
	 *
	 * @since 3.4.0
	 *
	 * @param string              $permalink The URL.
	 * @param AbstractPaywallProductSimple $product   The product object.
	 *
	 * @return string
	 */
	public function filter__woocommerce_loop_product_link( $permalink, $product ) {
		if ( $product instanceof AbstractPaywallProductSimple ) {
			$permalink = $this->maybe_add_hash_to_url( $permalink );
		}

		return $permalink;
	}

	/**
	 * Callback to add hash to the links to WooCommerce category/tag archives.
	 *
	 * @since        3.4.0
	 *
	 * @param string   $termlink Term link URL.
	 * @param \WP_Term $term     (Unused) Term object.
	 * @param string   $taxonomy Taxonomy slug.
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function filter__term_link( $termlink, $term, $taxonomy ) {

		// TODO: other WC taxonomies? Attributes?
		if ( $this->is_woocommerce_taxonomy( $taxonomy ) ) {
			$termlink = $this->maybe_add_hash_to_url( $termlink );
		}

		return $termlink;
	}

	/**
	 * Callback to see WooCommerce attributes in Admin -> Menus.
	 *
	 * @since 3.4.0
	 *
	 * @param bool   $yes_no To show or not.
	 * @param string $name   The attribute name.
	 *
	 * @return bool
	 */
	public function filter__woocommerce_attribute_show_in_nav_menus( $yes_no = false, $name = '' ) {

		// This check is doing nothing. Might want to add a filter here.
		if ( \str_starts_with( $name, 'pa_' ) ) {
			$yes_no = true;
		}

		return $yes_no;
	}

	/**
	 * Return true is taxonomy is one of WooCommerce's.
	 *
	 * @since 3.4.0
	 *
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return bool
	 */
	protected function is_woocommerce_taxonomy( $taxonomy ) {
		return in_array( $taxonomy, array(
				'product_cat',
				'product_tag',
			), true ) || str_starts_with( $taxonomy, 'pa_' );
	}

	/**
	 * (Un)Set cookie unless already.
	 *
	 * @since 3.5.0
	 *
	 * @param string $name  Cookie name.
	 * @param string $value Cookie value.
	 *
	 * @return void
	 */
	protected function maybe_set_cookie( $name, $value ) {
		if ( headers_sent() ) {
			Log::error( new Message( array( 'Trying to set cookie after headers already sent', $name, $value ) ) );

			return;
		}

		if (
			( empty( $_COOKIE[ $name ] ) && empty( $value ) )
			|| ( isset( $_COOKIE[ $name ] ) && $value === $_COOKIE[ $name ] )
		) {
			Log::debug( new Message( array( 'cookie', 'same', $name, $value ) ) );

			return;
		}

		setcookie( $name, $value, 0, '/' );
		$_COOKIE[ $name ] = $value;
		Log::debug( new Message( array( 'COOKIE', 'SET', $name, $value ) ) );
	}

	/**
	 * Set hash cookie.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	protected function set_hash_cookie() {
		$this->maybe_set_cookie( self::HASH_NAME, self::get_hash() );
	}

	/**
	 * Set logged-in cookie.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	protected function set_logged_in_cookie() {
		$this->maybe_set_cookie( self::LOGGED_IN_COOKIE_NAME, \is_user_logged_in() ? 'Y' : 'N' );
	}

	/**
	 * Generate hash value.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	protected function generate_hash_value() {

		/**
		 * Assume that logged-in users always see non-cached pages.
		 * <code>
		 * if ( \is_user_logged_in() ) {
		 *     self::unset_hash();
		 *    return;
		 *  }
		 * </code>
		 */

		$wc_cart_hash      = \WC()->cart->get_cart_hash();
		$orders_in_session = Session::get_order_ids();

		if ( empty( $wc_cart_hash ) && empty( $orders_in_session ) ) {
			self::unset_hash();

			return;
		}

		$orders_hash = \maybe_serialize( $orders_in_session );
		$to_hash     = $wc_cart_hash . '|' . $orders_hash;
		$wp_hash     = \wp_hash( $to_hash );
		$hash_value  = substr( $wp_hash, 0, 12 );

		self::set_hash( $hash_value );
	}

	/**
	 * Maybe add hash to URL.
	 *
	 * @since 3.4.0
	 *
	 * @param string $url URL.
	 *
	 * @return string
	 */
	protected function maybe_add_hash_to_url( $url ) {

		if ( self::is_hash() ) {
			$url = \add_query_arg( self::HASH_NAME, self::get_hash(), $url );
		}

		return $url;
	}

	/**
	 * Does the current page have products?
	 * Could be a WC page or a page with WC blocks.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	protected function is_hash_required_for_the_current_page() {

		global $product;
		if ( $product instanceof AbstractPaywallProductSimple ) {
			return true;
		}

		// Covers "shop" and WC taxonomies (cat, tag) - *** for the current page ***.
		if ( \is_shop() || \is_product_taxonomy() ) {
			return true;
		}
		if ( $this->has_woocommerce_blocks( \get_post() ) ) { // HPOS OK.
			return true;
		}

		return false;
	}

	/**
	 * Does a specific page have products?
	 * Could be a WC page or a page with WC blocks.
	 *
	 * @since 3.4.0
	 *
	 * @param \WP_Post $page Page object.
	 *
	 * @return bool
	 */
	protected function is_hash_required_for_page( \WP_Post $page ) {

		// Check is_woocommerce for the page passed as the parameter.
		// Other pages, probably, covered by specific filters.
		if ( \wc_get_page_id( 'shop' ) === $page->ID ) {
			return true;
		}

		if ( $this->has_woocommerce_blocks( $page ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Does page/post have WooCommerce blocks?
	 *
	 * @since 3.4.0
	 *
	 * @param \WP_Post $page The page
	 *
	 * @return bool
	 * @todo  Move to TIVWP
	 */
	protected function has_woocommerce_blocks( $page ) {

		// TODO: move to a separate method.
		if ( \has_shortcode( $page->post_content, 'woopaywall_purchased_products' ) ) {
			return true;
		}

		$blocks = \parse_blocks( $page->post_content );
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && is_string( $block['blockName'] ) && \str_starts_with( $block['blockName'], 'woocommerce/' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Is request to be ignored?
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	protected function is_request_to_ignore() {

		if ( Constants::is_true( 'DOING_CRON' ) ) {
			return true;
		}

		// TODO: refreshing fragments?
		if ( Env::is_doing_ajax() ) {
			return true;
		}

		// Assume that cached pages are not served on POST requests.
		// ... excluding cart operations
		if ( Env::is_parameter_in_http_post( 'add-to-cart' ) ) {
			return false;
		}

		0 && \wp_verify_nonce( '' );
		if ( ! empty( $_POST ) ) {
			return true;
		}

		// A crawler? Pass.
		if ( WCEnv::is_a_bot() ) {
			return true;
		}

		return false;
	}

	/**
	 * Not our business: robots.txt, favicon.ico, etc.
	 *
	 * @since 3.4.0
	 *
	 * @return bool
	 */
	protected function is_service_page_requested() {
		return ( \is_robots() || \is_favicon() || \is_feed() || \is_comment_feed() || \is_privacy_policy() );
	}

	/**
	 * Redirect to the URL with the correct hash, if necessary.
	 *
	 * @since 3.4.0
	 *
	 * @return void
	 */
	protected function maybe_redirect() {
		if ( ! self::is_hash() ) {
			return;
		}
		if ( $this->is_service_page_requested() ) {
			return;
		}

		if ( ! $this->is_hash_required_for_the_current_page() ) {
			return;
		}


		$hash_value   = self::get_hash();
		$current_hash = Env::get_http_get_parameter( self::HASH_NAME );
		if ( empty( $current_hash ) || $current_hash !== $hash_value ) {
			global $wp;

			$redirect_url = \trailingslashit( \home_url( $wp->request ) );

			if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
				$redirect_url = \add_query_arg( \wc_clean( \wp_unslash( $_SERVER['QUERY_STRING'] ) ), '', $redirect_url );
			}

			if ( ! \get_option( 'permalink_structure' ) ) {
				$redirect_url = \add_query_arg( $wp->query_string, '', $redirect_url );
			}

			$redirect_url = \add_query_arg( self::HASH_NAME, $hash_value, $redirect_url );

			// https://developer.mozilla.org/en-US/docs/Web/HTTP/Status/307
			\wp_safe_redirect( \esc_url_raw( $redirect_url ), 307 );
			exit;
		}
	}
}

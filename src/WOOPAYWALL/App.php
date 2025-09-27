<?php
/**
 * Application.
 *
 * @since   1.0.0
 * @package WOOPAYWALL
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL;

use WOOPAYWALL\Admin\Notices;
use WOOPAYWALL\Cache\CacheController;
use WOOPAYWALL\Dependencies\TIVWP\AbstractApp;
use WOOPAYWALL\Dependencies\TIVWP\Constants;
use WOOPAYWALL\Dependencies\TIVWP\InterfaceHookable;
use WOOPAYWALL\Dependencies\TIVWP\WC\Metabox\MetaboxEngine;
use WOOPAYWALL\MetaSet\MetaSetPaywall;
use WOOPAYWALL\Order\OrderEmail;
use WOOPAYWALL\TinyMCE\TinyMCEController;
use WOOPAYWALL\User\Customer;
use WOOPAYWALL\User\Guest;
use WOOPAYWALL\User\UserFactory;

/**
 * Class App
 */
class App extends AbstractApp implements InterfaceHookable {

	/**
	 * WooCommerce's settings tab slug.
	 *
	 * @since   1.0.0
	 * @since   3.9.3 Moved here from \WOOPAYWALL\Settings\Tab.
	 *
	 * @var string
	 */
	const TAB_SLUG = 'paywall';

	/**
	 * Required WC version.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	const WC_REQUIRES_AT_LEAST = '6.9.0';

	/**
	 * Version string to be used in the `ver` parameter of enqueue script/style.
	 *
	 * @since 4.0.0
	 * @var string
	 */
	public static $VER = '';

	/**
	 * Expiration settings.
	 *
	 * @since 2.0.0
	 *
	 * @var Expiration\GlobalSettings
	 */
	protected $expiration_global_settings;

	/**
	 * Expiration settings getter.
	 *
	 * @since        2.0.0
	 *
	 * @return Expiration\GlobalSettings
	 * @noinspection PhpUnused
	 */
	public function getExpirationGlobalSettings() {
		return $this->expiration_global_settings;
	}

	/**
	 * Current user lazy getter.
	 *
	 * @since 2.0.0
	 *
	 * @return Customer|Guest
	 */
	public function get_current_user() {
		static $current_user;

		/**
		 * WC does not like Elvis.
		 *
		 * @noinspection PhpTernaryExpressionCanBeReducedToShortVersionInspection
		 */
		return $current_user ? $current_user : $current_user = UserFactory::get_current_user();
	}

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function setup_hooks() {

		/**
		 * Required by {@see AbstractApp::load_translations()}.
		 */
		$this->textdomain = 'paywall-for-woocommerce';

		self::$VER = Constants::is_true( 'WP_LOCAL_DEV' ) ? (string) time() : PAYWALL_FOR_WOOCOMMERCE_VERSION;

		// Check prerequisites and continue.
		\add_action( 'plugins_loaded', array( $this, 'setup_hooks_after_plugins_loaded' ), self::HOOK_PRIORITY_LATE );
	}

	/**
	 * This runs only after we checked the prerequisites.
	 *
	 * @see      Notices::requirements()
	 * @internal filter.
	 */
	public function setup_hooks_after_plugins_loaded() {

		if ( ! self::requirements_met() ) {
			\add_action( 'admin_notices', array( Notices::get_class(), 'requirements' ) );

			return;
		}

		$this->load_translations();

		// The product type class is not namespaced.
		require_once __DIR__ . '/../WC_Product_Paywall.php';
		require_once __DIR__ . '/../WC_Product_Pwpass.php';

		$this->expiration_global_settings = new Expiration\GlobalSettings();
		$this->expiration_global_settings->setup_hooks();

		$order_meta = new Order\Meta();
		$order_meta->setup_hooks();

		( new OrderEmail() )->setup_hooks();

		( new MetaboxEngine( new MetaSetPaywall(), array( 'shop_order' ) ) )->setup_hooks();

		$integration_controller = new Integration\Controller();
		$integration_controller->setup_hooks();

		$my_account = new MyAccount\Controller();
		$my_account->setup_hooks();

		$alter_controller = new Alter\Controller();
		$alter_controller->setup_hooks();

		( new \WC_Product_Paywall() )->setup_hooks();
		( new \WC_Product_Pwpass() )->setup_hooks();

		( new CacheController() )->setup_hooks();

		( new TinyMCEController() )->setup_hooks();

		\add_action( 'wp', array( $this, 'frontend' ), self::HOOK_PRIORITY_LATE );
		\add_action( 'admin_init', array( $this, 'admin' ), self::HOOK_PRIORITY_LATE );
		\add_action( 'init', array( $this, 'settings' ), self::HOOK_PRIORITY_EARLY );


		// Admin preview hooks on 'query_vars' and so cannot be placed in the frontend controller.
		( new AdminPreview() )->setup_hooks();

		// Admin bar is visible on both front- and backend.
		( new AdminBar() )->setup_hooks();

		/**
		 * TODO: tasks for the future releases.
		 * // return apply_filters( 'woocommerce_csv_product_import_mapping_options', $options, $item );
		 * // $object = apply_filters( 'woocommerce_product_import_pre_insert_product_object', $object, $data );
		 */
	}

	/**
	 * Check the prerequisites.
	 *
	 * @return bool
	 */
	public static function requirements_met() {
		return class_exists( 'WooCommerce', false ) && version_compare( \wc()->version, self::WC_REQUIRES_AT_LEAST, '>=' );
	}

	/**
	 * Settings controller.
	 *
	 * @note  It's not under {@see admin()} because WC saves settings before the `admin_init` hook.
	 *
	 * @since 2.0.0
	 * @return void
	 */
	public function settings() {

		$settings = new Settings\Controller();
		$settings->setup_hooks();
	}

	/**
	 * Frontend controller.
	 *
	 * @return void
	 */
	public function frontend() {

		$front_end = new Frontend\Controller();
		$front_end->setup_hooks();
	}

	/**
	 * Admin area controller.
	 *
	 * @return void
	 */
	public function admin() {

		$admin = new Admin\Controller();
		$admin->setup_hooks();
	}

	/**
	 * The support URL.
	 *
	 * @since 2.0.0
	 *
	 * @var string
	 */
	protected $url_support = 'https://woocommerce.com/my-account/contact-support/';

	/**
	 * Getter for $this->url_support.
	 *
	 * @since 2.0.0
	 *
	 * @return string
	 */
	public function getUrlSupport() {
		return $this->url_support;
	}

	/**
	 * The docs URL.
	 *
	 * @since 3.9.3
	 * @return string
	 */
	public static function url_docs() {
		return 'https://woocommerce.com/document/paywall/';
	}
}

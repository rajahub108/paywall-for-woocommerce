<?php
/**
 * Endpoint abstract class.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\MyAccount;

use WOOPAYWALL\Dependencies\TIVWP\Abstracts\AbstractData;
use WOOPAYWALL\Dependencies\TIVWP\Env;
use WOOPAYWALL\Dependencies\TIVWP\InterfaceHookable;
use WOOPAYWALL\Dependencies\TIVWP\WC\WCEnv;

/**
 * Class AbstractEndpoint
 *
 * @package WOOPAYWALL\MyAccount
 */
abstract class AbstractEndpoint extends AbstractData implements InterfaceHookable {

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @var array $data
	 */
	protected $data = array(
		'id'    => '',
		'title' => '',
	);

	/**
	 * Static: ID
	 *
	 * @return string
	 */
	public static function id() {
		return '';
	}

	/**
	 * Static: title
	 *
	 * @return string
	 */
	public static function title() {
		return '';
	}

	/**
	 * Insert tab in My Account before this one.
	 *
	 * @return string
	 */
	abstract protected function insert_before();

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this
			->set_id( static::id() )
			->set_title( static::title() );
	}

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		if ( WCEnv::is_rest_api_call() ) {

			return;
		}

		if ( Env::in_wp_admin() ) {
			$this->hooks_admin();
		} else {
			$this->hooks_frontend();
		}
	}

	/**
	 * Hooks for frontend.
	 */
	protected function hooks_frontend() {
		/**
		 * Set page title for the endpoint.
		 *
		 * @see \WC_Query::get_endpoint_title
		 */
		\add_filter( 'woocommerce_endpoint_' . $this->get_slug() . '_title',
			array( $this, 'get_title' )
		);

		/**
		 * Get page title for the endpoint by ID, regardless of the slug, which is dynamic.
		 */
		\add_filter( 'woopaywall_endpoint_id_' . static::id() . '_slug',
			array( $this, 'get_slug' )
		);

		/**
		 * This must be done on frontend. In admin, rules remain the same. Tried. No time to fight.
		 */
		\add_action( 'wp_loaded', array( $this, 'maybe_flush_rewrite_rules' ) );

		\add_action( 'woocommerce_account_' . $this->get_slug() . '_endpoint',
			array( $this, 'action__endpoint_content' )
		);

		// It will also call add_rewrite_endpoint().
		\add_filter( 'woocommerce_get_query_vars',
			array( $this, 'filter__woocommerce_get_query_vars' )
		);

		\add_filter( 'woocommerce_account_menu_items',
			array( $this, 'filter__woocommerce_account_menu_items' )
		);
	}

	/**
	 * Hooks for admin area.
	 */
	protected function hooks_admin() {
		// Add endpoint slug edit box to Settings -> Advanced.
		\add_filter( 'woocommerce_settings_pages',
			array( $this, 'filter__woocommerce_settings_pages' )
		);

		\add_filter( 'woocommerce_custom_nav_menu_items', function ( $endpoints ) {
			$endpoints[ $this->get_id() ] = $this->get_title();

			return $endpoints;
		} );
	}

	/**
	 * Add endpoint slug input field to the WooCommerce -> Settings -> Advanced page.
	 *
	 * @param array $settings Existing input fields.
	 *
	 * @return array
	 */
	public function filter__woocommerce_settings_pages( $settings ) {

		$start  = array_slice( $settings, 0, - 1 );
		$end    = array_slice( $settings, - 1 );
		$insert = array(
			$this->get_option_key() => array(
				'title'    => \__( 'Paywall', 'paywall-for-woocommerce' ) . ': ' . $this->get_title(),
				'desc'     => sprintf( // translators: %s is placeholder for the page name.
					\__( 'Endpoint for the "My account &rarr; %s" page.', 'paywall-for-woocommerce' ),
					$this->get_title() ),
				'id'       => $this->get_option_key(),
				'type'     => 'text',
				'default'  => $this->get_id(),
				'desc_tip' => true,
			),
		);

		return array_merge( $start, $insert, $end );
	}

	/**
	 * Display the My Account tab content.
	 *
	 * @see \woocommerce_account_content()
	 * @return void
	 */
	abstract public function action__endpoint_content();

	/**
	 * Add new query var.
	 *
	 * @param string[] $query_vars Query vars.
	 *
	 * @return string[]
	 */
	public function filter__woocommerce_get_query_vars( $query_vars ) {
		$slug = $this->get_slug();

		$query_vars[ $slug ] = $slug;

		return $query_vars;
	}

	/**
	 * Insert the new endpoint into the My Account menu.
	 *
	 * @param array $menu_items
	 *
	 * @return array
	 */
	public function filter__woocommerce_account_menu_items( $menu_items ) {

		$position = array_search( $this->insert_before(), array_keys( $menu_items ), true );

		if ( false !== $position ) {
			// Insert before.
			$menu_items = array_merge(
				array_slice( $menu_items, 0, $position ),
				array( $this->get_slug() => $this->get_title() ),
				array_slice( $menu_items, $position )
			);
		} else {
			// Append to the end.
			$menu_items[ $this->get_slug() ] = $this->get_title();
		}

		return $menu_items;
	}

	/**
	 * Return ID with dashes replaced by underscores.
	 *
	 * @return string
	 */
	public function get_id_underscored() {
		return str_replace( '-', '_', $this->get_id() );
	}

	/**
	 * Option key used in WooCommerce -> Settings -> Advanced to set endpoint slugs.
	 *
	 * @return string
	 */
	public function get_option_key() {

		// WooCommerce's options for dashed slugs are underscored. Let's follow.
		return 'woopaywall_myaccount_' . $this->get_id_underscored() . '_endpoint';
	}

	/**
	 * Returns endpoint slug.
	 *
	 * @return string
	 */
	public function get_slug() {
		static $slug = '';

		/**
		 * WC does not like Elvis.
		 *
		 * @noinspection PhpTernaryExpressionCanBeReducedToShortVersionInspection
		 */
		return $slug ? $slug : \get_option( $this->get_option_key(), $this->get_id() );
	}

	/**
	 * Flush rewrite rules if slug changed.
	 *
	 * @since 3.0.0
	 * @since 3.0.2 Fix: Do nothing is no permalinks or permalinks option is not an array.
	 *
	 * @return void
	 */
	public function maybe_flush_rewrite_rules() {

		if ( ! \get_option( 'permalink_structure' ) ) {
			// No permalinks. Do nothing.
			return;
		}

		$rules = \get_option( 'rewrite_rules', array() );

		/**
		 * Flush rules only if our rule is not there.
		 *
		 * @see \WP_Rewrite::page_rewrite_rules() for the origin of `(.?.+?)`.
		 * @see \WP_Rewrite::generate_rewrite_rules() for the origin of `(/(.*))?/?$`.
		 * @example
		 *      <code>
		 *      (.?.+?)/my-purchased-products(/(.*))?/?$=index.php?pagename=$matches[1]&my-purchased-products=$matches[3]
		 *      </code>
		 *
		 */
		if ( is_array( $rules ) && array_key_exists( '(.?.+?)/' . $this->get_slug() . '(/(.*))?/?$', $rules ) ) {
			// Our rule already exists. Do nothing.
			return;
		}

		// Finally.
		\flush_rewrite_rules( false );
	}

	/**
	 * Get endpoint URL in My account.
	 *
	 * @return string
	 */
	public static function my_account_url() {
		/**
		 * Filter to set page title for the endpoint by ID, regardless of the slug, which is dynamic.
		 *
		 * @since 3.0.0
		 *
		 * @param string $endpoint_slug The slug.
		 */
		$endpoint_slug  = \apply_filters( 'woopaywall_endpoint_id_' . static::id() . '_slug', '' );
		$url_my_account = \wc_get_page_permalink( 'myaccount' );

		return \wc_get_endpoint_url( $endpoint_slug, '', $url_my_account );
	}
}

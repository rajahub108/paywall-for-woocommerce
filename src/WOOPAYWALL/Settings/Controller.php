<?php
/**
 * Settings controller.
 *
 * @since 2.0.0
 */

namespace WOOPAYWALL\Settings;

use WOOPAYWALL\App;
use WOOPAYWALL\Dependencies\TIVWP\WC\WCEnv;
use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\Alter\AddToCartText;
use WOOPAYWALL\Alter\AddToCartURL;
use WOOPAYWALL\Alter\PriceHTML;

/**
 * Class Controller
 */
class Controller extends Hookable {

	/**
	 * Order of the sections on the Settings Tab.
	 *
	 * @since    3.0.0
	 * @since    3.4.1 Added SectionCache. Changed algorithm of getting priority.
	 *
	 * @param string $section_id Section ID.
	 *
	 * @return int
	 */
	public static function get_priority( $section_id ) {
		$priority = array(
			'intro',
			'expiration',
			Sections\SectionProductOptions::ID,
			Sections\SectionCache::ID,
			'add_to_cart',
			AddToCartURL::get_section_id(),
			AddToCartText::get_section_id(),
			PriceHTML::get_section_id(),
			Sections\SectionLogging::ID,
		);

		return (int) array_search( $section_id, $priority, true );
	}

	/**
	 * Options table keys prefix.
	 *
	 * @var string
	 */
	const OPTIONS_PREFIX = 'woopaywall_';

	/**
	 * Make option key by adding prefix.
	 *
	 * @param string $option Name of the option.
	 *
	 * @return string
	 */
	public static function make_option_key( $option ) {
		return self::OPTIONS_PREFIX . $option;
	}

	/**
	 * Get option helper.
	 *
	 * @param string $option        Name of option to retrieve. Expected to not be SQL-escaped.
	 * @param mixed  $default_value Optional. Default value to return if the option does not exist.
	 *
	 * @return bool|mixed
	 */
	public static function get_option( $option, $default_value = false ) {
		return \get_option( self::make_option_key( $option ), $default_value );
	}

	/**
	 * Returns the settings page url.
	 *
	 * @since 3.9.3
	 *
	 * @return string
	 */
	public static function page_url() {
		$query_args = array(
			'page' => 'wc-settings',
			'tab'  => App::TAB_SLUG,
		);
		global $current_section;
		if ( $current_section ) {
			$query_args['section'] = $current_section;
		}

		return \add_query_arg( $query_args, \admin_url( 'admin.php' ) );
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

		\add_filter( 'woocommerce_get_settings_pages', array( $this, 'filter__woocommerce_get_settings_pages' ) );

		\add_filter(
			'plugin_action_links_' . App::instance()->plugin_basename,
			array( $this, 'filter__plugin_action_links' )
		);
	}

	/**
	 * Add settings Tab.
	 *
	 * @since    3.0.0
	 *
	 * @param array $settings The "All Settings" array.
	 *
	 * @return array
	 */
	public function filter__woocommerce_get_settings_pages( $settings ) {

		$settings[] = new Tab();

		$this->add_sections();

		return $settings;
	}

	/**
	 * Add sections to the Tab.
	 *
	 * @since    3.0.0
	 * @since    3.4.1 Added SectionCache.
	 * @return void
	 */
	protected function add_sections() {
		( new Sections\SectionIntro() )->setup_hooks();
		( new Sections\SectionCache() )->setup_hooks();
		( new Sections\SectionProductOptions() )->setup_hooks();
		( new Sections\SectionExpiration() )->setup_hooks();
		( new Sections\SectionAddToCart() )->setup_hooks();
		( new Sections\SectionAlterPriceHTML() )->setup_hooks();
		( new Sections\SectionAlterAddToCartText() )->setup_hooks();
		( new Sections\SectionAlterAddToCartURL() )->setup_hooks();
		( new Sections\SectionLogging() )->setup_hooks();
	}

	/**
	 * Select field type: select, radio.
	 *
	 * @since 3.0.0
	 *
	 * @param string $field_id Field ID.
	 *
	 * @return string
	 */
	public static function get_select_type( $field_id = '' ) {
		/**
		 * Filter to change the select type.
		 *
		 * @since 3.0.0
		 *
		 * @param string $type     Default = 'select'.
		 * @param string $field_id Field ID.
		 *
		 * @return string
		 */
		return \apply_filters( 'woopaywall_select_type', 'select', $field_id );
	}

	/**
	 * Helper make_link.
	 *
	 * @since 3.9.3
	 *
	 * @param string $url    URL.
	 * @param string $text   Text.
	 * @param string $target Target.
	 *
	 * @return string
	 */
	protected static function make_link( $url, $text, $target = '_self' ) {
		return '<a href="' . \esc_url( $url ) . '" target="' . \esc_attr( $target ) . '">' . \esc_html( $text ) . '</a>';
	}

	/**
	 * Adds the settings, docs and support links to the plugin screen.
	 *
	 * @since 3.9.3
	 *
	 * @param string[] $links The plugin's links displayed on the Plugins screen.
	 *
	 * @return string[]
	 */
	public function filter__plugin_action_links( $links ) {
		$plugin_links = array(
			self::make_link( self::page_url(), \__( 'Settings' ) ),
			self::make_link( App::url_docs(), \__( 'Docs', 'woocommerce' ), '_' ),
			self::make_link( App::instance()->getUrlSupport(), \__( 'Support' ), '_' ),
		);

		return array_merge( $plugin_links, $links );
	}


	/**
	 * Options table key: FORCE_SOLD_INDIVIDUALLY.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	const OPTION_FORCE_SOLD_INDIVIDUALLY = 'force_sold_individually';

	/**
	 * Options default: FORCE_SOLD_INDIVIDUALLY.
	 *
	 * @since 3.5.0
	 * @var string
	 */
	const OPTION_FORCE_SOLD_INDIVIDUALLY_DEFAULT = 'yes';

	/**
	 * Method is_sold_individually_forced.
	 *
	 * @since 4.1.1
	 * @return bool
	 */
	public static function is_sold_individually_forced(): bool {
		if ( self::OPTION_FORCE_SOLD_INDIVIDUALLY_DEFAULT === self::get_option( self::OPTION_FORCE_SOLD_INDIVIDUALLY, self::OPTION_FORCE_SOLD_INDIVIDUALLY_DEFAULT ) ) {
			// Forced in settings.
			return true;
		}
		return false;
	}
}

<?php
/**
 * AbstractPaywallProduct
 *
 * @since 4.0.0
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Abstracts;

use WOOPAYWALL\AdminPreview;
use WOOPAYWALL\App;
use WOOPAYWALL\Dependencies\TIVWP\WC\CacheVarProducts;
use WOOPAYWALL\Dependencies\TIVWP\WC\Product\AbstractProductSimple;
use WOOPAYWALL\Expiration;
use WOOPAYWALL\ProductState;
use WOOPAYWALL\Settings\Controller;

/**
 * Class AbstractPaywallProduct
 *
 * @since 4.0.0
 */
abstract class AbstractPaywallProductSimple extends AbstractProductSimple {

	/**
	 * Place our tab after the "General", which is order #10.
	 *
	 * @since 0.0.1
	 * @since 4.0.0 Move below the General tab
	 *
	 * @var int
	 */
	const PRODUCT_DATA_TAB_POSITION = 11;

	/**
	 * Setup actions and filters.
	 *
	 * @inheritDoc
	 */
	public function setup_hooks() {

		parent::setup_hooks();

		\add_action( 'admin_init', array( $this, 'action__admin_init' ) );

		\add_filter(
			'woocommerce_order_item_needs_processing',
			array( $this, 'filter__woocommerce_order_item_needs_processing' ),
			App::HOOK_PRIORITY_LATE,
			2
		);

		\add_filter(
			'woocommerce_sale_flash',
			array( $this, 'action__woocommerce_sale_flash' ),
			App::HOOK_PRIORITY_LATE,
			3
		);

		\add_filter(
			'woocommerce_bulk_edit_save_price_product_types',
			array( $this, 'filter__allow_bulk_edit_prices' )
		);
	}

	/**
	 * This product does not require processing. Orders will be marked as "completed" after payment.
	 *
	 * @param bool        $true_false Normally, this is true only for virtual downloadable items.
	 * @param \WC_Product $product    The product object.
	 *
	 * @return bool
	 */
	public function filter__woocommerce_order_item_needs_processing( $true_false, $product ) {
		return $this->is_my_product_type( $product ) ? false : $true_false;
	}

	/**
	 * Method action__admin_init.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function action__admin_init() {
		parent::action__admin_init();

		\add_action(
			'woocommerce_admin_process_product_object',
			array( $this, 'action__save_data' )
		);
	}

	/**
	 * Method get_meta_keys_to_save.
	 *
	 * @since 4.0.0
	 * @return string[]
	 */
	protected function get_meta_keys_to_save() {
		return array(
			static::make_meta_key( 'custom_' . Expiration\GlobalSettings::OPTION_VALUE ),
			static::make_meta_key( Expiration\GlobalSettings::OPTION_VALUE ),
			static::make_meta_key( Expiration\GlobalSettings::OPTION_UNITS ),
		);
	}

	/**
	 * Save the data.
	 *
	 * @since 4.0.0
	 *
	 * @param AbstractPaywallProductSimple $product Product object.
	 */
	public function action__save_data( $product ) {

		// Hooked to all products. We need only the one being saved.
		if ( ! $product instanceof AbstractPaywallProductSimple || ! $product->is_type( static::PRODUCT_TYPE ) ) {
			return;
		}

		$nonce  = 'woocommerce_meta_nonce';
		$action = 'woocommerce_save_data';
		if ( ! isset( $_POST[ $nonce ] ) ) {
			return;
		}
		$post_nonce = \sanitize_text_field( \wp_unslash( $_POST[ $nonce ] ) );
		if ( ! \wp_verify_nonce( $post_nonce, $action ) ) {
			return;
		}

		$meta_keys = $this->get_meta_keys_to_save();

		foreach ( $meta_keys as $meta_key ) {
			$value = isset( $_POST[ $meta_key ] ) ? \sanitize_text_field( \wp_unslash( $_POST[ $meta_key ] ) ) : null;
			if ( null === $value ) {
				$product->delete_meta_data( $meta_key );
			} else {
				$product->update_meta_data( $meta_key, $value );
			}
		}
	}

	/**
	 * We want to sell one at a time.
	 *
	 * @since 0.0.1
	 * @since 3.5.0 Allow disabling this restriction in Settings.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 */
	public function get_sold_individually( $context = 'view' ) {

		if ( Controller::is_sold_individually_forced() ) {
			// Forced in settings.
			return true;
		}

		return parent::get_sold_individually( $context );
	}

	/**
	 * Override get_virtual().
	 *
	 * @since        4.0.0
	 * @inheritDoc
	 * @noinspection PhpMissingParentCallCommonInspection
	 */
	public function get_virtual( $context = 'view' ) {
		return true;
	}

	/**
	 * Make meta key by adding prefix.
	 *
	 * @since 4.0.0
	 *
	 * @param string $meta_name Name of the meta.
	 *
	 * @return string
	 */
	public static function make_meta_key( $meta_name ) {
		return '_woo' . static::PRODUCT_TYPE . '_' . $meta_name;
	}

	/**
	 * Returns true if product has custom expiration settings.
	 *
	 * @since 4.0.0
	 *
	 * @return bool
	 */
	public function has_custom_expiration() {
		return ( 'yes' === $this->get_meta( self::make_meta_key( 'custom_' . Expiration\GlobalSettings::OPTION_VALUE ) ) );
	}

	/**
	 * Return custom or global expiration settings.
	 *
	 * @since 4.0.0
	 *
	 * @return Expiration\ExpireAfter
	 */
	public function get_expire_after() {

		if ( $this->has_custom_expiration() ) {
			$expire_after = new Expiration\ExpireAfter(
				$this->get_meta( self::make_meta_key(
					Expiration\GlobalSettings::OPTION_VALUE ) ),
				$this->get_meta( self::make_meta_key(
					Expiration\GlobalSettings::OPTION_UNITS ) )
			);
		} else {
			/**
			 * To please IDE.
			 *
			 * @var App $app_instance
			 */
			$app_instance        = App::instance();
			$expiration_settings = $app_instance->getExpirationGlobalsettings();
			$expire_after        = $expiration_settings->getExpireAfter();
		}

		return $expire_after;
	}

	/**
	 * Returns true if the product is purchased and not expired.
	 *
	 * @since 4.0.0
	 *
	 * @param array $args Product ID in args['product_id'].
	 *
	 * @return bool
	 */
	public function get_is_active( $args = array() ) {

		$is_admin_preview = AdminPreview::is_set();
		if ( null !== $is_admin_preview ) {
			return $is_admin_preview;
		}

		$product_id = $args['product_id'] ?? $this->get_id();

		return App::instance()->get_current_user()->can_view_product( $product_id );
	}

	/**
	 * Cached {@see get_is_active()}.
	 *
	 * @return bool
	 */
	public function is_active() {

		$product_id = $this->get_id();
		$property   = __FUNCTION__;
		$method     = array( $this, 'get_is_active' );
		$args       = array( 'product_id' => $product_id );

		return CacheVarProducts::get( $product_id, $property, $method, $args );
	}

	/**
	 * Returns true is the product is purchasable.
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function get_is_purchasable() {
		return parent::is_purchasable();
	}

	/**
	 * Override is_purchasable(). Cached {@see get_is_purchasable()}
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	public function is_purchasable() {

		$product_id = $this->get_id();
		$property   = __FUNCTION__;
		$method     = array( $this, 'get_is_purchasable' );
		$args       = null;

		return CacheVarProducts::get( $product_id, $property, $method, $args );
	}

	/**
	 * No shipping class.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int
	 */
	public function get_shipping_class_id( $context = 'view' ) {
		return 0;
	}

	/**
	 * Hide "Sale!" label.
	 *
	 * @param string                       $html    Original Sale badge HTML.
	 * @param \WP_Post                     $post    Unused.
	 * @param AbstractPaywallProductSimple $product The product object.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function action__woocommerce_sale_flash( $html, $post, $product ) {
		return $product->is_type( static::PRODUCT_TYPE ) && ! $product->is_purchasable() ? '' : $html;
	}

	/**
	 * Add our product type to the list of those allowed for Bulk Actions -> Edit.
	 *
	 * @since 1.0.0
	 * @since 4.0.0 Moved to Abstract
	 *
	 * @param string[] $types List of product types.
	 *
	 * @return string[]
	 */
	public function filter__allow_bulk_edit_prices( $types ) {

		$types[] = static::PRODUCT_TYPE;

		return array_unique( $types );
	}

	/**
	 * Print expiration metaboxes.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	protected function fieldset_expiration() {

		$id = static::make_meta_key( 'custom_' . Expiration\GlobalSettings::OPTION_VALUE );
		\woocommerce_wp_checkbox(
			array(
				'id'            => $id,
				'wrapper_class' => 'show_if_' . static::PRODUCT_TYPE,
				'label'         => __( 'Custom expiration?', 'paywall-for-woocommerce' ),
				'description'   => __( 'Check to override the default expiration settings for this product', 'paywall-for-woocommerce' ),
			)
		);

		$id = static::make_meta_key( Expiration\GlobalSettings::OPTION_VALUE );
		\woocommerce_wp_text_input(
			array(
				'id'                => $id,
				'wrapper_class'     => 'show_if_' . static::PRODUCT_TYPE,
				'label'             => __( 'Purchases expire after', 'paywall-for-woocommerce' ),
				'description'       => __( '1 hour to 365 days; 0 - never expire', 'paywall-for-woocommerce' ),
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => 0,
					'max'  => Expiration\GlobalSettings::MAX_VALUE,
					'step' => 1,
				),
				'style'             => 'width: 5em; text-align: right',
			)
		);

		$id = static::make_meta_key( Expiration\GlobalSettings::OPTION_UNITS );
		\woocommerce_wp_select(
			array(
				'id'            => $id,
				'wrapper_class' => 'show_if_' . static::PRODUCT_TYPE,
				'label'         => '',
				'options'       => Expiration\Units::get_units( Expiration\Units::FORCE_PLURAL ),
			)
		);
	}

	/**
	 * Override supports().
	 *
	 */
	public function supports( $feature ) {
		if ( 'ajax_add_to_cart' === $feature ) {
			return false;
		}

		return parent::supports( $feature );
	}

	/**
	 * Override add_to_cart_text().
	 *
	 * @since        0.0.1
	 * @since        3.0.0 Show standard text if enabled in settings.
	 * @since        4.0.0 Moved to Abstract.
	 *
	 * @inheritDoc
	 */
	public function add_to_cart_text() {

		$altered = false;
		$product =& $this;

		/**
		 * Alter Add To Cart text.
		 *
		 * @since        3.0.0
		 *
		 * @param false                        $altered False = do not alter.
		 * @param AbstractPaywallProductSimple $product Product instance.
		 *
		 * @return false|string If a string returned, it will be used instead of the price HTML.
		 * @noinspection PhpConditionAlreadyCheckedInspection
		 */
		$altered = \apply_filters( 'woopaywall_alter_add_to_cart_text', $altered, $product );
		if ( false !== $altered ) {
			return $altered;
		}

		return parent::add_to_cart_text();
	}

	/**
	 * Get product state ID.
	 *
	 * @since 4.0.0 Moved to Abstract.
	 *
	 * @return string
	 */
	public function get_state() {

		if ( $this->is_active() ) {
			$state = ProductState::PAID;
		} elseif ( $this->is_in_cart() ) {
			$state = ProductState::IN_CART;
		} elseif ( ! $this->is_in_stock() ) {
			$state = ProductState::OUT_OF_STOCK;
		} elseif ( ! $this->is_purchasable() ) {
			$state = ProductState::NOT_PURCHASABLE;
		} else {
			$state = ProductState::AVAILABLE;
		}

		return $state;
	}

	/**
	 * Get the add to url used mainly in loops.
	 * If already purchased, go to the product page.
	 * On archive pages, if not in the cart, to the product page.
	 * On single page, go to Checkout with adding to cart. (irrelevant?)
	 * If in the cart, just go to Checkout.
	 *
	 * @since        3.0.0 Show standard button if enabled in settings.
	 * @since        4.0.0 Moved to Abstract.
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public function add_to_cart_url() {

		$altered = false;
		$product =& $this;

		/**
		 * Alter Add To Cart URL.
		 *
		 * @since        3.0.0
		 *
		 * @param false                        $altered False = do not alter.
		 * @param AbstractPaywallProductSimple $product Product instance.         *
		 *
		 * @return false|string If a string returned, it will be used instead of the URL.
		 * @noinspection PhpConditionAlreadyCheckedInspection
		 */
		$altered = \apply_filters( 'woopaywall_alter_add_to_cart_url', $altered, $product );
		if ( false !== $altered ) {
			return $altered;
		}

		return parent::add_to_cart_url();
	}

	/**
	 * Product permalink.
	 *
	 * @since        3.4.0
	 * @since        4.0.0 Moved to Abstract.
	 * @return string
	 */
	public function get_permalink() {

		/**
		 * Filter to modify Paywall product permalink.
		 *
		 * @since 3.4.0
		 *
		 * @param string $permalink The permalink URL.
		 */
		return \apply_filters( 'woopaywall_product_permalink', parent::get_permalink() );
	}
}

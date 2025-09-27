<?php
/**
 * AbstractProductSimple.
 * Adds several methods to the standard WC Product.
 *
 * @noinspection PhpUnused
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Product;

use WOOPAYWALL\App;
use WOOPAYWALL\Dependencies\TIVWP\InterfaceHookable;
use WOOPAYWALL\Dependencies\TIVWP\WC\CacheVarProducts;

/**
 * Class AbstractProductSimple
 */
abstract class AbstractProductSimple extends \WC_Product_Simple implements InterfaceHookable {

	/**
	 * Product type. To be defined in the child classes.
	 *
	 * @var string
	 */
	const PRODUCT_TYPE = '';

	/**
	 * Override get_type().
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	public function get_type() {
		return static::PRODUCT_TYPE;
	}

	/**
	 * Product tab icon. Override in child classes.
	 *
	 * @since 4.0.0
	 * @var string
	 * @link  https://developer.wordpress.org/resource/dashicons/#admin-generic
	 */
	const PRODUCT_ICON = 'f111';

	/**
	 * Method is_my_product_type.
	 *
	 * @since 4.0.0
	 *
	 * @param \WC_Product $product The product object.
	 *
	 * @return bool
	 */
	protected function is_my_product_type( $product ) {
		return $product->get_type() === static::PRODUCT_TYPE;
	}

	/**
	 * Method get_type_label.
	 *
	 * @since 4.0.0
	 * @return string
	 */
	abstract protected function get_type_label();

	/**
	 * Setup actions and filters.
	 *
	 * @inheritDoc
	 */
	public function setup_hooks() {
		\add_action( 'init', array( $this, 'action__setup_product_type_taxonomy' ) );
		\add_action( 'admin_init', array( $this, 'action__admin_init' ) );
		\add_filter(
			'wc_stripe_payment_request_supported_types',
			array( $this, 'filter__wc_stripe_payment_request_supported_types' )
		);
		\add_action(
			'woocommerce_' . static::PRODUCT_TYPE . '_add_to_cart',
			array( $this, 'action__add_to_cart' )
		);
	}

	/**
	 * Conditionally show add-to-cart/checkout button.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	abstract public function action__add_to_cart();

	/**
	 * Setup product_type taxonomy.
	 *
	 * @return void
	 */
	public function action__setup_product_type_taxonomy() {

		// If there is no product type taxonomy, add it.
		if ( ! \get_term_by( 'slug', static::PRODUCT_TYPE, 'product_type' ) ) {
			\wp_insert_term( static::PRODUCT_TYPE, 'product_type' );
		}
	}

	/**
	 * Method action__admin_init.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function action__admin_init() {
		\add_filter( 'product_type_selector', array( $this, 'filter__product_type_selector' ) );
		\add_filter( 'woocommerce_product_data_tabs', function ( $tabs ) {
			$tabs[ static::PRODUCT_TYPE ] = array(
				'label'    => $this->get_type_label(),
				'target'   => static::PRODUCT_TYPE . '_product_options',
				'class'    => 'show_if_' . static::PRODUCT_TYPE,
				'priority' => static::PRODUCT_DATA_TAB_POSITION,
			);

			/**
			 * // $tabs['variations']['class'][] = 'show_if_' . static::PRODUCT_TYPE;
			 * // $tabs['attribute']['class'][] = 'hide_if_' . $this->get_type();
			 * // $tabs['linked_product']['class'][] = 'hide_if_' . static::PRODUCT_TYPE;
			 */

			if ( $this->is_virtual() ) {
				$tabs['shipping']['class'][] = 'hide_if_' . static::PRODUCT_TYPE;
			}

			return $tabs;
		} );
		\add_action( 'woocommerce_product_data_panels', array( $this, 'action__woocommerce_product_data_panels' ) );

		foreach ( array( 'post-new.php', 'post.php' ) as $hook_suffix ) {
			\add_action( "admin_print_scripts-$hook_suffix", function () {
				/**
				 * Post type.
				 *
				 * @global string $post_type
				 */
				global $post_type;
				if ( 'product' !== $post_type ) {
					return;
				}

				\wp_enqueue_script(
					'woopaywall-show-general',
					App::instance()->plugin_dir_url() . 'assets/js/show-general-tab.min.js',
					array(),
					App::$VER,
					false
				);

				// @formatter:off
				?>
				<style id="<?php echo \esc_attr( static::PRODUCT_TYPE ); ?>-icon">
					#woocommerce-product-data ul.wc-tabs li.<?php echo \esc_attr( static::PRODUCT_TYPE ); ?>_options a::before {
						font-family: Dashicons, fantasy;
						content: "\<?php echo \esc_attr( static::PRODUCT_ICON ); ?>";
					}
				</style>
				<script id="<?php echo \esc_attr( static::PRODUCT_TYPE ); ?>-product-data">
					document.addEventListener("DOMContentLoaded", () => {
						document.querySelectorAll(".show_if_simple")?.forEach(element =>
							element.classList.add("show_if_<?php echo \esc_attr( static::PRODUCT_TYPE ); ?>")
						);
					});
				</script>
				<?php
				// @formatter:on
			} );
		}
	}

	/**
	 * Method filter__product_type_selector.
	 *
	 * @since 4.0.0
	 *
	 * @param array $product_types Array of Product types and their labels.
	 *
	 * @return array
	 */
	public function filter__product_type_selector( $product_types ) {
		$product_types[ static::PRODUCT_TYPE ] = $this->get_type_label();

		return $product_types;
	}

	/**
	 * Add Content to Product Tab
	 *
	 * @since 4.0.0
	 * @return void
	 */
	abstract public function action__woocommerce_product_data_panels();

	/**
	 * True if the product has been purchased by the current user.
	 *
	 * @return bool
	 */
	public function is_purchased_by_me() {

		$user = \wp_get_current_user();

		return ( $user->exists() && \wc_customer_bought_product( $user->user_email, $user->ID, $this->get_id() ) );
	}

	/**
	 * True if the product is in the cart. Cached {@see get_is_in_cart()}.
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	public function is_in_cart() {
		$product_id = $this->get_id();
		$property   = __FUNCTION__;
		$method     = array( $this, 'get_is_in_cart' );
		$args       = array( 'product_id' => $product_id );

		return CacheVarProducts::get( $product_id, $property, $method, $args );
	}

	/**
	 * True if the product is in the cart.
	 *
	 * @since 1.0.0
	 * @since 1.1.0 Check cart existence to avoid fatal errors.
	 * @since 1.1.0 Do not rely on product_cart_id because it depends on
	 *        "other cart item data passed which affects this items uniqueness in the cart".
	 *        Loop through the cart to find the product_id.
	 * @since 4.0.0 Use {@see CacheVarProducts}.
	 * @return bool
	 */
	public function get_is_in_cart( $args = array() ) {
		$product_id = $args['product_id'] ?? $this->get_id();

		$cart = \WC()->cart;
		if ( ! $cart instanceof \WC_Cart ) {
			return false;
		}

		$cart_contents = $cart->get_cart_contents();

		return in_array( $product_id, array_column( $cart_contents, 'product_id' ), true );
	}

	/**
	 * Returns true if there is another product of my type already in the Cart.
	 *
	 * @since 4.0.0
	 * @return bool
	 */
	protected function is_my_type_in_cart() {
		$cart = \WC()->cart;
		if ( ! $cart instanceof \WC_Cart ) {
			return false;
		}

		$cart_contents = $cart->get_cart_contents();

		if ( ! count( $cart_contents ) ) {
			return false;
		}

		$my_id    = $this->get_id();
		$my_class = get_class( $this );

		foreach ( $cart_contents as $product_in_cart ) {
			if ( $product_in_cart['data'] instanceof $my_class && $product_in_cart['product_id'] !== $my_id ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Get the checkout URL.
	 *
	 * @param bool $add_to_cart Set to true if the button should add the current product to the Cart.
	 *
	 * @return string
	 */
	public function get_checkout_url( $add_to_cart = false ) {

		return \wc_get_checkout_url() . ( $add_to_cart ? '?add-to-cart=' . $this->get_id() : '' );
	}

	/**
	 * Add our type to the Stripe supported types.
	 * Required for Apple Pay button, for example.
	 *
	 * @since 3.3.2
	 *
	 * @param array $types Product types.
	 *
	 * @return array
	 */
	public function filter__wc_stripe_payment_request_supported_types( $types ) {
		$types[] = static::PRODUCT_TYPE;

		return $types;
	}
}

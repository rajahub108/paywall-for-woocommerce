<?php
/**
 * Product type "Paywall"
 *
 * @since 0.0.1
 */


use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\Alter\AddToCartURL;
use WOOPAYWALL\App;
use WOOPAYWALL\Dependencies\TIVWP\Env;
use WOOPAYWALL\Frontend\Alert;
use WOOPAYWALL\Frontend\Product\Downloads;
use WOOPAYWALL\Settings\Controller as SettingsController;

/**
 * Class WC_Product_Paywall
 */
class WC_Product_Paywall extends AbstractPaywallProductSimple {

	/**
	 * Product type.
	 *
	 * @var string
	 */
	const PRODUCT_TYPE = 'paywall';

	/**
	 * Product tab icon.
	 *
	 * @since 4.0.0
	 * @var string
	 * @link  https://developer.wordpress.org/resource/dashicons/#hidden
	 */
	const PRODUCT_ICON = 'f530';

	/**
	 * Wrapper to return our product type.
	 *
	 * @since        0.0.1
	 *
	 * @param WC_Product|int|false $the_product Post object or post ID of the product.
	 *
	 * @return false|WC_Product_Paywall|null
	 * @noinspection PhpReturnDocTypeMismatchInspection
	 */
	public static function wc_get_product( $the_product = false ) {
		return wc_get_product( $the_product );
	}

	/**
	 * Implement get_type_label().
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	protected function get_type_label() {
		return __( 'Paywall', 'paywall-for-woocommerce' );
	}

	/**
	 * Setup actions and filters.
	 *
	 * @since 0.0.1
	 *
	 * @inheritDoc
	 */
	public function setup_hooks() {

		parent::setup_hooks();

		add_filter(
			'wc_add_to_cart_message_html',
			array( __CLASS__, 'tweak_added_to_cart_message' ),
			App::HOOK_PRIORITY_LATE,
			2
		);

		add_filter(
			'woocommerce_pre_remove_cart_item_from_session',
			array( __CLASS__, 'filter__woocommerce_pre_remove_cart_item_from_session' ), App::HOOK_PRIORITY_LATE,
			3
		);
	}

	/**
	 * Implement action__add_to_cart().
	 *
	 * @since 2.0.0
	 * @inheritDoc
	 */
	public function action__add_to_cart() {

		$product = static::wc_get_product();

		if ( ! $product ) {
			// Impossible?
			return;
		}

		if ( App::instance()->get_current_user()->is_any_pwpass_active() ) {
			Alert::pwpass_active();
		}

		if ( $product->is_active() ) {
			Alert::thanks( $product );
			/**
			 * Show download buttons.
			 *
			 * @since 2.1.0
			 */
			Downloads::show_buttons( $product );
		} elseif ( $product->is_in_cart() ) {
			Alert::already_in_cart();
		} else {
			/**
			 * Trigger the single product add to cart action.
			 * Our product type do not have its own template, so we use the Simple Product's add_to_cart action.
			 *
			 * @since 2.0.0
			 */
			do_action( 'woocommerce_simple_add_to_cart' );
			$expiration_global_settings = App::instance()->getExpirationGlobalsettings();
			$expiration_global_settings->show_on_single_product_page( $product );
		}

		if ( ! is_user_logged_in() ) {
			/**
			 * For 3rd parties to add any content here.
			 *
			 * @since 4.0.0
			 *
			 * @param AbstractPaywallProductSimple $product
			 */
			do_action( 'woopaywall_single_product_not_logged', $product );
		}
	}

	/**
	 * Implement action__woocommerce_product_data_panels().
	 *
	 * @since 0.0.1
	 *
	 * @inheritDoc
	 */
	public function action__woocommerce_product_data_panels() {
		/**
		 * Globals.
		 *
		 * @global WC_Product $product_object
		 */
		global $product_object;

		if ( ! $product_object ) {
			return;
		}

		?>
		<div id="<?php echo esc_attr( static::PRODUCT_TYPE ); ?>_product_options"
				class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<?php

				$id = static::META_HIDE_DESCRIPTION;
				woocommerce_wp_checkbox(
					array(
						'id'            => $id,
						'wrapper_class' => 'show_if_' . static::PRODUCT_TYPE,
						'label'         => __( 'Hide description?', 'paywall-for-woocommerce' ),
						'description'   => __( 'Do not show the description until paid', 'paywall-for-woocommerce' ),
					)
				);

				$id = static::META_HIDDEN_MEDIA_URL;
				woocommerce_wp_text_input(
					array(
						'id'            => $id,
						'wrapper_class' => 'show_if_' . static::PRODUCT_TYPE,
						'label'         => __( 'Hidden media URL', 'paywall-for-woocommerce' ),
						'description'   => __( 'When paid, this media will be shown instead of the product image.', 'paywall-for-woocommerce' ),
						'type'          => 'url',
						'style'         => 'max-width:300px;',
						'placeholder'   => __( 'Enter URL', 'paywall-for-woocommerce' ),
					)
				);

				$id = static::META_VISIBLE_MEDIA_URL;
				woocommerce_wp_text_input(
					array(
						'id'            => $id,
						'wrapper_class' => 'show_if_' . static::PRODUCT_TYPE,
						'label'         => __( 'Visible media URL', 'paywall-for-woocommerce' ),
						'description'   => __( 'This will be shown instead of the product image until paid (a video trailer, for example)', 'paywall-for-woocommerce' ),
						'type'          => 'url',
						'style'         => 'max-width:300px;',
						'placeholder'   => __( 'Enter URL', 'paywall-for-woocommerce' ),
					)
				);

				$this->fieldset_expiration();
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Override get_price_html().
	 *
	 * @since 0.0.1
	 * @inheritDoc
	 */
	public function get_price_html( $deprecated = '' ) {

		if ( ! Env::in_wp_admin() ) {

			/**
			 * Alter price HTML.
			 *
			 * @since 3.0.0
			 *
			 * @param false              $price_html False = do not alter.
			 * @param WC_Product_Paywall $product    Product instance.
			 *
			 * @return false|string If a string returned, it will be used instead of the price HTML.
			 */
			$price_html = apply_filters( 'woopaywall_alter_price_html', false, $this );
			if ( false !== $price_html ) {
				return $price_html;
			}
		}

		return parent::get_price_html();
	}

	/**
	 * Implement get_is_purchasable().
	 * Returns false if the product cannot be bought:
	 * 1. OK to view means "not purchasable" because already purchased.
	 * 2. Check parent.
	 *
	 * @since 0.0.1
	 * @inerhitDoc
	 */
	public function get_is_purchasable() {
		return ! $this->is_ok_to_view() && parent::get_is_purchasable();
	}

	/**
	 * URL meta key.
	 *
	 * @var string
	 */
	const META_HIDE_DESCRIPTION = '_woopaywall_hide_description';

	/**
	 * Hidden media URL meta key.
	 *
	 * @var string
	 */
	const META_HIDDEN_MEDIA_URL = '_woopaywall_url';

	/**
	 * Visible media URL meta key.
	 *
	 * @var string
	 */
	const META_VISIBLE_MEDIA_URL = '_woopaywall_url_trailer';

	/**
	 * Options table key: TWEAK_CART_CHANGE_MESSAGES.
	 *
	 * @since 3.6.0
	 * @var string
	 */
	const OPTION_TWEAK_CART_CHANGE_MESSAGES = 'tweak_cart_change_messages';

	/**
	 * Options default: TWEAK_CART_CHANGE_MESSAGES.
	 *
	 * @since 3.6.0
	 * @var string
	 */
	const OPTION_TWEAK_CART_CHANGE_MESSAGES_DEFAULT = 'yes';

	/**
	 * Check if a product supports a given feature.
	 *
	 * Product classes should override this to declare support (or lack of support) for a feature.
	 *
	 * @since        3.0.0
	 *
	 * @param string $feature string The name of a feature to test support for.
	 *
	 * @return bool True if the product supports the feature, false otherwise.
	 * @noinspection PhpUnused
	 */
	public function supports( $feature ) {

		if ( 'ajax_add_to_cart' === $feature && AddToCartURL::need_to_disable_ajax_add_to_cart() ) {
			return false;
		}

		return parent::supports( $feature );
	}

	/**
	 * Returns the gallery attachment ids.
	 * If purchased, show only the main image. Otherwise, each gallery image will be replaced with the premium content.
	 * Alternative: disable `do_action( 'woocommerce_product_thumbnails' );`
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return array
	 * @noinspection PhpUnused
	 */
	public function get_gallery_image_ids( $context = 'view' ) {
		return $this->is_ok_to_view() ? array() : parent::get_gallery_image_ids( $context );
	}

	/**
	 * Custom methods.
	 */

	/**
	 * Returns true if OK to view the product's hidden media.
	 *
	 * @since 0.0.1
	 * @since 4.0.0 Alias to {@see is_active()}.
	 * @return bool
	 */
	public function is_ok_to_view() {
		return $this->is_active();
	}

	/**
	 * Returns true if we need to hide the description until paid.
	 *
	 * @return bool
	 */
	public function is_need_to_hide_description() {
		return ( 'yes' === $this->get_meta( self::META_HIDE_DESCRIPTION ) );
	}

	/**
	 * Allow 3rd parties to validate this item before it's added to cart and add their own notices.
	 *
	 * @since        3.0.0
	 *
	 * @param bool   $true_false If true, the item will not be added to the cart. Default: false.
	 * @param string $key        Cart item key.
	 * @param array  $values     Cart item values e.g. quantity and product_id.
	 *
	 * @return bool
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function filter__woocommerce_pre_remove_cart_item_from_session( $true_false, $key, $values ) {

		// Hide message '%s has been removed from your cart because it can no longer be purchased. Please contact us if you need assistance.'
		if ( ! empty( $values['product_id'] ) ) {
			$product = self::wc_get_product( $values['product_id'] );
			if ( ! $product->is_purchasable() ) {
				$true_false = true;
			}
		}

		return $true_false;
	}

	/**
	 * Do not show "... added to cart" message when we add and go to Checkout in one step.
	 *
	 * @since 0.0.1
	 * @since 3.6.0 Option to disable this in Settings.
	 *
	 * @param WC_Product[] $products List of products being added.
	 * @param string       $message  The message text.
	 *
	 * @return string
	 */
	public static function tweak_added_to_cart_message( $message, $products ) {

		if ( self::OPTION_TWEAK_CART_CHANGE_MESSAGES_DEFAULT !== SettingsController::get_option( self::OPTION_TWEAK_CART_CHANGE_MESSAGES, self::OPTION_TWEAK_CART_CHANGE_MESSAGES_DEFAULT ) ) {
			// Disabled in settings.
			return $message;
		}

		// Only act if one product added, and it's our product type.
		if ( 1 === count( $products ) ) {
			$product_ids = array_keys( $products );
			$product     = self::wc_get_product( $product_ids[0] );
			if ( $product->is_type( self::PRODUCT_TYPE ) ) {
				$message = __( 'Please proceed with the payment.', 'paywall-for-woocommerce' );
			}
		}

		return $message;
	}

	/**
	 * Override get_meta_keys_to_save().
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	protected function get_meta_keys_to_save() {
		return array_merge(
			parent::get_meta_keys_to_save(),
			array(
				static::META_HIDE_DESCRIPTION,
				static::META_HIDDEN_MEDIA_URL,
				static::META_VISIBLE_MEDIA_URL,
			)
		);
	}

	/**
	 * NO override get_manage_stock.
	 *
	 * @since 2.0.4 Disabled.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return boolean
	 * public function get_manage_stock( $context = 'view' ) {
	 * return false;
	 * }
	 */

	/**
	 * NO override get_stock_quantity.
	 *
	 * @since 2.0.4 Disabled.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return int|null
	 * public function get_stock_quantity( $context = 'view' ) {
	 * return 0;
	 * }
	 */

	/**
	 * NO override get_stock_status.
	 *
	 * @since 2.0.4 Disabled.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return string
	 * public function get_stock_status( $context = 'view' ) {
	 * return 'in-stock';
	 * }
	 */

	/**
	 * NO override get_downloadable.
	 *
	 * @since 2.1.0 Disabled.
	 *
	 * @param string $context What the value is for. Valid values are view and edit.
	 *
	 * @return bool
	 * <code>
	 * public function get_downloadable( $context = 'view' ) {
	 * return false;
	 * }
	 * <code>
	 */
}

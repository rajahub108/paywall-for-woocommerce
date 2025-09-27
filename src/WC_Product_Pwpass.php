<?php
/**
 * Product WC_Product_Pwpass.
 *
 * @since 4.0.0
 */

use WOOPAYWALL\Abstracts\AbstractPaywallProductSimple;
use WOOPAYWALL\App;
use WOOPAYWALL\Dependencies\TIVWP\Env;
use WOOPAYWALL\Frontend\Alert;

/**
 * Class WC_Product_Pwpass.
 *
 * @since 4.0.0
 */
class WC_Product_Pwpass extends AbstractPaywallProductSimple {

	/**
	 * Product type.
	 *
	 * @var string
	 */
	const PRODUCT_TYPE = 'pwpass';

	/**
	 * Product tab icon.
	 *
	 * @since 4.0.0
	 * @var string
	 * @link  https://developer.wordpress.org/resource/dashicons/#visibility
	 */
	const PRODUCT_ICON = 'f177';

	/**
	 * Wrapper to return our product type.
	 *
	 * @since        4.0.0
	 *
	 * @param WC_Product|int|false $the_product Post object or post ID of the product.
	 *
	 * @return false|WC_Product_Pwpass|null
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
		return __( 'Paywall Pass', 'paywall-for-woocommerce' );
	}

	/**
	 * Implement action__add_to_cart().
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	public function action__add_to_cart() {

		$product = static::wc_get_product();

		if ( ! $product ) {
			// Impossible?
			return;
		}

		// This product can only be purchased by logged-in users.
		if ( ! is_user_logged_in() ) {
			Alert::must_login_to_purchase();
			Alert::prompt_to_register_on_my_account_page();

			/**
			 * For 3rd parties to add any content here.
			 *
			 * @since 4.0.0
			 *
			 * @param AbstractPaywallProductSimple $product
			 */
			do_action( 'woopaywall_single_product_not_logged', $product );

			return;
		}

		if ( App::instance()->get_current_user()->is_any_pwpass_active() ) {
			Alert::pwpass_active();
		}

		if ( $product->is_active() ) {
			Alert::thanks( $product );
		} elseif ( $product->is_in_cart() ) {
			Alert::already_in_cart();
		} elseif ( $product->is_my_type_in_cart() ) {
			// Pwpass only!
			Alert::pwpass_in_cart();
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
	}

	/**
	 * Implement action__woocommerce_product_data_panels().
	 *
	 * @since 4.0.0
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
				<?php $this->fieldset_expiration(); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Override add_to_cart_text().
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	public function add_to_cart_text() {
		if ( $this->is_active() ) {
			return __( 'Active', 'paywall-for-woocommerce' );
		}

		return parent::add_to_cart_text();
	}

	/**
	 * Override get_price_html().
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	public function get_price_html( $deprecated = '' ) {
		if ( ! Env::in_wp_admin() ) {

			// Do not show price if purchased and not expired.
			// OK to show if not purchasable.
			if ( $this->is_active() ) {
				return '&nbsp;';
			}
		}

		return parent::get_price_html();
	}

	/**
	 * Implement get_is_purchasable().
	 *
	 * @since 4.0.0
	 * @inerhitDoc
	 */
	public function get_is_purchasable() {
		return is_user_logged_in() && ! $this->is_active() && parent::get_is_purchasable();
	}

	/**
	 * Override get_downloadable().
	 *
	 * @since 4.0.0
	 * @inheritDoc
	 */
	public function get_downloadable( $context = 'view' ) {
		return false;
	}

	/**
	 * NO Override add_to_cart_url().
	 *
	 * DO NOT NEED - @see is_purchasable()
	 * public function add_to_cart_url() {
	 * if ( $this->is_active() ) {
	 * return $this->get_permalink();
	 * }
	 *
	 * return parent::add_to_cart_url();
	 * }
	 */
}

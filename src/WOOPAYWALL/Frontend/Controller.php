<?php
/**
 * Frontend controller.
 *
 * @since 1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Frontend;

use WOOPAYWALL\Dependencies\TIVWP\Media;
use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;
use WOOPAYWALL\Frontend\Product\AddToCart;
use WOOPAYWALL\Frontend\Product\Description;
use WOOPAYWALL\Order\Info;
use WOOPAYWALL\Settings\Controller as SettingsController;


/**
 * Class Controller
 *
 * @package WOOPAYWALL\Frontend
 */
class Controller extends Hookable {

	const OPTION_ORDER_PAGE_BUTTONS = 'order_page_buttons';

	const OPTION_ORDER_PAGE_BUTTONS_BEFORE = 'before';

	const OPTION_ORDER_PAGE_BUTTONS_AFTER = 'after';

	const OPTION_ORDER_PAGE_BUTTONS_NONE = 'none';

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		$this->prevent_caching();

		$product_description = new Description();
		$product_description->setup_hooks();

		$add_to_cart = new AddToCart();
		$add_to_cart->setup_hooks();

		( new ProductCSS() )->setup_hooks();

		$shortcode = new Shortcode();
		$shortcode->setup_hooks();

		\add_filter(
			'woocommerce_single_product_image_thumbnail_html',
			array( $this, 'show_media_if_paid' ),
			App::HOOK_PRIORITY_LATE
		);

		/**
		 * Replaced by the `woocommerce_before_template_part` action.
		 *
		 * @since 3.0.6
		 */
		0 && \add_action(
			'woocommerce_before_thankyou',
			array( $this, 'show_buttons_on_order_received_page' )
		);

		$order_page_buttons_location = SettingsController::get_option( self::OPTION_ORDER_PAGE_BUTTONS, self::OPTION_ORDER_PAGE_BUTTONS_BEFORE );

		/**
		 * To support themes that do not have the `woocommerce_before_thankyou` action.
		 *
		 * @since 3.0.6
		 * @since 3.8.0-rc.1 Can choose, display at the top or at the bottom.
		 */
		if ( self::OPTION_ORDER_PAGE_BUTTONS_BEFORE === $order_page_buttons_location ) {
			\add_action( 'woocommerce_before_template_part',
				array( $this, 'action__on_checkout_thankyou' ),
				App::HOOK_PRIORITY_EARLY,
				4
			);
		} elseif ( self::OPTION_ORDER_PAGE_BUTTONS_AFTER === $order_page_buttons_location ) {
			\add_action( 'woocommerce_after_template_part',
				array( $this, 'action__on_checkout_thankyou' ),
				App::HOOK_PRIORITY_EARLY,
				4
			);
		}

		/**
		 * Recommend creating account.
		 *
		 * @see Alert::checkout().
		 */
		\add_action(
			'woocommerce_before_checkout_form',
			array( Alert::get_class(), 'checkout' )
		);
	}

	/**
	 * Replace image with the media.
	 *
	 * @param string $html The image HTML.
	 *
	 * @return string
	 */
	public function show_media_if_paid( $html ) {

		$product = \WC_Product_Paywall::wc_get_product();

		if ( $product && $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) ) {
			if ( $product->is_ok_to_view() ) {
				$meta_key = \WC_Product_Paywall::META_HIDDEN_MEDIA_URL;
			} else {
				$meta_key = \WC_Product_Paywall::META_VISIBLE_MEDIA_URL;
			}

			$media_url = $product->get_meta( $meta_key );
			if ( $media_url ) {
				$html = Media\Factory::get_media( $media_url, 'paywall-for-woocommerce' )->get_embed_html();
			}
		}

		return $html;
	}

	/**
	 * Force no-cache our product pages, so we display proper buttons and proper content.
	 *
	 * @return void
	 */
	protected function prevent_caching() {

		$product = \WC_Product_Paywall::wc_get_product();

		if ( $product && $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) ) {
			\nocache_headers();
		}
	}

	/**
	 * Call {@see show_buttons_on_order_received_page} before the 'thankyou' template.
	 *
	 * @since        3.0.6
	 *
	 * @param string $template_name Template name.
	 * @param string $template_path Template path (Unused).
	 * @param string $located       Located template (Unused).
	 * @param array  $args          Arguments (We need the Order ID from here).
	 *
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function action__on_checkout_thankyou(
		$template_name,
		$template_path,
		$located,
		$args
	) {
		if (
			'checkout/thankyou.php' === $template_name
			&& isset( $args['order'] )
			&& $args['order'] instanceof \WC_Order
		) {
			$this->show_buttons_on_order_received_page( $args['order']->get_id() );
		}
	}

	/**
	 * Show the "View" buttons on the "Order Received" page.
	 * If logged in, these are just links to the products, to avoid questions "I paid, now what?"
	 * If guest order, products in session are validated on the product page.
	 *
	 * @param int $order_id Order ID.
	 *
	 * @internal Action.
	 */
	public function show_buttons_on_order_received_page( $order_id ) {

		if ( (int) $order_id <= 0 ) {
			return;
		}

		$order_info = new Info( $order_id );
		if ( $order_info->is_not_mine() ) {
			return;
		}
		if ( ! $order_info->is_paid() ) {
			return;
		}

		/**
		 * Add this purchase to session - for Guest users.
		 */
		Session::add_to_orders( $order_id );
		$order_items = $order_info->get_items();
		foreach ( $order_items as $item ) {
			$product = $item->get_product();
			if ( $product->is_type( \WC_Product_Paywall::PRODUCT_TYPE ) ) {
				Session::add_to_purchased( $product->get_id() );
			}
		}
		/**
		 * Reload the products after adding to session.
		 *
		 * @since 1.0.0
		 */
		\do_action( 'woopaywall_load_purchased_products' );

		/**
		 * Template.
		 *
		 * @see \WOOPAYWALL\Template\bookmark__template_order_received
		 */
		$template_name = 'paywall-order-received.php';
		\WOOPAYWALL\Template\Controller::load( $template_name );
	}
}

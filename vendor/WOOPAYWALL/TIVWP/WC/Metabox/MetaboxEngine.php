<?php
/**
 * Metabox engine.
 *
 * @since  1.6.0
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Metabox;

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use WOOPAYWALL\Dependencies\TIVWP\AbstractApp;
use WOOPAYWALL\Dependencies\TIVWP\Abstracts\MetaSetInterface;
use WOOPAYWALL\Dependencies\TIVWP\Env;
use WOOPAYWALL\Dependencies\TIVWP\InterfaceHookable;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Log;
use WOOPAYWALL\Dependencies\TIVWP\Logger\Message;
use WOOPAYWALL\Dependencies\TIVWP\UniMeta\AbstractUniMeta;
use WOOPAYWALL\Dependencies\TIVWP\UniMeta\UniMeta_Factory;

/**
 * Class
 *
 * @since  1.6.0
 */
class MetaboxEngine implements InterfaceHookable {

	/**
	 * Nonce name.
	 *
	 * @since  1.6.0
	 * @var string
	 */
	const NONCE_NAME = 'tivwp_nonce_name';

	/**
	 * Nonce action.
	 *
	 * @since  1.6.0
	 * @var string
	 */
	const NONCE_ACTION = 'tivwp_nonce_action';

	/**
	 * Meta action constant for the tivwp_meta_changed action.
	 *
	 * @since  1.10.0
	 * @var string
	 */
	const META_ACTION_DELETE_EMPTY = 'delete_empty';

	/**
	 * Meta action constant for the tivwp_meta_changed action.
	 *
	 * @since  1.10.0
	 * @var string
	 */
	const META_ACTION_DELETE_ABSENT = 'delete_absent';

	/**
	 * Meta action constant for the tivwp_meta_changed action.
	 *
	 * @since  1.10.0
	 * @var string
	 */
	const META_ACTION_UPDATE = 'update';

	/**
	 * List of the admin screens where to add these metaboxes.
	 *
	 * @since  1.6.0
	 * @var string[]
	 */
	protected $screens = array();

	/**
	 * MetaSet.
	 *
	 * @since  1.6.0
	 * @var MetaSetInterface
	 */
	protected $meta_set;

	/**
	 * Var current_screen.
	 *
	 * @since 1.9.0
	 *
	 * @var \WP_Screen
	 */
	protected $current_screen;

	/**
	 * Constructor.
	 *
	 * @since  1.6.0
	 *
	 * @param MetaSetInterface $meta_set MetaSet.
	 * @param string|string[]  $screens  Screen(s).
	 */
	public function __construct( MetaSetInterface $meta_set, $screens ) {

		$this->meta_set = $meta_set;
		$this->screens  = (array) $screens;
	}

	/**
	 * Setup actions and filters.
	 *
	 * @since  1.6.0
	 * @return void
	 */
	public function setup_hooks() {

		\add_action( 'current_screen', function ( $current_screen ) {

			if ( ! $current_screen instanceof \WP_Screen ) {
				return;
			}
			$this->current_screen = $current_screen;

			\add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

			if ( $this->is_order_screen_id( $this->current_screen->id ) ) {
				\add_action( 'woocommerce_process_shop_order_meta', array(
					$this,
					'action__woocommerce_process_shop_order_meta',
				), 10, 2 );
			} elseif ( $this->is_product_screen_id( $this->current_screen->id ) ) {
				\add_action( 'woocommerce_process_product_meta', array(
					$this,
					'action__woocommerce_process_product_meta',
				), AbstractApp::HOOK_PRIORITY_EARLY, 2 );
			} else {
				\add_action( 'save_post', array( $this, 'action__save_post' ) );
			}

			( new WooScriptLoader() )->setup_hooks();
		} );
	}

	/**
	 * Convert screen_id for new WC features, i.e., HPOS.
	 *
	 * @since 1.9.0
	 *
	 * @param string $screen_id Screen ID.
	 *
	 * @return string
	 */
	protected function convert_screen_id( $screen_id ) {
		if ( 'shop_order' === $screen_id
			 && class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController', false )
			 && \wc_get_container()
				 ->get( CustomOrdersTableController::class )
				 ->custom_orders_table_usage_is_enabled()
		) {
			$screen_id = \wc_get_page_screen_id( 'shop-order' );
		}

		return $screen_id;
	}

	/**
	 * Maybe convert screens.
	 *
	 * @since 1.9.0
	 *
	 * @return void
	 */
	protected function maybe_convert_screens() {
		foreach ( $this->screens as &$screen_id ) {
			$screen_id = $this->convert_screen_id( $screen_id );
		}
	}

	/**
	 * Is order screen_id?
	 *
	 * @since 1.9.0
	 *
	 * @param string $screen_id Screen ID.
	 *
	 * @return bool
	 * @todo  Do not hardcode 'woocommerce_page_wc-orders'
	 *
	 */
	protected function is_order_screen_id( $screen_id ) {
		return in_array( $screen_id, array( 'shop_order', 'woocommerce_page_wc-orders' ), true );
	}

	/**
	 * Is product screen_id?
	 *
	 * @since 1.9.0
	 *
	 * @param string $screen_id Screen ID.
	 *
	 * @return bool
	 *
	 */
	protected function is_product_screen_id( $screen_id ) {
		return 'product' === $screen_id;
	}

	/**
	 * Add metaboxes.
	 *
	 * @since    1.6.0
	 * @since    1.9.0 Convert screens.
	 *
	 * @internal action
	 */
	public function add_meta_boxes() {
		$screen_id = $this->current_screen->id;

		$this->maybe_convert_screens();
		if ( in_array( $screen_id, $this->screens, true ) ) {
			\add_meta_box(
				'tivwp-metabox-' . $screen_id,
				$this->meta_set->get_title(),
				array( $this, 'meta_box_callback' ),
				$screen_id,
				'advanced',
				'high'
			);
		}
	}

	/**
	 * Metabox callback.
	 *
	 * @since    1.6.0
	 *
	 * @param \WP_Post $post The Post object.
	 *
	 * @internal callback.
	 */
	public function meta_box_callback( $post ) {
		\wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$this->field_generator( $post );
	}

	/**
	 * Metabox content.
	 *
	 * @since  1.6.0
	 *
	 * @param \WP_Post $post The Post object.
	 */
	protected function field_generator( $post ) {

		$uni_meta = UniMeta_Factory::get_object( $post );
		if ( ! $uni_meta ) {
			return;
		}

		$output = '';
		foreach ( $this->meta_set->get_meta_fields() as $meta_field ) {

			try {
				if ( empty( $meta_field['id'] ) ) {
					throw new Message( 'empty( $meta_field["id"] )' );
				}

			} catch ( Message $e ) {
				Log::error( $e );

				continue;
			}

			if ( ! empty( $meta_field['label'] ) ) {
				$label = '<label for="' . $meta_field['id'] . '">' . $meta_field['label'] . '</label>';
			} else {
				$label = '';
			}

			switch ( $meta_field['type'] ) {
				case 'product_categories':
					$input = Fields\SelectProductCategories::render( $meta_field, $uni_meta );
					break;
				case 'product':
					$input = Fields\SelectProducts::render( $meta_field, $uni_meta );
					break;
				case 'multiselect':
					$input = Fields\MultiSelect::render( $meta_field, $uni_meta );
					break;
				case 'select':
					$input = Fields\Select::render( $meta_field, $uni_meta );
					break;
				case 'datetime':
					$input = Fields\DateTime::render( $meta_field, $uni_meta );
					break;
				case 'hidden':
					$input = Fields\Hidden::render( $meta_field, $uni_meta );
					break;
				default:
					$input = '';
			}

			if ( ! empty( $meta_field['desc'] ) ) {
				$input .= '<p id="' . $meta_field['id'] . '_desc" class="description">' . $meta_field['desc'] . '</p>';
			}

			if ( 'hidden' === $meta_field['type'] ) {
				$output .= $input;
			} else {
				$output .= $this->format_rows( $label, $input );
			}
		}

		$allowed_tags = array(
			'script' => true,
			'option' => array(
				'selected' => true,
				'value'    => true,
			),
			'label'  => array( 'for' => true ),
			'input'  => array(
				'aria-label' => true,
				'class'      => true,
				'id'         => true,
				'name'       => true,
				'style'      => true,
				'type'       => true,
				'value'      => true,
			),
			'select' => array(
				'id'               => true,
				'name'             => true,
				'class'            => true,
				'multiple'         => true,
				'style'            => true,
				'aria-label'       => true,
				'data-sortable'    => true,
				'data-placeholder' => true,
				'data-action'      => true,
				'data-exclude'     => true,
			),
			'tr'     => array( 'class' => true ),
			'th'     => array(
				'class' => true,
				'scope' => true,
			),
			'td'     => true,
			'p'      => array(
				'id'    => true,
				'style' => true,
				'class' => true,
			),
			'br'     => true,
		);

		echo '<table class="form-table"><tbody>'
			 . \wp_kses( $output, $allowed_tags )
			 . '</tbody></table>';
	}

	/**
	 * Put values in a table row.
	 *
	 * @since  1.6.0
	 *
	 * @param string $th Content of the <th> tag.
	 * @param string $td Content of the <td> tag.
	 *
	 * @return string
	 */
	protected function format_rows( $th, $td ) {
		return '<tr><th scope="row" class="titledesc">' . $th . '</th><td>' . $td . '</td></tr>';
	}

	/**
	 * Method handle_meta_updates.
	 *
	 * @since 1.9.0
	 *
	 * @param AbstractUniMeta $uni_meta UniMeta object.
	 *
	 * @return void
	 */
	protected function handle_meta_updates( $uni_meta ) {
		$meta_set = $this->meta_set;
		foreach ( $meta_set->get_meta_fields() as $meta_field ) {
			$meta_key = $meta_field['id'];
			if ( Env::is_parameter_in_http_post( $meta_key ) ) {
				$meta_value = Env::get_http_post_parameter( $meta_key );
				if ( ! $meta_value && ! empty( $meta_field['delete_empty'] ) ) {
					$uni_meta->delete_meta( $meta_key );
					$meta_action = self::META_ACTION_DELETE_EMPTY;

					/**
					 * Action tivwp_meta_deleted.
					 *
					 * @deperecated Use tivwp_meta_changed
					 * @since       1.9.0
					 *
					 * @param array            $meta_field Meta field.
					 * @param MetaSetInterface $meta_set   MetaSet.
					 */
					\do_action( 'tivwp_meta_deleted', $meta_field, $meta_set );
				} else {

					// Do not set meta if value has not changed.
					$existing_value = $uni_meta->get_meta( $meta_key );
					if ( $existing_value === $meta_value ) {
						continue;
					}

					$uni_meta->set_meta( $meta_key, $meta_value );
					$meta_action = self::META_ACTION_UPDATE;

					/**
					 * Action tivwp_meta_updated.
					 *
					 * @deperecated Use tivwp_meta_changed
					 * @since       1.9.0
					 *
					 * @param array            $meta_field Meta field.
					 * @param MetaSetInterface $meta_set   MetaSet.
					 */
					\do_action( 'tivwp_meta_updated', $meta_field, $meta_value, $meta_set );
				}
			} else {
				// Meta not in $POST. Delete.
				$uni_meta->delete_meta( $meta_key );
				$meta_action = self::META_ACTION_DELETE_ABSENT;
				$meta_value  = '';
			}

			/**
			 * Act on meta changes.
			 *
			 * @since 1.10.0
			 *
			 * @param string           $meta_action Meta action - see constants.
			 * @param array            $meta_field  Meta field.
			 * @param mixed            $meta_value  Meta value.
			 * @param MetaSetInterface $meta_set    MetaSet.
			 * @param AbstractUniMeta  $uni_meta    UniMeta.
			 */
			\do_action(
				'tivwp_meta_changed',
				$meta_action,
				$meta_field,
				$meta_value,
				$meta_set,
				$uni_meta
			);
		}
	}

	/**
	 * Save meta - generic.
	 *
	 * @since    1.6.0
	 *
	 * @param int $post_id Post ID.
	 *
	 * @internal action.
	 */
	public function action__save_post( $post_id ) {

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) ) {
			return;
		}

		if ( ! \wp_verify_nonce( \wc_clean( \wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		$uni_meta = UniMeta_Factory::get_object( $post_id );
		if ( $uni_meta ) {
			$this->handle_meta_updates( $uni_meta );
		}

	}

	/**
	 * Save meta - for shop order.
	 *
	 * @since        1.9.0
	 *
	 * @param int       $order_id Order ID (Unused).
	 * @param \WC_Order $order    Order object.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function action__woocommerce_process_shop_order_meta( $order_id, $order ) {

		$uni_meta = UniMeta_Factory::get_object( $order );
		$this->handle_meta_updates( $uni_meta );
		$uni_meta->save_object();

	}

	/**
	 * Save meta - for product.
	 * do_action( 'woocommerce_process_' . $post->post_type . '_meta', $post_id, $post );
	 *
	 * @since        1.9.0
	 *
	 * @param int      $post_id                  Post ID (Unused).
	 * @param \WP_Post $post_having_type_product Post object.
	 *
	 * @return void
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function action__woocommerce_process_product_meta( $post_id, $post_having_type_product ) {

		$uni_meta = UniMeta_Factory::get_object( $post_having_type_product );
		$this->handle_meta_updates( $uni_meta );
		$uni_meta->save_object();

	}
}

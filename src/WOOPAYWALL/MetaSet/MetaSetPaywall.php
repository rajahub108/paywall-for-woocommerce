<?php
/**
 * MetaSet Paywall
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\MetaSet;

use WOOPAYWALL\Dependencies\TIVWP\Abstracts\MetaSetInterface;

/**
 * Class
 */
class MetaSetPaywall implements MetaSetInterface {

	/**
	 * Meta keys.
	 *
	 * @var array
	 */
	const META_KEYS = array(
		'EXPIRE_ON' => array( 'ID' => '_woopaywall_expire_on', 'DEFAULT' => '' ),
	);


	/**
	 * Returns the MetaSet title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Paywall', 'paywall-for-woocommerce' );
	}

	/**
	 * Returns the MetaSet fields.
	 *
	 * @return array[]
	 */
	public function get_meta_fields() {
		return array(
			array(
				'type'               => 'datetime',
				'label'              => implode( '<br>', array(
					\__( 'Set expiration date/time:', 'paywall-for-woocommerce' ),
					// Translators: %s holds the Time zone string.
					sprintf( \__( '(Time zone: %s)', 'paywall-for-woocommerce' ), \wc_timezone_string() ),
				) ),
				'id'                 => self::META_KEYS['EXPIRE_ON']['ID'],
				'default'            => self::META_KEYS['EXPIRE_ON']['DEFAULT'],
				'delete_empty'       => true,
				'desc'               => implode( '<br>', array(
					\__( 'If set, access to all Paywall products in this order will expire at the date/time specified.', 'paywall-for-woocommerce' ),
					\__( 'To remove, make this field blank and save the order.', 'paywall-for-woocommerce' ),
				) ),
				'unsupported'        => \__( 'Sorry, your browser does not support this type of input field', 'paywall-for-woocommerce' ),
				// Translators: %s - placeholder for date.
				'order_note_updated' => \__( 'Paywall: Order expiration set to: %s', 'paywall-for-woocommerce' ),
				'order_note_deleted' => \__( 'Paywall: Order expiration removed', 'paywall-for-woocommerce' ),
			),
		);
	}
}

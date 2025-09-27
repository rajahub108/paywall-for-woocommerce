<?php
/**
 * Interface Alter.
 *
 * @since 3.0.0
 * Copyright (c) 2021, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Alter;

interface InterfaceAlter {

	/**
	 * Settings section ID.
	 *
	 * @return string
	 */
	public static function get_section_id();

	/**
	 * Default field option for a specific product state.
	 *
	 * @param string $product_state [Optional] State of the product: 'PAID', 'IN_CART'.
	 *
	 * @return string
	 * @noinspection PhpUnused
	 */
	public static function get_default_option_value( $product_state = '' );
}

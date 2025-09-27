<?php
/**
 * ProductState
 *
 * @since 4.0.0
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL;

/**
 * Class ProductState
 *
 * @since 4.0.0
 */
class ProductState {

	/**
	 * Product state.
	 *
	 * @since 3.0.0
	 * @since 4.0.0 Moved to the ProductState class.
	 * @var string
	 */
	const AVAILABLE       = 'AVAILABLE';
	const PAID            = 'PAID';
	const IN_CART         = 'IN_CART';
	const OUT_OF_STOCK    = 'OUT_OF_STOCK';
	const NOT_PURCHASABLE = 'NOT_PURCHASABLE';
}

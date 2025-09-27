<?php
/**
 * WOOPAYWALL\Dependencies\TIVWP
 *
 * @since 1.12.1
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP;

/**
 * Class WOOPAYWALL\Dependencies\TIVWP
 *
 * @since 1.12.1
 */
class TIVWP {

	/**
	 * Version
	 *
	 * @since 1.12.1
	 * @var string
	 */
	const VERSION = '1.12.4';

	/**
	 * Version string to be used in the `ver` parameter of enqueue script/style.
	 *
	 * @since 1.12.1
	 * @return string
	 */
	public static function ver() {
		return Constants::is_true('WP_LOCAL_DEV') ? (string) time() : self::VERSION;
	}

}

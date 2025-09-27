<?php
/**
 * AbstractCacheVar
 *
 * @since 4.0.0
 *
 * Copyright (c) 2023, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Abstracts;

/**
 * Class AbstractCacheVar
 *
 * @since 4.0.0
 */
class AbstractCacheVar {

	/**
	 * Cache.
	 *
	 * @since 4.0.0
	 *
	 * @var array
	 */
	protected static $cache = array();

	/**
	 * Getter.
	 *
	 * @since        4.0.0
	 * @return array
	 * @noinspection PhpUnused
	 */
	public static function getCache(): array {
		return self::$cache;
	}
}

<?php
/**
 * Abstract Integration Class.
 *
 * @since   2.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Integration;

use WOOPAYWALL\Abstracts\Hookable;

/**
 * Class AbstractIntegration
 */
abstract class AbstractIntegration extends Hookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	abstract public function setup_hooks();
}

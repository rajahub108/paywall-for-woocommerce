<?php
/**
 * Interface "Hookable".
 *
 * @noinspection PhpUnused
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP;

/**
 * Interface InterfaceHookable
 */
interface InterfaceHookable {

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 * @noinspection PhpUnused
	 */
	public function setup_hooks();
}

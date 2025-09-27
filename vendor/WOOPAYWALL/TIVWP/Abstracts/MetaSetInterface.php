<?php
/**
 * MetaSet interface
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Abstracts;

/**
 * Interface MetaSetInterface
 *
 * @package A4WCS\Abstracts
 */
interface MetaSetInterface {

	/**
	 * Returns the MetaSet fields.
	 *
	 * @return array[]
	 */
	public function get_meta_fields();

	/**
	 * Returns the MetaSet title.
	 *
	 * @return string
	 */
	public function get_title();

}

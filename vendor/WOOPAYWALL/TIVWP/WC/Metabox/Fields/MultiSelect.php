<?php
/**
 * Metabox field: multiselect.
 *
 * @since  1.6.0
 * Copyright (c) TIV.NET INC 2021.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC\Metabox\Fields;

/**
 * Class MultiSelect
 *
 * @since  1.6.0
 * @since  1.9.0 Extends Select.
 */
class MultiSelect extends Select {

	/**
	 * Is multiple select?
	 *
	 * @since 1.9.0
	 * @return bool
	 */
	protected static function is_multiple() {
		return true;
	}
}

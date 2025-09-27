<?php
/**
 * User Factory
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\User;

/**
 * Class UserFactory
 *
 * @package WOOPAYWALL\User
 */
class UserFactory {

	/**
	 * Construct an instance of the current user.
	 *
	 * @return Customer|Guest
	 */
	public static function get_current_user() {
		$current_user_id = \get_current_user_id();

		return $current_user_id ? new Customer( $current_user_id ) : new Guest();
	}
}

<?php
/**
 * Settings section
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings;

use WOOPAYWALL\Dependencies\TIVWP\Abstracts\AbstractData;

/**
 * Class Section.
 *
 * @since 3.0.0
 */
class Section extends AbstractData {

	/**
	 * Field sections prefix.
	 *
	 * @since 3.0.0
	 * @var string
	 */
	const SECTION_ID_PREFIX = 'woopaywall_';

	/**
	 * Core data for this object. Name value pairs (name + default value).
	 *
	 * @since 3.0.0
	 * @var array $data
	 */
	protected $data = array(
		'id'    => '',
		'title' => '',
		'desc'  => '',
	);

	/**
	 * Make section ID by adding prefix.
	 *
	 * @since 3.0.0
	 *
	 * @param string $id Section ID w/o prefix, underscored.
	 *
	 * @return string
	 */
	public static function make_id( $id ) {
		return self::SECTION_ID_PREFIX . $id;
	}

	/**
	 * Get ID
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public function get_id() {
		return self::make_id( parent::get_id() );
	}

	/**
	 * Make the section start array.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_start() {
		return array(
			'id'    => $this->get_id(),
			'title' => $this->get_title(),
			'desc'  => $this->get_desc(),
			'type'  => 'title',
		);
	}

	/**
	 * Make the section end array.
	 *
	 * @since 3.0.0
	 * @return array
	 */
	public function get_end() {
		return array(
			'id'   => $this->get_id(),
			'type' => 'sectionend',
		);
	}

	/**
	 * Method text_do_not_change.
	 *
	 * @since 3.0.0
	 * @return string
	 */
	public static function text_do_not_change() {
		return \_x(
			'-- Do not change the default settings --',
			'settings',
			'paywall-for-woocommerce'
		);
	}
}

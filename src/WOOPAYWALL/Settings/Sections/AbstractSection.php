<?php
/**
 * Abstract settings section.
 *
 * @since 3.0.0
 * Copyright (c) 2020, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Settings\Sections;

use WOOPAYWALL\Abstracts\Hookable;
use WOOPAYWALL\App;
use WOOPAYWALL\Settings\Section;

/**
 * Class AbstractSection
 *
 * @package WOOPAYWALL\Settings\Sections
 */
abstract class AbstractSection extends Hookable {

	/**
	 * ID.
	 *
	 * @var string
	 */
	protected $id = '';

	/**
	 * Getter.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Setter.
	 *
	 * @param string $id
	 */
	public function set_id( $id ) {
		$this->id = $id;
	}

	/**
	 * Title.
	 *
	 * @var string
	 */
	protected $title = '';

	/**
	 * Getter.
	 *
	 * @return string
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Setter.
	 *
	 * @param string $title
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Description.
	 *
	 * @var string
	 */
	protected $desc = '';

	/**
	 * Getter.
	 *
	 * @return string
	 */
	public function get_desc() {
		return $this->desc;
	}

	/**
	 * Setter.
	 *
	 * @param string $desc
	 */
	public function set_desc( $desc ) {
		$this->desc = $desc;
	}

	/**
	 * Priority.
	 *
	 * @var int
	 */
	protected $priority = 10;

	/**
	 * Getter.
	 *
	 * @return int
	 */
	public function get_priority() {
		return $this->priority;
	}

	/**
	 * Setter.
	 *
	 * @param int $priority
	 */
	public function set_priority( $priority ) {
		$this->priority = $priority;
	}

	/**
	 * Setup actions and filters.
	 *
	 * @return void
	 */
	public function setup_hooks() {

		\add_filter( 'woocommerce_get_settings_' . App::TAB_SLUG, array( $this, 'section' ), $this->get_priority() );
	}

	/**
	 * Section.
	 *
	 * @param array $settings The "All Settings" array.
	 *
	 * @return array
	 */
	public function section( array $settings ) {

		$id = $this->get_id();
		if ( ! $id ) {
			// Child class did not set the section ID. Ignore.
			return $settings;
		}

		$section = new Section();
		$section
			->set_id( $id )
			->set_title( $this->get_title() )
			->set_desc( $this->get_desc() );

		$settings[] = $section->get_start();

		$this->add_fields( $settings );

		$settings[] = $section->get_end();

		return $settings;
	}

	/**
	 * Add fields to section.
	 *
	 * @param array $settings The "All Settings" array (reference).
	 */
	protected function add_fields( &$settings ) {
	}
}

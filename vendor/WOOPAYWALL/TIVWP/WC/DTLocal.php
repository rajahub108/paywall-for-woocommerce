<?php
/**
 * DateTime extension. Automatically set local time zone.
 *
 * @since  1.6.0
 * Copyright (c) 2021, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\WC;

use WOOPAYWALL\Dependencies\TIVWP\Logger\Log;

/**
 * Class DTLocal
 */
class DTLocal extends \WC_DateTime {

	/**
	 * DTLocal constructor.
	 *
	 * Parent:
	 *
	 * @link https://php.net/manual/en/datetime.construct.php
	 *
	 * @param string        $datetime [optional]
	 *                                <p>A date/time string. Valid formats are explained in {@link https://php.net/manual/en/datetime.formats.php Date and Time Formats}.</p>
	 *                                <p>
	 *                                Enter <b>now</b> here to obtain the current time when using
	 *                                the <em>$timezone</em> parameter.
	 *                                </p>
	 * @param \DateTimeZone $timezone [optional] <p>
	 *                                A {@link https://php.net/manual/en/class.datetimezone.php DateTimeZone} object representing the
	 *                                timezone of <em>$datetime</em>.
	 *                                </p>
	 *                                <p>
	 *                                If <em>$timezone</em> is omitted,
	 *                                the current timezone will be used.
	 *                                </p>
	 *                                <blockquote><p><b>Note</b>:
	 *                                </p><p>
	 *                                The <em>$timezone</em> parameter
	 *                                and the current timezone are ignored when the
	 *                                <em>$time</em> parameter either
	 *                                is a UNIX timestamp (e.g. <em>@946684800</em>)
	 *                                or specifies a timezone
	 *                                (e.g. <em>2010-01-28T15:00:00+02:00</em>).
	 *                                </p> <p></p></blockquote>
	 *
	 * @throws \Exception
	 */
	public function __construct( $datetime = 'now', \DateTimeZone $timezone = null ) {

		try {
			parent::__construct( $datetime, $timezone );
		} catch ( \Exception $e ) {
			Log::error( $e );

			return;
		}

		$this->set_local_timezone();
	}

	/**
	 * Set timezone to the WordPress site's timezone, or a UTC offset
	 * if no timezone string is available.
	 *
	 * @return self
	 * @throws \Exception
	 */
	public function set_local_timezone() {

		if ( \get_option( 'timezone_string' ) ) {
			$this->setTimezone( new \DateTimeZone( \wc_timezone_string() ) );
		} else {
			$this->set_utc_offset( \wc_timezone_offset() );
		}

		return $this;
	}

	/**
	 * Return true is this DateTime instance is already in the past.
	 *
	 * @return bool
	 */
	public function is_in_the_past() {
		$now = new self();

		return ( $now > $this );
	}
}

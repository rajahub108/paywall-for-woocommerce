<?php
/**
 * Logging to WooCommerce Status->Logs
 *
 * @since 1.0.0
 * Copyright (c) 2019, TIV.NET INC. All Rights Reserved.
 */

namespace WOOPAYWALL\Dependencies\TIVWP\Logger;

use WOOPAYWALL\Dependencies\TIVWP\Env;

/**
 * WC_Logger wrapper.
 *
 * @since 1.0.0
 */
class Log {

	/**
	 * Default log level.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const LOG_LEVEL_NONE = '';

	/**
	 * Adding tracing to the Debug log level.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	const LOG_LEVEL_TRACE = 'trace';

	/**
	 * Is tracing needed?
	 *
	 * @since 1.0.0
	 *
	 * @var bool
	 */
	protected static $need_tracing = false;

	/**
	 * Randomly generated "Request ID" to distinguish between parallel calls.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	protected static $request_id = '';

	/**
	 * Getter: $request_id.
	 *
	 * @since 1.7.0
	 *
	 * @return string
	 */
	protected static function getRequestId() {
		if ( ! self::$request_id ) {
			if ( Env::is_doing_ajax() ) {
				$request_type = 'A-';
			} elseif ( '/wp-json/' === substr( self::getRequestURI(), 0, strlen( '/wp-json/' ) ) ) {
				$request_type = 'J-';
			} else {
				$request_type = 'R-';
			}
			self::$request_id = $request_type . \wp_rand( 1000, 9999 );
		}

		return self::$request_id;
	}

	/**
	 * Request URI - "cached" here to have fewer calls to `Env`.
	 *
	 * @since 1.7.0
	 * @var string
	 */
	protected static $request_uri = '';

	/**
	 * Getter: $request_uri.
	 *
	 * @since 1.7.0
	 * @return string
	 */
	protected static function getRequestURI() {
		if ( ! self::$request_uri ) {
			self::$request_uri = Env::request_uri();
		}

		return self::$request_uri;
	}

	/**
	 * Log source.
	 *
	 * @since 1.0.0
	 * @since 1.9.0 Set default source.
	 *
	 * @return string
	 */
	protected static function source() {
		return 'TIVWP';
	}

	/**
	 * Log level defined in the application settings.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	protected static function threshold() {
		return \WC_Log_Levels::DEBUG;
	}

	/**
	 * Get the last two tokens of the file path.
	 *
	 * @since 1.7.0
	 * @return string
	 */
	protected static function short_path( $path ) {
		$normalized_path = \wp_normalize_path( $path );

		return basename( dirname( $normalized_path ) ) . '/' . basename( $normalized_path );
	}

	/**
	 * Some backtrace args arrays do not hold any info, just blanks.
	 *
	 * @since 1.7.0
	 *
	 * @param array $a Array.
	 *
	 * @return bool
	 */
	protected static function is_trivial_array( $a ) {
		if ( empty( $a ) ) {
			return true;
		}

		if ( isset( $a[0] ) && empty( $a[0] ) ) {
			return true;
		}

		if ( is_array( $a[0] ) && isset( $a[0][0] ) && empty( $a[0][0] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Maybe truncate string to max chars.
	 *
	 * @since 1.7.0
	 *
	 * @param string $sz String.
	 *
	 * @return string
	 */
	protected static function maybe_truncate( $sz ) {
		if ( strlen( $sz ) > 400 ) {
			return substr( $sz, 0, 400 ) . '...)';
		}

		return $sz;
	}

	/**
	 * Convert backtrace args to string.
	 *
	 * @since 1.7.0
	 *
	 * @param array $a args.
	 *
	 * @return string
	 */
	protected static function args_as_string( $a ) {
		if ( self::is_trivial_array( $a ) ) {
			return '';
		}

		if ( 1 === count( $a ) && is_string( $a[0] ) ) {
			return "'" . trim( $a[0] ) . "'";
		}

		$safe_text = \maybe_serialize( $a );

		$safe_text = str_replace( "\r", '\\r', $safe_text );
		$safe_text = str_replace( "\n", '\\n', $safe_text );

		return self::maybe_truncate( $safe_text );
	}

	/**
	 * Convert backtrace array to string (multiline).
	 *
	 * @since 1.7.0
	 *
	 * @param array $backtrace Backtrace.
	 *
	 * @return string
	 */
	protected static function backtrace_as_string( $backtrace ) {
		$out = array();
		foreach ( $backtrace as $num => $step ) {

			if ( empty( $step['file'] ) ) {
				// Strange...
				continue;
			}

			$line = "\t#" . $num . ' ' . self::short_path( $step['file'] );
			if ( ! empty( $step['line'] ) ) {
				$line .= '(' . $step['line'] . '): ';
			}
			if ( ! empty( $step['class'] ) && ! empty( $step['type'] ) ) {
				$line .= $step['class'] . $step['type'];
			}
			if ( ! empty( $step['function'] ) ) {
				$line .= $step['function'];
			}
			$line .= '(' . ( isset( $step['args'] ) ? self::args_as_string( $step['args'] ) : '' ) . ')';

			$out[] = $line;
		}

		return PHP_EOL . implode( PHP_EOL, $out );
	}

	/**
	 * Build the log entry string.
	 *
	 * @since 1.7.0
	 * @since 1.11.1 Fix: message is instance of Exception.
	 *
	 * @param string|string[]|Message $message Message to log.
	 *
	 * @return string
	 */
	protected static function build_log_entry( $message ) {
		$to_send = array();

		if ( $message instanceof \Exception ) {
			$file      = self::short_path( $message->getFile() );
			$line      = $message->getLine();
			$file_line = $file . '(' . $line . ')';

			$to_send = array(
				$message->getMessage(),
				$file_line,
				self::getRequestURI(),
			);
			if ( self::$need_tracing ) {
				$to_send[] = self::backtrace_as_string( $message->getTrace() );
			}
		} elseif ( is_array( $message ) || is_string( $message ) ) {
			$backtrace = Env::get_trace();

			// Remove all entries of this file from the trace.
			$this_file = basename( __FILE__ );
			while ( basename( $backtrace[0]['file'] ) === $this_file ) {
				array_shift( $backtrace );
			}

			$file      = self::short_path( $backtrace[0]['file'] );
			$line      = $backtrace[0]['line'];
			$file_line = $file . '(' . $line . ')';

			$to_send   = (array) $message;
			$to_send[] = $file_line;
			$to_send[] = self::getRequestURI();
			if ( self::$need_tracing ) {
				array_shift( $backtrace );
				$to_send[] = self::backtrace_as_string( $backtrace );
			}
		}

		if ( ! empty( $to_send ) ) {
			array_unshift( $to_send, self::getRequestId() );

			return implode( Message::GLUE, $to_send );
		}

		return '';

	}

	/**
	 * Check if we should handle messages of this level.
	 *
	 * @since 1.0.0
	 *
	 * @param string $level The log level.
	 *
	 * @return bool
	 */
	protected static function should_handle( $level ) {

		if ( defined( 'DOING_PHPUNIT' ) ) {
			return false;
		}

		// Log level from the settings.
		$option_log_level = static::threshold();

		if ( self::LOG_LEVEL_NONE === $option_log_level ) {
			// "No log" asked in settings (also the default).
			return false;
		}

		/**
		 * Trace level is same as debug, but we set the "need tracing" flag.
		 */
		if ( self::LOG_LEVEL_TRACE === $option_log_level ) {
			$option_log_level   = \WC_Log_Levels::DEBUG;
			self::$need_tracing = true;
		}

		if ( \WC_Log_Levels::DEBUG === $option_log_level ) {
			// "Debug" asked in settings. Log everything.
			return true;
		}

		// Write messages with severity higher or equal to the asked in settings.
		$level_severity            = \WC_Log_Levels::get_level_severity( $level );
		$option_log_level_severity = \WC_Log_Levels::get_level_severity( $option_log_level );

		return $level_severity >= $option_log_level_severity;
	}

	/**
	 * Adds an error level message.
	 *
	 * @since 1.0.0
	 * @since 1.7.0 Optionally pass $context.
	 *
	 * @param string|string[]|Message $message Message to log.
	 * @param array                   $context Additional information for log handlers.
	 */
	public static function error( $message, $context = array() ) {
		self::log( \WC_Log_Levels::ERROR, $message, $context );
	}

	/**
	 * Adds an info level message.
	 *
	 * @since 1.0.0
	 * @since 1.7.0 Optionally pass $context.
	 *
	 * @param string|string[]|Message $message Message to log.
	 * @param array                   $context Additional information for log handlers.
	 */
	public static function info( $message, $context = array() ) {
		self::log( \WC_Log_Levels::INFO, $message, $context );
	}

	/**
	 * Adds a debug level message.
	 *
	 * @since 1.0.0
	 * @since 1.7.0 Optionally pass $context.
	 *
	 * @param string|string[]|Message $message Message to log.
	 * @param array                   $context Additional information for log handlers.
	 */
	public static function debug( $message, $context = array() ) {
		self::log( \WC_Log_Levels::DEBUG, $message, $context );
	}

	/**
	 * Add a log entry by calling @see \WC_Logger::log().
	 *
	 * @since 1.0.0
	 * @since 1.7.0 Optionally pass $context.
	 *
	 * @param string                  $level      One of the following:
	 *                                            'emergency': System is unusable.
	 *                                            'alert': Action must be taken immediately.
	 *                                            'critical': Critical conditions.
	 *                                            'error': Error conditions.
	 *                                            'warning': Warning conditions.
	 *                                            'notice': Normal but significant condition.
	 *                                            'info': Informational messages.
	 *                                            'debug': Debug-level messages.
	 * @param string|string[]|Message $message    Message to log.
	 * @param array                   $context    Additional information for log handlers.
	 */
	public static function log( $level, $message, $context = array() ) {

		if ( ! self::should_handle( $level ) ) {
			return;
		}

		$log_entry_string = self::build_log_entry( $message );
		if ( empty( $log_entry_string ) ) {
			return;
		}

		if ( empty( $context ) ) {
			$context = array( 'source' => static::source() );
		}

		\wc_get_logger()->log( $level, $log_entry_string, $context );
	}
}

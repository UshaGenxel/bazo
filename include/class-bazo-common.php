<?php
/**
 * Common helpers for Bazo theme
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

class Bazo_Common {

	/**
	 * Parse date and time strings into a Unix timestamp in WP timezone.
	 *
	 * Accepts dates like: Y-m-d, d/m/Y, m/d/Y, d-m-Y, m-d-Y
	 * Accepts times like: H:i, H:i:s, g:i a, g:i A, g a, g A
	 *
	 * @param string $date_str
	 * @param string $time_str
	 * @param string $default_time
	 * @return int|null
	 */
	public static function parse_datetime_to_ts( $date_str, $time_str = '', $default_time = '23:59:59' ) {
		if ( empty( $date_str ) ) {
			return null;
		}

		$tz = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( date_default_timezone_get() );

		$date_formats = [ 'Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y' ];
		$time_value   = $time_str ? trim( $time_str ) : $default_time;
		$time_formats = $time_str ? [ 'H:i', 'H:i:s', 'g:i a', 'g:i A', 'g a', 'g A' ] : [ 'H:i:s', 'H:i' ];

		foreach ( $date_formats as $df ) {
			foreach ( $time_formats as $tf ) {
				$fmt = $df . ' ' . $tf;
				$dt  = DateTime::createFromFormat( $fmt, $date_str . ' ' . $time_value, $tz );
				if ( $dt instanceof DateTime ) {
					return $dt->getTimestamp();
				}
			}
		}

		// Fallback to PHP parser
		try {
			$dt2 = new DateTime( $date_str . ' ' . $time_value, $tz );
			return $dt2->getTimestamp();
		} catch ( Exception $e ) {
			return null;
		}
	}
}



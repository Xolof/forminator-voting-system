<?php
/**
 * Forminator_Geo_Wrapper
 *
 * @package Forminator Votation System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


require_once __DIR__ . '/../../../forminator/library/class-geo.php';

/**
 * A wrapper class for Forminator_Geo,
 * making it possible to mock the static method get_user_ip().
 */
class Forminator_Geo_Wrapper {

	/**
	 * Get the user's IP address.
	 *
	 * @return string
	 */
	public function get_user_ip(): string {
		return Forminator_Geo::get_user_ip();
	}
}

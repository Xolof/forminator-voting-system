<?php
/**
 * ForminatorGeoWrapper
 *
 * @package Forminator Votation System
 */

namespace ForminatorVotingSystem\Wrapper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../../forminator/library/class-geo.php';

/**
 * A wrapper class for Forminator_Geo,
 * making it possible to mock the static method get_user_ip().
 */
class ForminatorGeoWrapper {

	/**
	 * Get the user's IP address.
	 *
	 * @return string
	 */
	public function get_user_ip(): string {
		return \Forminator_Geo::get_user_ip();
	}
}

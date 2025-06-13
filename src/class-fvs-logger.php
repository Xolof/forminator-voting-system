<?php
/**
 * Custom logger
 *
 * @package Forminator Voting System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FVS Custom Logger
 */
class Fvs_Logger {
	/**
	 * Write message to a log file.
	 *
	 * @param string $message
	 * @return void
	 */
	public static function log( string $message ): void {
		$path             = plugin_dir_path( __DIR__ );
		$message          = wp_json_encode( $message ) . "\n";
		$destination_file = $path . 'fvs.log';
		error_log( $message, 3, $destination_file ); // phpcs:ignore
	}
}

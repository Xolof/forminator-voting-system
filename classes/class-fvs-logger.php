<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Fvs_Logger {

	public static function log( string $message ): void {
		file_put_contents( __DIR__ . '/../fvs.log', wp_json_encode( $message ) . "\n", FILE_APPEND );
	}
}

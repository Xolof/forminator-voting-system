<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings_Processor {

	protected const SETTINGS_PAGE_URL = 'admin.php?page=render_votation_settings';

	public function process_settings(): void {
		$invalid_message = 'Invalid nonce specified.';

		if ( ! isset( $_POST['fvs_nonce'] ) ) {
			$this->fvs_wp_die( $invalid_message, self::SETTINGS_PAGE_URL );
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['fvs_nonce'] ) );

		if ( wp_verify_nonce( $nonce, 'fvs_nonce' ) ) {
			if (
			$this->process_blocked_ips() === false ||
			$this->process_alternatives() === false ||
			$this->process_multiple_votes_from_same_ip() === false
			) {
				$this->fvs_wp_die( 'Option update failed.', self::SETTINGS_PAGE_URL );
			}

			$this->set_flash_message( 'success', 'Settings saved!' );
			$this->redirect();
		}

		$this->fvs_wp_die( $invalid_message, self::SETTINGS_PAGE_URL );
	}

	protected function fvs_wp_die( string $message, string $back_link ): void {
		wp_die(
			__( esc_html( $message ), 'fvs' ),
			__( esc_html( 'Error' ), 'fvs' ),
			array(
				'response'  => 403,
				'back_link' => esc_html( $back_link ),
			)
		);
	}

	protected function process_blocked_ips(): mixed {
		$blocked_ips = $_POST['blocked_ips'] ?? array();
		if ( gettype( $blocked_ips ) !== 'string' ) {
			$this->fvs_wp_die( 'Invalid IP value submitted. Blocked IPs should be a string.', self::SETTINGS_PAGE_URL );
		}

		$blocked_ips = explode( ',', $blocked_ips );
		if ( '' !== $blocked_ips[0] ) {
			foreach ( $blocked_ips as $ip ) {
				if (
				! ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ||
				filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) )
				) {
					$this->set_flash_message(
						'error',
						'Ogiltigt värde för IP-adresser. Ange blockerade IP-adresser separerade med komma.'
					);
					$this->redirect();
					exit;
				}
			}
		}

		return $this->process_option( 'fvs_votation_blocked_ips', $blocked_ips );
	}

	protected function process_alternatives(): mixed {
		$votation_forminator_form_ids = isset( $_POST['alternatives'] ) ? array_keys( $_POST['alternatives'] ) : array();
		foreach ( $votation_forminator_form_ids as $form_id ) {
			if ( ! is_numeric( $form_id ) ) {
				$this->fvs_wp_die( 'Invalid form data.', $back_link );
			}
		}

		return $this->process_option( 'fvs_votation_forminator_form_ids', $votation_forminator_form_ids );
	}

	protected function process_multiple_votes_from_same_ip(): mixed {
		$fvs_allow_multiple_votes_from_same_ip = $_POST['fvs_allow_multiple_votes_from_same_ip'];
		if ( isset( $fvs_allow_multiple_votes_from_same_ip ) ) {
			if ( ! in_array( $fvs_allow_multiple_votes_from_same_ip, array( 'yes', 'no' ) ) ) {
				$this->fvs_wp_die( 'Option update failed.', self::SETTINGS_PAGE_URL );
			}
			return $this->process_option( 'fvs_allow_multiple_votes_from_same_ip', $fvs_allow_multiple_votes_from_same_ip );
		}
	}

	protected function process_option( string $option_name, array|string $post_data ): bool {
		$result = true;
		if ( ! get_option( $option_name ) ) {
			$result = add_option( $option_name, wp_json_encode( $post_data ), '', 'no' );
		}
		if ( json_decode( get_option( $option_name ) ) !== $post_data ) {
			$result = update_option( $option_name, wp_json_encode( $post_data ), '', 'no' );
		}
		return $result;
	}

	protected function set_flash_message( string $type, string $message ): void {
		set_transient(
			'fvs_flash_message',
			array(
				'type'    => $type,
				'message' => $message,
			),
			0
		);
	}

	protected function redirect(): void {
		wp_safe_redirect(
			esc_url_raw(
				admin_url(
					'admin.php?page=render_votation_settings'
				)
			)
		);
		exit;
	}
}

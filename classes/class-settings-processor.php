<?php
/**
 * Settings_Processor
 *
 * Processes settings for the votation.
 *
 * @package Forminator Votation System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings_Processor
 *
 * Processes settings for the votation.
 */
class Settings_Processor {

	protected const SETTINGS_PAGE_URL = 'admin.php?page=render_votation_settings';

	/**
	 * Process settings.
	 *
	 * @return void
	 */
	public function process_settings(): void {
		$invalid_message = esc_html__( 'Invalid nonce specified.', 'fvs' );

		if ( ! isset( $_POST['fvs_nonce'] ) ) {
			$this->fvs_wp_die( $invalid_message, self::SETTINGS_PAGE_URL );
		}

		$nonce = sanitize_text_field( wp_unslash( $_POST['fvs_nonce'] ) );

		if ( wp_verify_nonce( $nonce, 'fvs_nonce' ) ) {
			if (
			$this->process_blocked_ips() === false ||
			$this->process_form_ids() === false ||
			$this->process_multiple_votes_from_same_ip() === false
			) {
				$this->fvs_wp_die( esc_html__( 'Option update failed.', 'fvs' ), self::SETTINGS_PAGE_URL );
			}

			$this->set_flash_message( 'success', esc_html__( 'Settings saved!', 'fvs' ) );
			$this->redirect();
		}

		$this->fvs_wp_die( $invalid_message, self::SETTINGS_PAGE_URL );
	}

	/**
	 * Custom wp_die. Displaying an error message with a back link.
	 *
	 * @param string $message
	 * @param string $back_link
	 * @return void
	 */
	protected function fvs_wp_die( string $message, string $back_link ): void {
		wp_die(
			esc_html( $message ),
			esc_html__( 'Error', 'fvs' ),
			array(
				'response'  => 500,
				'back_link' => esc_html( $back_link ),
			)
		);
	}

	/**
	 * Process blocked IP addresses.
	 *
	 * @return mixed
	 */
	protected function process_blocked_ips(): mixed {
		if ( ! isset( $_POST['blocked_ips'] ) ) {
			$blocked_ips = array();
		} else {
			$blocked_ips = sanitize_text_field( wp_unslash( $_POST['blocked_ips'] ) );
		}

		if ( gettype( $blocked_ips ) !== 'string' ) {
			$this->fvs_wp_die( esc_html__( 'Invalid IP value submitted. Blocked IPs should be a string.', 'fvs' ), self::SETTINGS_PAGE_URL );
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
						esc_html__( 'Invalid value for IP-addresses. Enter blocked IP-addresses separated by comma.', 'fvs' )
					);
					$this->redirect();
					exit;
				}
			}
		}

		return $this->process_option( 'fvs_votation_blocked_ips', $blocked_ips );
	}

	/**
	 * Process ids of Forminator forms.
	 *
	 * @return mixed
	 */
	protected function process_form_ids(): mixed {
		if ( isset( $_POST['form_ids'] ) ) {
			$votation_forminator_form_ids = array_keys( $_POST['form_ids'] );
		} else {
			$votation_forminator_form_ids = array();
		}

		foreach ( $votation_forminator_form_ids as $form_id ) {
			if ( ! is_numeric( $form_id ) ) {
				$this->fvs_wp_die( esc_html__( 'Invalid form data.', 'fvs' ), $back_link );
			}
		}

		return $this->process_option( 'fvs_votation_forminator_form_ids', $votation_forminator_form_ids );
	}

	/**
	 * Process the option for allowing several votes from the same IP address.
	 *
	 * @return mixed
	 */
	protected function process_multiple_votes_from_same_ip(): mixed {
		if ( isset( $_POST['fvs_allow_multiple_votes_from_same_ip'] ) ) {
			$fvs_allow_multiple_votes_from_same_ip = sanitize_text_field( wp_unslash( $_POST['fvs_allow_multiple_votes_from_same_ip'] ) );

			if ( ! in_array( $fvs_allow_multiple_votes_from_same_ip, array( 'yes', 'no' ), true ) ) {
				$this->fvs_wp_die( esc_html__( 'Option update failed.', 'fvs' ), self::SETTINGS_PAGE_URL );
			}
			return $this->process_option( 'fvs_allow_multiple_votes_from_same_ip', $fvs_allow_multiple_votes_from_same_ip );
		}
	}

	/**
	 * Save the option if it has a new value, create it if it does not exist.
	 *
	 * @param string $option_name
	 * @param array|string $post_data
	 * @return boolean
	 */
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

	/**
	 * Set a flash message.
	 *
	 * @param string $type
	 * @param string $message
	 * @return void
	 */
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

	/**
	 * Redirect to the settings page.
	 *
	 * @return void
	 */
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

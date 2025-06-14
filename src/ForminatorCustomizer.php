<?php
/**
 * ForminatorCustomizer
 *
 * @package Forminator Voting System
 */

namespace ForminatorVotingSystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use ForminatorVotingSystem\Wrapper\ForminatorFormEntryModelWrapper;
use ForminatorVotingSystem\Wrapper\ForminatorGeoWrapper;

/**
 * ForminatorCustomizer
 *
 * Handles customization of Forminator form behaviour.
 */
class ForminatorCustomizer {

	protected ResultsFetcher $results_fetcher;
	protected ForminatorGeoWrapper $forminator_geo;
	protected ForminatorFormEntryModelWrapper $forminator_form_entry_model;

	const IP_BLOCKED_MESSAGE         = 'Your IP address has been blocked.';
	const ONLY_VOTE_ONE_TIME_MESSAGE = 'You have already voted for this alternative with this email address.';
	const IP_ALREADY_VOTED_MESSAGE   = 'Someone has already voted for this alternative with this IP address.';

	/**
	 * Constructor
	 *
	 * @param ResultsFetcher                  $results_fetcher
	 * @param ForminatorGeoWrapper            $forminator_geo
	 * @param ForminatorFormEntryModelWrapper $forminator_form_entry_model
	 */
	public function __construct(
		ResultsFetcher $results_fetcher,
		ForminatorGeoWrapper $forminator_geo,
		ForminatorFormEntryModelWrapper $forminator_form_entry_model
	) {
		$this->results_fetcher             = $results_fetcher;
		$this->forminator_geo              = $forminator_geo;
		$this->forminator_form_entry_model = $forminator_form_entry_model;
	}

	/**
	 * Add a custom error message to the form.
	 *
	 * @param array   $response
	 * @param integer $form_id
	 * @return array
	 */
	public function custom_error_message( array $response, int $form_id ): array {
		if ( ! in_array( intval( $form_id ), $this->get_forminator_form_ids(), true ) ) {
			return $response;
		}
		if ( ! $response['success'] && isset( $response['message'] ) ) {
			$response['message'] = '<p>' . esc_html__( 'Invalid form data:', 'fvs' ) . '</p>';

			$errors_string = '';

			foreach ( $response['errors'] as $error_array ) {
				foreach ( $error_array as $error ) {
					$sanitized_error = wp_kses_post( $error );

					$errors_string .= <<<EOD
						<li class="fvs-forminator-error">
							$sanitized_error
						</li>
					EOD;
				}
			}

			$response['message'] .= "<ul>$errors_string</ul>";
		}
		return $response;
	}

	/**
	 * If the users IP is blocked, add an error to the form.
	 *
	 * @param array   $submit_errors
	 * @param integer $form_id
	 * @return array $submit_errors
	 */
	public function submit_errors_ip_blocked( array $submit_errors, int $form_id ): array {
		if ( in_array( intval( $form_id ), $this->get_forminator_form_ids(), true ) ) {
			$user_ip = $this->forminator_geo->get_user_ip();
			if ( in_array( $user_ip, $this->get_blocked_ips(), true ) ) {
				// Put each error in an array due to how Forminator prints errors in a hidden list.
				$submit_errors[]['fvs-ip-blocked'] = esc_html__( self::IP_BLOCKED_MESSAGE, 'fvs' ); // phpcs:ignore
			}
		}
		return $submit_errors;
	}

	/**
	 * Check if email is missing or has already voted.
	 *
	 * @param array   $submit_errors
	 * @param integer $form_id
	 * @param array   $field_data
	 * @return array $submit_errors
	 */
	public function submit_errors_email( array $submit_errors, int $form_id, array $field_data ): array {
		if ( in_array( intval( $form_id ), $this->get_forminator_form_ids(), true ) ) {
			if ( 0 === count( $field_data ) ) {
				// Put each error in an array due to how Forminator prints errors in a hidden list.
				$submit_errors[]['fvs-missing-email'] = esc_html__( 'Email address is missing.', 'fvs' );
				return $submit_errors;
			}
			$email = $field_data[0]['value'];
			if ( $this->email_has_already_voted( $email, $form_id ) ) {
				// Put each error in an array due to how Forminator prints errors in a hidden list.
				$submit_errors[]['fvs-email-already-voted'] = esc_html__( self::ONLY_VOTE_ONE_TIME_MESSAGE, 'fvs' ); // phpcs:ignore
			}
		}
		return $submit_errors;
	}

	/**
	 * Check if the IP address has already voted.
	 *
	 * @param array   $submit_errors
	 * @param integer $form_id
	 * @return array submit_errors
	 */
	public function submit_errors_ip_already_voted( array $submit_errors, int $form_id ): array {
		if ( $this->multiple_votes_from_same_ip_is_allowed() === 'yes' ) {
			return $submit_errors;
		}

		if ( ! in_array( intval( $form_id ), $this->get_forminator_form_ids(), true ) ) {
			return $submit_errors;
		}

		if ( $this->ip_already_voted( $form_id ) ) {
			// Put each error in an array due to how Forminator prints errors in a hidden list.
			$submit_errors[]['fvs-ip-already-voted'] = esc_html__( self::IP_ALREADY_VOTED_MESSAGE, 'fvs' ); // phpcs:ignore
		}
		return $submit_errors;
	}

	/**
	 * Check if an email has already voted.
	 *
	 * @param string  $email
	 * @param integer $form_id
	 * @return boolean
	 */
	protected function email_has_already_voted( string $email, int $form_id ): bool {
		global $wpdb;
		$frmt_form_entry      = $this->results_fetcher->get_table_name_with_prefix( 'frmt_form_entry' );
		$frmt_form_entry_meta = $this->results_fetcher->get_table_name_with_prefix( 'frmt_form_entry_meta' );

		$email_already_voted_query = <<<SQL
		SELECT
		EXISTS(
			SELECT meta_value
			FROM %i
			LEFT JOIN %i
				USING(entry_id)
				WHERE meta_key="email-1"
				AND form_id = %d
				AND meta_value="%s"
		) as email_already_voted;
		SQL;

		$result = $wpdb->get_results( // phpcs:ignore
			$wpdb->prepare(
				$email_already_voted_query, // phpcs:ignore
				array( $frmt_form_entry, $frmt_form_entry_meta, $form_id, $email )
			)
		);
		if ( 1 === (int) $result[0]->email_already_voted ) {
			return true;
		}
		return false;
	}

	/**
	 * Check if an IP address has already voted.
	 *
	 * @param integer $form_id
	 * @return boolean
	 */
	protected function ip_already_voted( int $form_id ): bool {
		$user_ip = $this->forminator_geo->get_user_ip();
		if ( ! empty( $user_ip ) ) {
			$last_entry = $this->forminator_form_entry_model->get_last_entry_by_ip_and_form( $form_id, $user_ip );
			if ( ! empty( $last_entry ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get the ids of the forms used in the votation.
	 *
	 * @return array
	 */
	protected function get_forminator_form_ids(): array {
		return json_decode( get_option( 'fvs_votation_forminator_form_ids' ) );
	}

	/**
	 * Get blocked IP addresses.
	 *
	 * @return array
	 */
	protected function get_blocked_ips(): array {
		return json_decode( get_option( 'fvs_votation_blocked_ips' ) ) ?? array();
	}

	/**
	 * Check is several votes from the same IP address are allowed.
	 *
	 * @return array
	 */
	protected function multiple_votes_from_same_ip_is_allowed(): string {
		return json_decode( get_option( 'fvs_allow_multiple_votes_from_same_ip' ) ) ?? array();
	}
}

<?php
/**
 * Forminator_Customizer
 * @package Forminator Voting System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Forminator_Customizer
 *
 * Handles customization of Forminator form behaviour.
 */
class Forminator_Customizer {

	protected Results_Fetcher $results_fetcher;

	const IP_BLOCKED_MESSAGE         = 'Din IP-adress har blockerats.';
	const ONLY_VOTE_ONE_TIME_MESSAGE = 'Du har redan röstat på det här alternativet med den här epostadressen.';
	const IP_ALREADY_VOTED_MESSAGE   = 'Någon har redan röstat på det här alternativet med den här IP-adressen.';

	public function __construct( Results_Fetcher $results_fetcher ) {
		$this->results_fetcher = $results_fetcher;
	}

	public function custom_error_message( array $response, int $form_id ): array {
		if ( in_array( intval( $form_id ), FVS_VOTATION_FORM_IDS, true ) ) {
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
		}
		return $response;
	}

	public function submit_errors_ip_blocked( array $submit_errors, int $form_id ): array {
		if ( in_array( intval( $form_id ), FVS_VOTATION_FORM_IDS, true ) ) {
			$user_ip = Forminator_Geo::get_user_ip();
			if ( in_array( $user_ip, FVS_IP_BLOCK_LIST, true ) ) {
				// Put each error in an array due to how Forminator prints errors in a hidden list.
				$submit_errors[]['fvs-ip-blocked'] = self::IP_BLOCKED_MESSAGE;
			}
		}
		return $submit_errors;
	}

	public function submit_errors_email( array $submit_errors, int $form_id, array $field_data ): array {
		if ( in_array( intval( $form_id ), FVS_VOTATION_FORM_IDS, true ) ) {
			if ( 0 === count( $field_data ) ) {
				// Put each error in an array due to how Forminator prints errors in a hidden list.
				$submit_errors[]['fvs-missing-email'] = esc_html__( 'Email address is missing.', 'fvs' );
				return $submit_errors;
			}
			$email = $field_data[0]['value'];
			if ( $this->email_has_already_voted( $email, $form_id ) ) {
				// Put each error in an array due to how Forminator prints errors in a hidden list.
				$submit_errors[]['fvs-email-already-voted'] = self::ONLY_VOTE_ONE_TIME_MESSAGE;
			}
		}
		return $submit_errors;
	}

	public function submit_errors_ip_already_voted( array $submit_errors, int $form_id ): array {
		if ( FVS_ALLOW_MULTIPLE_VOTES_FROM_SAME_IP === 'yes' ) {
			return $submit_errors;
		}

		if ( ! in_array( intval( $form_id ), FVS_VOTATION_FORM_IDS, true ) ) {
			return $submit_errors;
		}

		if ( $this->ip_already_voted( $form_id ) ) {
			// Put each error in an array due to how Forminator prints errors in a hidden list.
			$submit_errors[]['fvs-ip-already-voted'] = self::IP_ALREADY_VOTED_MESSAGE;
		}
		return $submit_errors;
	}

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

		$result = $wpdb->get_results(
			$wpdb->prepare(
				$email_already_voted_query,
				array( $frmt_form_entry, $frmt_form_entry_meta, $form_id, $email )
			)
		);
		if ( 1 === (int) $result[0]->email_already_voted ) {
			return true;
		}
		return false;
	}

	protected function ip_already_voted( int $form_id ): bool {
		$user_ip = Forminator_Geo::get_user_ip();
		if ( ! empty( $user_ip ) ) {
			$last_entry = Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form( $form_id, $user_ip );
			if ( ! empty( $last_entry ) ) {
				return true;
			}
		}
		return false;
	}
}

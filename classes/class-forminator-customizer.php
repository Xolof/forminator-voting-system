<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Forminator_Customizer {

	protected Results_Fetcher $results_fetcher;

	public function __construct( Results_Fetcher $results_fetcher ) {
		$this->results_fetcher = $results_fetcher;
	}

	public function forminator_submit_errors_block( array $submit_errors, int $form_id ): array {
		if ( in_array( intval( $form_id ), VOTATION_FORM_IDS, true ) ) {
			$user_ip = Forminator_Geo::get_user_ip();
			if ( in_array( $user_ip, IP_BLOCK_LIST ) ) {
				$submit_errors[]        = IP_BLOCKED_MESSAGE;
				$_SESSION['IP_BLOCKED'] = true;
			}
		}
		return $submit_errors;
	}

	public function forminator_invalid_form_message_block( string $invalid_form_message, int $form_id ): string {
		if ( in_array( intval( $form_id ), VOTATION_FORM_IDS, true ) ) {
			if ( isset( $_SESSION['IP_BLOCKED'] ) ) {
				return IP_BLOCKED_MESSAGE;
			}
		}
		return $invalid_form_message;
	}

	public function forminator_submit_errors_email( array $submit_errors, int $form_id, array $field_data ): array {
		if ( in_array( intval( $form_id ), VOTATION_FORM_IDS, true ) ) {
			$email = $field_data[0]['value'];
			if ( $this->email_has_already_voted( $email, $form_id ) ) {
				$submit_errors[]                 = ONLY_VOTE_ONE_TIME_MESSAGE;
				$_SESSION['EMAIL_ALREADY_VOTED'] = true;
			}
		}
		return $submit_errors;
	}

	public function forminator_invalid_form_message_email( string $invalid_form_message, int $form_id ): string {
		if ( in_array( intval( $form_id ), VOTATION_FORM_IDS, true ) ) {
			if ( isset( $_SESSION['EMAIL_ALREADY_VOTED'] ) ) {
				$invalid_form_message = ONLY_VOTE_ONE_TIME_MESSAGE;
			}
		}
		return $invalid_form_message;
	}

	public function forminator_submit_errors_same_ip( array $submit_errors, int $form_id ): array {
		if ( ! in_array( intval( $form_id ), VOTATION_FORM_IDS, true ) ) {
			return $submit_errors;
		}
		if ( $this->ip_already_voted( $form_id ) ) {
			$submit_errors[]['submit']    = ONLY_VOTE_ONE_TIME_PER_IP_MESSAGE;
			$_SESSION['IP_ALREADY_VOTED'] = true;
		}
		return $submit_errors;
	}

	public function forminator_invalid_form_message_same_ip( string $invalid_form_message, int $form_id ): string {
		if ( ! in_array( intval( $form_id ), VOTATION_FORM_IDS, true ) ) {
			return $invalid_form_message;
		}
		if ( isset( $_SESSION['IP_ALREADY_VOTED'] ) ) {
			return ONLY_VOTE_ONE_TIME_PER_IP_MESSAGE;
		}
		return $invalid_form_message;
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
		$result                    = $wpdb->get_results(
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

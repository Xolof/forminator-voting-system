<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Results_Fetcher {

	public function get_votation_results() {
		global $wpdb;
		$postmeta             = $this->get_table_name_with_prefix( 'postmeta' );
		$frmt_form_entry      = $this->get_table_name_with_prefix( 'frmt_form_entry' );
		$frmt_form_entry_meta = $this->get_table_name_with_prefix( 'frmt_form_entry_meta' );

		$votation_form_id_placeholders = $this->get_votation_form_id_placeholders();
		$votation_result_query         = <<<EOD
		SELECT
			form_id, COUNT(*) as num_votes, %i.meta_value as alternative
			FROM %i
		LEFT JOIN %i
			USING(entry_id)
		LEFT JOIN %i
			ON post_id=form_id 
		WHERE
			form_id IN ($votation_form_id_placeholders)
			AND %i.meta_key="email-1"
		GROUP BY form_id
		;
	EOD;
		$results                       = $wpdb->get_results(
			$wpdb->prepare(
				$votation_result_query,
				array_merge(
					array(
						$postmeta,
						$frmt_form_entry,
						$frmt_form_entry_meta,
						$postmeta,
					),
					VOTATION_FORM_IDS,
					array( $frmt_form_entry_meta )
				)
			)
		);
		return $results;
	}

	public function get_votes_per_ip_results() {
		global $wpdb;
		$postmeta             = $this->get_table_name_with_prefix( 'postmeta' );
		$frmt_form_entry      = $this->get_table_name_with_prefix( 'frmt_form_entry' );
		$frmt_form_entry_meta = $this->get_table_name_with_prefix( 'frmt_form_entry_meta' );

		$votation_form_id_placeholders = $this->get_votation_form_id_placeholders();
		$votes_per_ip_query            = <<<EOD
		SELECT
			%i.meta_value as IP_address,
			COUNT(*) as num_votes
			FROM %i
		LEFT JOIN %i
			USING(entry_id)
		WHERE
			form_id IN ($votation_form_id_placeholders)
			AND %i.meta_key="_forminator_user_ip"
		GROUP BY IP_address;
		EOD;
		return $wpdb->get_results(
			$wpdb->prepare(
				$votes_per_ip_query,
				array_merge(
					array(
						$frmt_form_entry_meta,
						$frmt_form_entry,
						$frmt_form_entry_meta,
					),
					VOTATION_FORM_IDS,
					array( $frmt_form_entry_meta )
				)
			)
		);
	}

	protected function get_votation_form_id_placeholders() {
		$votation_form_id_placeholders = '';
		foreach ( VOTATION_FORM_IDS as $id ) {
			$votation_form_id_placeholders .= '%d,';
		}
		return rtrim( $votation_form_id_placeholders, ',' );
	}

	public function get_table_name_with_prefix( $tablename_without_prefix ) {
		global $wpdb;
		$prefix             = $wpdb->prefix;
		$prefixed_tablename = $prefix . $tablename_without_prefix;

		$table_exists = $wpdb->get_results(
			$wpdb->prepare(
				'SHOW TABLES LIKE %s',
				$prefixed_tablename
			)
		);

		if ( count( $table_exists ) ) {
			return $prefixed_tablename;
		}

		throw new Exception( esc_html( "Table $prefixed_tablename not found." ) );
	}
}

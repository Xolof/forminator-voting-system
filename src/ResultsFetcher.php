<?php
/**
 * Class ResultsFetcher
 *
 * Fetches votation results from the database.
 *
 * @package Forminator Voting System
 */

namespace ForminatorVotingSystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ResultsFetcher
 *
 * Fetches votation results from the database.
 */
class ResultsFetcher {

	/**
	 * Get the results of the votation.
	 *
	 * Disable WordPress phpcs rule about placeholders.
	 * We are using placeholders which we generate dynamically.
	 * phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
	 *
	 * @return array
	 */
	public function get_votation_results(): array {
		global $wpdb;
		$postmeta             = $this->get_table_name_with_prefix( 'postmeta' );
		$frmt_form_entry      = $this->get_table_name_with_prefix( 'frmt_form_entry' );
		$frmt_form_entry_meta = $this->get_table_name_with_prefix( 'frmt_form_entry_meta' );

		$votation_form_id_placeholders = $this->get_votation_form_id_placeholders();
		$this->get_forminator_form_ids();
		$votation_result_query = <<<EOD
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
		GROUP BY form_id;
	EOD;

		$statement = $wpdb->prepare(
			$votation_result_query,
			array_merge(
				array(
					$postmeta,
					$frmt_form_entry,
					$frmt_form_entry_meta,
					$postmeta,
				),
				$this->get_forminator_form_ids(),
				array( $frmt_form_entry_meta )
			)
		);

		$results = $wpdb->get_results( $statement ); // phpcs:ignore
		return $results;
	}

	/**
	 * Get number of votes per IP address
	 *
	 * @return array
	 */
	public function get_votes_per_ip_results(): array {
		global $wpdb;
		$postmeta             = $this->get_table_name_with_prefix( 'postmeta' );
		$frmt_form_entry      = $this->get_table_name_with_prefix( 'frmt_form_entry' );
		$frmt_form_entry_meta = $this->get_table_name_with_prefix( 'frmt_form_entry_meta' );

		$votation_form_id_placeholders = $this->get_votation_form_id_placeholders();
		$votes_per_ip_query            = <<<EOD
		SELECT
			%i.meta_value as ip_address,
			COUNT(*) as num_votes
			FROM %i
		LEFT JOIN %i
			USING(entry_id)
		WHERE
			form_id IN ($votation_form_id_placeholders)
			AND %i.meta_key="_forminator_user_ip"
		GROUP BY ip_address;
		EOD;
		$statement                     = $wpdb->prepare(
			$votes_per_ip_query,
			array_merge(
				array(
					$frmt_form_entry_meta,
					$frmt_form_entry,
					$frmt_form_entry_meta,
				),
				$this->get_forminator_form_ids(),
				array( $frmt_form_entry_meta )
			)
		);
		return $wpdb->get_results($statement); // phpcs:ignore
	}

	/**
	 * Generate placeholders for each votation form to be used in an SQL-query.
	 *
	 * @return string
	 */
	protected function get_votation_form_id_placeholders(): string {
		$votation_form_id_placeholders = '';
		foreach ( $this->get_forminator_form_ids() as $id ) {
			$votation_form_id_placeholders .= '%d,';
		}
		return rtrim( $votation_form_id_placeholders, ',' );
	}

	/**
	 * Get the tablename prepended with the database prefix. This is
	 * needed because WordPress prefixes tables so that it is possible
	 * to have multiple instances of WordPress in the same database.
	 *
	 * @param string $tablename_without_prefix The tablename without prefix.
	 * @return string
	 *
	 * @throws Exception Throws Exception if the table does not exist.
	 */
	public function get_table_name_with_prefix( string $tablename_without_prefix ): string {
		global $wpdb;
		$prefix             = $wpdb->prefix;
		$prefixed_tablename = $prefix . $tablename_without_prefix;

		$table_exists = $wpdb->get_results( // phpcs:ignore
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

	/**
	 * Get the ids of the forms used in the votation.
	 *
	 * @return array
	 */
	protected function get_forminator_form_ids(): array {
		return json_decode( get_option( 'fvs_votation_forminator_form_ids' ) );
	}
}

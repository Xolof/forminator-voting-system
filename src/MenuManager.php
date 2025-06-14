<?php
/**
 * MenuManager
 *
 * @package Forminator Voting System
 */

namespace ForminatorVotingSystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Menu Manager
 */
class MenuManager {

	protected ResultsFetcher $results_fetcher;

	/**
	 * Constructor
	 *
	 * @param ResultsFetcher $results_fetcher
	 */
	public function __construct( ResultsFetcher $results_fetcher ) {
		$this->results_fetcher = $results_fetcher;
	}

	/**
	 * Add menu pages.
	 *
	 * @return void
	 */
	public function add_menu_pages(): void {
		add_menu_page(
			esc_html__( 'Forminator Voting System', 'fvs' ),
			esc_html__( 'Forminator Voting System', 'fvs' ),
			'manage_options',
			'fvs',
			array( $this, 'render_votation_results' ),
			'dashicons-book',
			3
		);
		add_submenu_page(
			'fvs',
			esc_html__( 'Results', 'fvs' ),
			esc_html__( 'Results', 'fvs' ),
			'manage_options',
			'render_votation_results',
			array( $this, 'render_votation_results' )
		);
		add_submenu_page(
			'fvs',
			esc_html__( 'Settings', 'fvs' ),
			esc_html__( 'Settings', 'fvs' ),
			'manage_options',
			'render_votation_settings',
			array( $this, 'render_votation_settings' )
		);
		add_submenu_page(
			'fvs',
			esc_html__( 'Manual', 'fvs' ),
			esc_html__( 'Manual', 'fvs' ),
			'manage_options',
			'render_votation_manual',
			array( $this, 'render_votation_manual' )
		);
		remove_submenu_page( 'fvs', 'fvs' );
	}

	/**
	 * Get the votation results and render in template.
	 *
	 * @return void
	 */
	public function render_votation_results() {
		if ( ! FVS_VOTATION_FORM_IDS ) {
			require_once __DIR__ . '/../templates/results.php';
			return;
		}
		$fvs_votation_results_db     = $this->results_fetcher->get_votation_results();
		$fvs_votes_per_ip_results_db = $this->results_fetcher->get_votes_per_ip_results();
		require_once __DIR__ . '/../templates/results.php';
	}

	/**
	 * Render the manual page.
	 *
	 * @return void
	 */
	public function render_votation_manual() {
		require_once __DIR__ . '/../templates/manual.php';
	}

	/**
	 * Get the Forminator forms and render the settings page.
	 *
	 * @return void
	 */
	public function render_votation_settings() {

		$fvs_votation_forminator_forms = \Forminator_API::get_forms();

		$fvs_allow_multiple_votes_from_same_ip = json_decode( get_option( 'fvs_allow_multiple_votes_from_same_ip' ) ) ?? 'yes';
		$fvs_ip_block_list                     = json_decode( get_option( 'fvs_votation_blocked_ips' ) ) ?? array();
		$fvs_existing_votation_form_ids        = json_decode( get_option( 'fvs_votation_forminator_form_ids' ) ) ?? array();

		require_once __DIR__ . '/../templates/settings.php';
	}
}

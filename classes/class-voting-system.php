<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * ForminatorVotingSystem
 *
 * The main class for the plugin.
 */
class Voting_System {

	protected Settings_Processor $settings_processor;
	protected Menu_Manager $menu_manager;
	protected Forminator_Customizer $forminator_customizer;

	public function __construct(
		Settings_Processor $settings_processor,
		Menu_Manager $menu_manager,
		Forminator_Customizer $forminator_customizer
	) {
		$this->settings_processor    = $settings_processor;
		$this->menu_manager          = $menu_manager;
		$this->forminator_customizer = $forminator_customizer;
	}

	public function init(): void {
		define( 'ALLOW_MULTIPLE_VOTES_FROM_SAME_IP', json_decode( get_option( 'allow_multiple_votes_from_same_ip' ) ) ?? 'yes' );
		define( 'IP_BLOCK_LIST', json_decode( get_option( 'fvs_votation_blocked_ips' ) ) ?? array() );
		define( 'VOTATION_FORM_IDS', json_decode( get_option( 'fvs_votation_forminator_form_ids' ) ) ?? array() );
		define( 'IP_BLOCKED_MESSAGE', 'Din IP-adress har blockerats.' );
		define( 'ONLY_VOTE_ONE_TIME_MESSAGE', 'Du har redan röstat på det här alternativet med den här epostadressen.' );
		define( 'ONLY_VOTE_ONE_TIME_PER_IP_MESSAGE', 'Någon har redan röstat på det här alternativet med den här IP-adressen.' );

		$this->initiate_session();
		$this->add_menu_pages();
		$this->process_settings();
		$this->set_admin_notices();
		$this->set_forminator_hooks();
		$this->add_styles();
	}

	protected function set_forminator_hooks(): void {
		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'forminator_submit_errors_block' ), 51, 3 );
		add_filter( 'forminator_custom_form_invalid_form_message', array( $this->forminator_customizer, 'forminator_invalid_form_message_block' ), 50, 3 );

		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'forminator_submit_errors_email' ), 31, 3 );
		add_filter( 'forminator_custom_form_invalid_form_message', array( $this->forminator_customizer, 'forminator_invalid_form_message_email' ), 30, 3 );

		if ( ALLOW_MULTIPLE_VOTES_FROM_SAME_IP === 'no' ) {
			add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'forminator_submit_errors_same_ip' ), 41, 3 );
			add_filter( 'forminator_custom_form_invalid_form_message', array( $this->forminator_customizer, 'forminator_invalid_form_message_same_ip' ), 40, 2 );
		}
	}

	protected function process_settings(): void {
		add_action( 'admin_post_fvs_form_response', array( $this->settings_processor, 'process_settings' ) );
	}

	protected function add_menu_pages(): void {
		add_action( 'admin_menu', array( $this->menu_manager, 'add_menu_page' ) );
	}

	protected function initiate_session(): void {
		add_action( 'init', array( $this, 'fvs_session_init' ) );
	}

	public function fvs_session_init(): void {
		if ( session_status() === PHP_SESSION_NONE ) {
			session_start();
		}
		session_write_close();
	}

	protected function set_admin_notices(): void {
		add_action( 'admin_notices', array( $this, 'print_plugin_admin_notices' ) );
	}

	public function print_plugin_admin_notices(): void {
		if ( $flash = get_transient( 'fvs_flash_message' ) ) {
			printf(
				'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
				esc_attr( $flash['type'] ),
				esc_html( $flash['message'] )
			);
			delete_transient( 'fvs_flash_message' );
		}
	}

	protected function add_styles(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_style(
					'fvs-plugin-styles',
					plugins_url( '../assets/css/fvs-styles.css', __FILE__ ),
					array(),
					'1.0',
					'all'
				);
			}
		);
	}
}

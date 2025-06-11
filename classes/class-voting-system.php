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
		define( 'FVS_ALLOW_MULTIPLE_VOTES_FROM_SAME_IP', json_decode( get_option( 'fvs_allow_multiple_votes_from_same_ip' ) ) ?? 'yes' );
		define( 'FVS_IP_BLOCK_LIST', json_decode( get_option( 'fvs_votation_blocked_ips' ) ) ?? array() );
		define( 'FVS_VOTATION_FORM_IDS', json_decode( get_option( 'fvs_votation_forminator_form_ids' ) ) ?? array() );

		$this->initiate_session();
		$this->add_menu_pages();
		$this->process_settings();
		$this->set_admin_notices();
		$this->set_forminator_hooks();
		$this->add_styles();
		$this->load_textdomain();
		$this->register_deactivation_hook();
	}

	protected function set_forminator_hooks(): void {
		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'submit_errors_ip_blocked' ), 9, 3 );
		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'submit_errors_email' ), 10, 3 );
		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'submit_errors_ip_already_voted' ), 11, 3 );

		add_filter( 'forminator_form_submit_response', array( $this->forminator_customizer, 'custom_error_message' ), 20, 2 );
		add_filter( 'forminator_form_ajax_submit_response', array( $this->forminator_customizer, 'custom_error_message' ), 20, 2 );
	}

	protected function process_settings(): void {
		add_action( 'admin_post_fvs_form_response', array( $this->settings_processor, 'process_settings' ) );
	}

	protected function add_menu_pages(): void {
		add_action( 'admin_menu', array( $this->menu_manager, 'add_menu_pages' ) );
	}

	protected function initiate_session(): void {
		add_action(
			'init',
			function () {
				if ( session_status() === PHP_SESSION_NONE ) {
					session_start();
				}
				session_write_close();
			}
		);
	}

	protected function set_admin_notices(): void {
		add_action(
			'admin_notices',
			function () {
				$flash = get_transient( 'fvs_flash_message' );
				if ( $flash ) {
					printf(
						'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
						esc_attr( $flash['type'] ),
						esc_html( $flash['message'] )
					);
					delete_transient( 'fvs_flash_message' );
				}
			}
		);
	}

	protected function add_styles(): void {
		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_style(
					'fvs-plugin-admin-styles',
					plugins_url( '../assets/css/fvs-admin-styles.css', __FILE__ ),
					array(),
					'1.0',
					'all'
				);
			}
		);
	}

	protected function load_textdomain() {
		add_action(
			'plugins_loaded',
			function () {

				$path   = plugin_dir_path( __DIR__ ) . 'languages';
				$loaded = load_plugin_textdomain( 'fvs', false, plugin_basename( $path ) );
				Fvs_logger::log( 'Text domain "fvs" loaded: ' . ( $loaded ? 'Yes' : 'No' ) );
				Fvs_logger::log( 'Path: ' . $path );
				Fvs_logger::log( 'Locale: ' . get_locale() );
			},
			5
		);
	}

	protected function register_deactivation_hook(): void {
		$main_plugin_file = plugin_dir_path( __DIR__ ) . 'forminator-voting-system.php';

		register_deactivation_hook(
			plugin_basename( $main_plugin_file ),
			function () {
				$options = array(
					'fvs_allow_multiple_votes_from_same_ip',
					'fvs_votation_blocked_ips',
					'fvs_votation_forminator_form_ids',
					'fvs_settings',
					'fvs_db_version',
				);

				foreach ( $options as $option ) {
					if ( get_option( $option ) ) {
						delete_option( $option );
					}
				}
			}
		);
	}
}

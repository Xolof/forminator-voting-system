<?php
/**
 * Main class.
 *
 * @package Forminator Voting System
 */

namespace ForminatorVotingSystem;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * VotingSystem
 *
 * The main class for the plugin.
 */
class VotingSystem {

	protected SettingsProcessor $settings_processor;
	protected MenuManager $menu_manager;
	protected ForminatorCustomizer $forminator_customizer;

	/**
	 * Constructor
	 *
	 * @param SettingsProcessor    $settings_processor
	 * @param MenuManager          $menu_manager
	 * @param ForminatorCustomizer $forminator_customizer
	 */
	public function __construct(
		SettingsProcessor $settings_processor,
		MenuManager $menu_manager,
		ForminatorCustomizer $forminator_customizer
	) {
		$this->settings_processor    = $settings_processor;
		$this->menu_manager          = $menu_manager;
		$this->forminator_customizer = $forminator_customizer;
	}

	/**
	 * Start up the plugin.
	 *
	 * @return void
	 */
	public function init(): void {
		$this->load_textdomain();

		if ( ! $this->check_forminator_dependency() ) {
			$this->show_message_forminator_missing();
			return;
		}

		$this->add_menu_pages();
		$this->process_settings();
		$this->set_admin_notices();
		$this->set_forminator_hooks();
		$this->add_styles();
		$this->register_deactivation_hook();
	}

	/**
	 * Customize Forminator behavior.
	 *
	 * @return void
	 */
	protected function set_forminator_hooks(): void {
		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'submit_errors_ip_blocked' ), 9, 3 );
		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'submit_errors_email' ), 10, 3 );
		add_filter( 'forminator_custom_form_submit_errors', array( $this->forminator_customizer, 'submit_errors_ip_already_voted' ), 11, 3 );

		add_filter( 'forminator_form_submit_response', array( $this->forminator_customizer, 'custom_error_message' ), 20, 2 );
		add_filter( 'forminator_form_ajax_submit_response', array( $this->forminator_customizer, 'custom_error_message' ), 20, 2 );
	}

	/**
	 * Process the settings from the admin page.
	 *
	 * @return void
	 */
	protected function process_settings(): void {
		add_action( 'admin_post_fvs_form_response', array( $this->settings_processor, 'process_settings' ) );
	}

	/**
	 * Add menu pages in admin interface.
	 *
	 * @return void
	 */
	protected function add_menu_pages(): void {
		add_action( 'admin_menu', array( $this->menu_manager, 'add_menu_pages' ) );
	}

	/**
	 * Define a custom flash message.
	 *
	 * @return void
	 */
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

	/**
	 * Add custom stylesheet.
	 *
	 * @return void
	 */
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

	/**
	 * Load the plugin's textdomain which is needed for translations.
	 *
	 * @return void
	 */
	protected function load_textdomain(): void {
		add_action(
			'plugins_loaded',
			function () {
				$path   = plugin_dir_path( __DIR__ ) . 'languages';
				$loaded = load_plugin_textdomain( 'fvs', false, plugin_basename( $path ) );
			},
			5
		);
	}

	/**
	 * Check if Forminator plugin is active.
	 *
	 * If we are in the PhpUnit test environment we skip the check
	 * by returning 'true', because the Forminator class will not be found
	 * in the test environment.
	 */
	protected function check_forminator_dependency() {
		if ( defined( 'PHPUNIT_COMPOSER_INSTALL' ) ) {
			return true;
		}

		return is_plugin_active( 'forminator/forminator.php' );
	}

	/**
	 * Show error message about missing Forminator dependency.
	 *
	 * @return void
	 */
	protected function show_message_forminator_missing(): void {
		add_action(
			'admin_notices',
			function () {
				echo '<div class="notice notice-error"><p>'
				. esc_html__( 'Forminator Voting System requires the Forminator plugin in order to work.', 'fvs' )
				. '</p></div>';
			}
		);
	}

	/**
	 * Register actions to execute on deactivation of the plugin.
	 *
	 * @return void
	 */
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

<?php
/**
 * Plugin Name:     Forminator Voting System
 * Plugin URI:      https://github.com/xolof/forminator-voting-system
 * Description:     A voting system using Forminator forms
 * Author:          Olof Johansson
 * Author URI:      https://oljo.online
 * Text Domain:     fvs
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Forminator_voting_system
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/classes/class-voting-system.php';
require_once __DIR__ . '/classes/class-forminator-customizer.php';
require_once __DIR__ . '/classes/class-settings-processor.php';
require_once __DIR__ . '/classes/class-results-fetcher.php';
require_once __DIR__ . '/classes/class-menu-manager.php';
require_once __DIR__ . '/classes/class-fvs-logger.php';
require_once __DIR__ . '/classes/wrappers/forminator-form-entry-model-wrapper.php';
require_once __DIR__ . '/classes/wrappers/forminator-geo-wrapper.php';
require_once __DIR__ . '/debug/functions.php';

$forminator_geo_wrapper = new Forminator_Geo_Wrapper();
$forminator_form_entry_model_wrapper = new Forminator_Form_Entry_Model_Wrapper();

$results_fetcher       = new Results_Fetcher();
$settings_processor    = new Settings_Processor();
$menu_manager          = new Menu_Manager( $results_fetcher );
$forminator_customizer = new Forminator_Customizer( $results_fetcher, $forminator_geo_wrapper, $forminator_form_entry_model_wrapper );

$voting_system = new Voting_System(
	$settings_processor,
	$menu_manager,
	$forminator_customizer
);

$voting_system->init();

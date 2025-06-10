<?php
/**
 * Plugin Name:     Forminator Voting System
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     A voting system using Forminator forms
 * Author:          Olof Johansson
 * Author URI:      YOUR SITE HERE
 * Text Domain:     forminator_voting_system
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
require_once __DIR__ . '/helpers/functions.php';

// Fvs_Logger::log("testing the logger.");

$results_fetcher = new Results_Fetcher();

$settings_processor    = new Settings_Processor();
$menu_manager          = new Menu_Manager($results_fetcher);
$forminator_customizer = new Forminator_Customizer($results_fetcher);

$voting_system = new Voting_System(
	$settings_processor,
	$menu_manager,
	$forminator_customizer
);

$voting_system->init();


// register_deactivation_hook(
// 	__FILE__,
// 	function() {
// 		$options = [
// 			'fvs_allow_multiple_votes_from_same_ip',
// 			'fvs_votation_blocked_ips',
// 			'fvs_votation_forminator_form_ids',
// 			'fvs_settings',
// 			'fvs_db_version'
// 		];

// 		foreach($options as $option) {
// 			if ( get_option( $option ) ) {
// 				delete_option( $option );
// 			}
// 		}
// 	}
// );

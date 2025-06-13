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

require_once __DIR__ . '/autoload.php';

use ForminatorVotingSystem\VotingSystem;
use ForminatorVotingSystem\ForminatorCustomizer;
use ForminatorVotingSystem\SettingsProcessor;
use ForminatorVotingSystem\ResultsFetcher;
use ForminatorVotingSystem\MenuManager;
use ForminatorVotingSystem\FvsLogger;
use ForminatorVotingSystem\Wrapper\ForminatorFormEntryModelWrapper;
use ForminatorVotingSystem\Wrapper\ForminatorGeoWrapper;

require_once __DIR__ . '/debug/functions.php';

$forminator_geo_wrapper              = new ForminatorGeoWrapper();
$forminator_form_entry_model_wrapper = new ForminatorFormEntryModelWrapper();

$results_fetcher       = new ResultsFetcher();
$settings_processor    = new SettingsProcessor();
$menu_manager          = new MenuManager( $results_fetcher );
$forminator_customizer = new ForminatorCustomizer( $results_fetcher, $forminator_geo_wrapper, $forminator_form_entry_model_wrapper );

$voting_system = new VotingSystem(
	$settings_processor,
	$menu_manager,
	$forminator_customizer
);

$voting_system->init();

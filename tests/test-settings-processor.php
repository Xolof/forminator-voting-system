<?php
/**
 * Class SettingsProcessorTest
 *
 * @package Forminator_voting_system
 */

/**
 * Settings Processor test.
 */
class SettingsProcessorTest extends WP_UnitTestCase {

	protected $user_id;

	/**
	 * Set user and 'current screen'.
	 * 
	 * Mock wp_redirect_status to avoid exiting the tests on redirect.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->user_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->user_id );
		set_current_screen( 'admin' );

		add_filter( 'wp_redirect_status', function () {
			return 200;
		}, 999 );
	}

	public function tearDown(): void {
		$_POST = [];
		wp_set_current_user( 0 );
		set_current_screen( 'front' );
		remove_all_filters( 'wp_redirect_status' );
		parent::tearDown();
	}

	/**
	 * Test processing options.
	 */
	public function test_process_options() {
		$action = 'admin_post_fvs_form_response';
		$nonce = wp_create_nonce( 'fvs_nonce' );

		$_POST = [
			'fvs_nonce'   => $nonce,
			'blocked_ips' => '127.0.0.1,127.0.20.5',
			'form_ids'    => [6 => "a", 7 => "b", 8 => "c"],
			'fvs_allow_multiple_votes_from_same_ip' => 'no',
			'action'      => $action,
		];

		try {
			do_action( $action );
		} catch ( WPDieException $e ) {
			// We catch the redirect with a WPDieException and log the error message.
			// This way the test does not exit and can move on to the assertions.
			error_log( 'WPDieException: ' . $e->getMessage() );
		}

		$this->assertEquals( [6, 7, 8], json_decode(get_option( 'fvs_votation_forminator_form_ids' )) );
		$this->assertEquals( ['127.0.0.1', '127.0.20.5'], json_decode(get_option( 'fvs_votation_blocked_ips' )) );
		$this->assertEquals( 'no', json_decode(get_option( 'fvs_allow_multiple_votes_from_same_ip' )) );
	}

	/**
	 * Test can not process options with invalid nonce.
	 */
	public function test_can_not_process_options_with_invalid_nonce() {
		$action = 'admin_post_fvs_form_response';

		// Set an invalid nonce.
		$nonce = wp_create_nonce( 'non_existant_nonce' );

		// Get the values of the options before.
		$form_ids_before = json_decode(get_option( 'fvs_votation_forminator_form_ids' ));
		$ips_before = json_decode(get_option( 'fvs_votation_blocked_ips' ));
		$allow_multiple_votes_same_ip_before = json_decode(get_option( 'fvs_allow_multiple_votes_from_same_ip' ));

		// Set some new POST data.
		$_POST = [
			'fvs_nonce'   => $nonce,
			'blocked_ips' => '127.0.20.1',
			'form_ids'    => [1 => "a", 2 => "b", 3 => "c"],
			'fvs_allow_multiple_votes_from_same_ip' => 'yes',
			'action'      => $action,
		];

		// Do the action.
		try {
			do_action( $action );
		} catch ( WPDieException $e ) {
			// We catch the redirect with a WPDieException and log the error message.
			// This way the test does not exit and can move on to the assertions.
			error_log( 'WPDieException: ' . $e->getMessage() );
		}

		// Check that the option values haven't changed.
		$this->assertEquals( $form_ids_before, json_decode(get_option( 'fvs_votation_forminator_form_ids' )) );
		$this->assertEquals( $ips_before, json_decode(get_option( 'fvs_votation_blocked_ips' )) );
		$this->assertEquals( $allow_multiple_votes_same_ip_before, json_decode(get_option( 'fvs_allow_multiple_votes_from_same_ip' )) );
	}

	/**
	 * Test can not process options with invalid data for IP addresses.
	 */
	public function test_can_not_process_options_with_invalid_data_for_ips() {
		$action = 'admin_post_fvs_form_response';
		$nonce = wp_create_nonce( 'fvs_nonce' );

		$form_ids_before = json_decode(get_option( 'fvs_votation_forminator_form_ids' ));
		$ips_before = json_decode(get_option( 'fvs_votation_blocked_ips' ));
		$allow_multiple_votes_same_ip_before = json_decode(get_option( 'fvs_allow_multiple_votes_from_same_ip' ));

		$_POST = [
			'fvs_nonce'   => $nonce,
			'blocked_ips' => 'this is not an IP address.',
			'action'      => $action,
		];

		try {
			do_action( $action );
		} catch ( WPDieException $e ) {
			// We catch the redirect with a WPDieException and log the error message.
			// This way the test does not exit and can move on to the assertions.
			error_log( 'WPDieException: ' . $e->getMessage() );
		}

		$this->assertEquals( $form_ids_before, json_decode(get_option( 'fvs_votation_forminator_form_ids' )) );
		$this->assertEquals( $ips_before, json_decode(get_option( 'fvs_votation_blocked_ips' )) );
		$this->assertEquals( $allow_multiple_votes_same_ip_before, json_decode(get_option( 'fvs_allow_multiple_votes_from_same_ip' )) );
	}
}

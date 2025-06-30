<?php
/**
 * Class ResultsFetcherTest
 *
 * @package Forminator_voting_system
 */

use ForminatorVotingSystem\ResultsFetcher;

/**
 * Results Fetcher test.
 */
class ResultsFetcherTest extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		// Set the id's of the Forminator forms.
		$option_name = 'fvs_votation_forminator_form_ids';
		$post_data = [6, 7, 8];
		$this->add_option($option_name, $post_data);
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_fetching_results(): void {
		$resultsFetcher = new ResultsFetcher();

		$votationResults = $resultsFetcher->get_votation_results();

		// We check that there are three items in the votation results array.
		$this->assertEquals(3, count($votationResults));

		// Verify that the form ids of the votation results correspond to the ids we 
		// set in the setUp method.
		$this->assertEquals($votationResults[0]->form_id, '6');
		$this->assertEquals($votationResults[1]->form_id, '7');
		$this->assertEquals($votationResults[2]->form_id, '8');

		$votesPerIpResults = $resultsFetcher->get_votes_per_ip_results();

		// Verify that the votes per ip results correspond to the data from the test database tables.
		// There is only one IP address in the test tables, so there should only be one item in the results array.
		$this->assertEquals(1, count($votesPerIpResults));
		$this->assertEquals('172.20.0.1', $votesPerIpResults[0]->ip_address);
		$this->assertEquals('4', $votesPerIpResults[0]->num_votes);
	}

	protected function add_option(string $option_name, array $post_data): void {
		if ( ! get_option( $option_name ) ) {
			$result = add_option( $option_name, wp_json_encode( $post_data ), '', 'no' );
		}
		if ( json_decode( get_option( $option_name ) ) !== $post_data ) {
			$result = update_option( $option_name, wp_json_encode( $post_data ), '', 'no' );
		}
	}
}

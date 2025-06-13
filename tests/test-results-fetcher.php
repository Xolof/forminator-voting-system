<?php
/**
 * Class ResultsFetcherTest
 *
 * @package Forminator_voting_system
 */

/**
 * Results Fetcher test.
 */
class ResultsFetcherTest extends WP_UnitTestCase {

	public function setUp(): void {
		parent::setUp();

		$option_name = 'fvs_votation_forminator_form_ids';
		$post_data = [6, 7, 8];
		$this->add_option($option_name, $post_data);
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_fetching_results(): void {
		$resultsFetcher = new Results_Fetcher();
		$results = $resultsFetcher->get_votation_results();

		$this->assertEquals(3, count($results));

		$results = $resultsFetcher->get_votes_per_ip_results();

		$this->assertEquals(1, count($results));
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

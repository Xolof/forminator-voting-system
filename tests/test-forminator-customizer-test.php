<?php

/**
 * Class ForminatorCustomizerTest
 *
 * @package Forminator_voting_system
 */

require_once __DIR__ . '/../classes/wrappers/forminator-form-entry-model-wrapper.php';
require_once __DIR__ . '/../classes/wrappers/forminator-geo-wrapper.php';

/**
 * Forminator_Customizer test.
 */
class ForminatorCustomizerTest extends WP_UnitTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        $option_id = 'fvs_votation_forminator_form_ids';
        $option_value = [6, 7, 8];
        $this->add_option($option_id, $option_value);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_can_not_vote_several_times_from_same_ip_if_not_allowed(): void
    {
        $option_id = 'fvs_allow_multiple_votes_from_same_ip';
        $option_value = 'no';
        $this->add_option($option_id, $option_value);

        $forminator_geo = $this->createMock(Forminator_Geo_Wrapper::class);
        $forminator_geo->method('get_user_ip')
             ->willReturn('127.0.0.1');

        $forminator_form_entry_model = $this->createMock(Forminator_Form_Entry_Model_Wrapper::class);
        $forminator_form_entry_model->method('get_last_entry_by_ip_and_form')
             ->willReturn(42);

        $results_fetcher       = new Results_Fetcher();
        $forminator_customizer = new Forminator_Customizer($results_fetcher, $forminator_geo, $forminator_form_entry_model);
        $res = $forminator_customizer->submit_errors_ip_already_voted([], 7, [['value' => 'person@test.se']]);

        $this->assertEquals($res[0]['fvs-ip-already-voted'], 'Someone has already voted for this alternative with this IP address.');
    }

    public function test_can_vote_several_times_from_same_ip_if_allowed(): void
    {
        $option_id = 'fvs_allow_multiple_votes_from_same_ip';
        $option_value = 'yes';
        $this->add_option($option_id, $option_value);

        $forminator_geo = $this->createMock(Forminator_Geo_Wrapper::class);
        $forminator_geo->method('get_user_ip')
             ->willReturn('127.0.0.1');

        $forminator_form_entry_model = $this->createMock(Forminator_Form_Entry_Model_Wrapper::class);
        $forminator_form_entry_model->method('get_last_entry_by_ip_and_form')
             ->willReturn(42);

        $results_fetcher       = new Results_Fetcher();
        $forminator_customizer = new Forminator_Customizer($results_fetcher, $forminator_geo, $forminator_form_entry_model);
        $res = $forminator_customizer->submit_errors_ip_already_voted([], 7, [['value' => 'person@test.se']]);

        $this->assertEquals($res, []);
    }

    public function test_email_already_voted(): void
    {
        $forminator_geo = new Forminator_Geo();
        $forminator_form_entry_model = new Forminator_Form_Entry_Model();

        $results_fetcher       = new Results_Fetcher();
        $forminator_customizer = new Forminator_Customizer($results_fetcher, $forminator_geo, $forminator_form_entry_model);
        $res = $forminator_customizer->submit_errors_email([], 7, [['value' => 'mumin@troll.se']]);

        $this->assertEquals($res[0]['fvs-email-already-voted'], 'You have already voted for this alternative with this email address.');
    }

    public function test_email_is_missing(): void
    {
        $forminator_geo = new Forminator_Geo();
        $forminator_form_entry_model = new Forminator_Form_Entry_Model();

        $results_fetcher       = new Results_Fetcher();
        $forminator_customizer = new Forminator_Customizer($results_fetcher, $forminator_geo, $forminator_form_entry_model);
        $res = $forminator_customizer->submit_errors_email([], 7, []);

        $this->assertEquals($res[0]['fvs-missing-email'], 'Email address is missing.');
    }

    public function test_submit_errors_ip_blocked(): void
    {
        $forminator_geo = new Forminator_Geo();
        $forminator_form_entry_model = new Forminator_Form_Entry_Model();

        $option_id = 'fvs_votation_blocked_ips';
        $option_value = [$forminator_geo->get_user_ip()];
        $this->add_option($option_id, $option_value);

        $results_fetcher       = new Results_Fetcher();
        $forminator_customizer = new Forminator_Customizer($results_fetcher, $forminator_geo, $forminator_form_entry_model);
        $res = $forminator_customizer->submit_errors_ip_blocked([], 6);

        $this->assertEquals($res[0]['fvs-ip-blocked'], 'Your IP address has been blocked.');
    }

    public function test_custom_error_message(): void
    {
        $responseArg = [
            "message" => "Error: Your form is not valid, please fix the errors!",
            "success" => false,
            "notice" => "error",
            "form_id" => "7",
            "errors" => [
                [
                    "fvs-email-already-voted" => "You have already voted for this alternative with this email address."
                ]
            ]
        ];

        $forminator_geo = new Forminator_Geo();
        $forminator_form_entry_model = new Forminator_Form_Entry_Model();

        $results_fetcher       = new Results_Fetcher();
        $forminator_customizer = new Forminator_Customizer($results_fetcher, $forminator_geo, $forminator_form_entry_model);

        $res = $forminator_customizer->custom_error_message($responseArg, 6);

        $this->assertStringContainsString('<li class="fvs-forminator-error">', $res["message"]);
        $this->assertStringContainsString('You have already voted for this alternative with this email address.', $res["message"]);
    }

    protected function add_option(string $option_name, array|string $post_data): void
    {
        if (! get_option($option_name)) {
            $result = add_option($option_name, wp_json_encode($post_data), '', 'no');
        }
        if (json_decode(get_option($option_name)) !== $post_data) {
            $result = update_option($option_name, wp_json_encode($post_data), '', 'no');
        }
    }
}

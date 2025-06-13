<?php
/**
 * Forminator_Form_Entry_Model_Wrapper
 *
 * @package Forminator Votation System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/../../../forminator/library/model/class-form-entry-model.php';
require_once __DIR__ . '/../../../forminator/library/class-database-tables.php';

/**
 * A wrapper class for Forminator_Form_Entry_Model,
 * making it possible to mock the static method get_last_entry_by_ip_and_form().
 */
class Forminator_Form_Entry_Model_Wrapper {

	/**
	 * Get the id of the last entry of the form submitted from the IP address.
	 *
	 * @param integer $form_id
	 * @param string  $user_ip
	 * @return int
	 */
	public function get_last_entry_by_ip_and_form( int $form_id, string $user_ip ): int {
		return Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form( $form_id, $user_ip );
	}
}

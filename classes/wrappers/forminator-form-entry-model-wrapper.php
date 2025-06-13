<?php

require_once __DIR__ . '/../../../forminator/library/model/class-form-entry-model.php';
require_once __DIR__ . '/../../../forminator/library/class-database-tables.php';

class Forminator_Form_Entry_Model_Wrapper
{
    public function get_last_entry_by_ip_and_form( int $form_id, string $user_ip )
    {
        return Forminator_Form_Entry_Model::get_last_entry_by_ip_and_form($form_id, $user_ip);
    }
}

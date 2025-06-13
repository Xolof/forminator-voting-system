<?php

require_once __DIR__ . '/../../../forminator/library/class-geo.php';

class Forminator_Geo_Wrapper
{
    public function get_user_ip(): string {
        return Forminator_Geo::get_user_ip();
    }
}

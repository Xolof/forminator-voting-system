#!/usr/bin/bash

vendor/bin/phpcbf -ps src/ debug/ templates/ forminator-voting-system.php 

vendor/bin/phpcs -ps src/ debug/ templates/ forminator-voting-system.php 

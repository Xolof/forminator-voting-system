#!/usr/bin/bash

vendor/bin/phpcbf -ps classes/ debug/ templates/ forminator-voting-system.php 

vendor/bin/phpcs -ps classes/ debug/ templates/ forminator-voting-system.php 

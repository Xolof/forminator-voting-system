# Forminator Voting System

* Tags: voting, votation
* Requires at least: 6.8.1
* Tested up to: 6.8.1
* Requires PHP: 8.3.0
* Stable tag: 0.1.0
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: fvs

A voting system using Forminator forms.

## Description

Compiles results from voting and shows them in WP admin interface.
Receives only one vote per option per email address.
Lets you allow only one vote per option per IP address.
Makes it possible to block IP addresses.

## Installation

1. Install the plugin Forminator. 
2. Upload the directory `forminator_voting_system` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Create Forminator forms. The forms should have an email field.
5. Configure votation rules on the plugin's settings page in Wordpress admin.

## Changelog

### 0.1.0
* First version.

## Useful commands

### Make a new pot file for translations:
`wp i18n make-pot . languages/my-plugin.pot`

### Compile translations
`./compile_translations.sh`

### Lint
`./lint.sh`

### Install tests
`bin/install-wp-tests.sh`

### Setup test Forminator tables
`mysql <your-wordpress-database> < tests/test_tables.sql`

### Run tests
`vendor/bin/phpunit --verbose --coverage-html coverage`

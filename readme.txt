=== Forminator Voting System ===
Tags: comments, spam
Requires at least: 4.5
Tested up to: 6.8.1
Requires PHP: 8.0.0
Stable tag: 0.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: fvs

A voting system using Forminator forms.

== Description ==

Compiles results from voting and shows them in WP admin interface.
Receives only one vote per option per email address.
Lets you allow only one vote per option per IP address.
Enables blocking of IP addresses.

== Installation ==

1. Upload the directory `forminator_voting_system` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Create Forminator forms. The forms should have an email field.
4. Configure votation rules on the plugin's settings page in Wordpress admin.

== Changelog ==

= 0.1.0 =
* First version.

== Useful commands ==

### Make a new pot file for translations:
`wp i18n make-pot . languages/my-plugin.pot`

### Compile translations
`./compile_translations.sh`

### Check code with Phpcs
`vendor/bin/phpcs -ps classes/ --standard=WordPress`

### Install tests
`bin/install-wp-tests.sh`

### Run tests
`vendor/bin/phpunit --verbose --coverage-html coverage`

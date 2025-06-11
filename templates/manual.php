<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h1><?php echo esc_html__( 'Manual', 'fvs' ); ?></h1>
<h3><?php echo esc_html__( 'Functionality', 'fvs' ); ?></h3>
<ul>
	<li><?php echo esc_html__( 'Compiles results from the votation and shows them in the admin interface.', 'fvs' ); ?></li>
	<li><?php echo esc_html__( 'Receives only one vote per alternative per Email address.', 'fvs' ); ?></li>
	<li><?php echo esc_html__( 'Makes it possible to allow only one vote per alternative per IP address.', 'fvs' ); ?></li>
	<li><?php echo esc_html__( 'Allows blocking IP addresses.', 'fvs' ); ?></li>
</ul>
<h3><?php echo esc_html__( 'Get started', 'fvs' ); ?></h3>
<ol>
	<li><?php echo esc_html__( 'Create forms in Forminator. The forms should have an Email field.', 'fvs' ); ?></li>
	<li><?php echo esc_html__( 'Go to the settings for this plugin.', 'fvs' ); ?></li>
	<li><?php echo esc_html__( 'Mark the forms to include in the votation.', 'fvs' ); ?></li>
	<li><?php echo esc_html__( 'Save', 'fvs' ); ?>.</li>
</ol>

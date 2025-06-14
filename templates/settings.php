<?php
/**
 * Settings page
 *
 * @package Forminator Voting System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<h1><?php echo esc_html__( 'Settings', 'fvs' ); ?></h1>

<?php if ( current_user_can( 'manage_options' ) ) : ?>
	<?php $fvs_nonce = wp_create_nonce( 'fvs_nonce' ); ?>
	<div class="fvs_form">
	<form 
		action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
		method="POST"
		id="fvs_form"
	>
		<fieldset>
		<legend><?php echo esc_html__( 'Forms for votation:', 'fvs' ); ?></legend>
		<?php foreach ( $fvs_votation_forminator_forms as $fvs_form ) : ?>
			<div>
			<input
				type="checkbox"
				id="<?php echo esc_html( $fvs_form->id ); ?>"
				name="form_ids[<?php echo esc_html( $fvs_form->id ); ?>]"
				<?php echo in_array( $fvs_form->id, $fvs_existing_votation_form_ids, true ) ? 'checked' : null; ?>
			/>
			<label for="<?php echo esc_html( $fvs_form->id ); ?>"><?php echo esc_html( $fvs_form->settings['formName'] ); ?></label>
			</div>
		<?php endforeach; ?>
		</fieldset>
		<br>
		<fieldset>
		<legend><?php echo esc_html__( 'Allow multiple submissions from the same IP-address', 'fvs' ); ?></legend>
		<select name="fvs_allow_multiple_votes_from_same_ip" id="fvs_allow_multiple_votes_from_same_ip">
		<option value="yes" <?php echo 'yes' === $fvs_allow_multiple_votes_from_same_ip ? 'selected' : null; ?>><?php echo esc_html__( 'Yes' ); ?></option>
		<option value="no" <?php echo 'no' === $fvs_allow_multiple_votes_from_same_ip ? 'selected' : null; ?>><?php echo esc_html__( 'No' ); ?></option>
		</select> 
		</fieldset>
		<br>
		<fieldset>
		<legend><?php echo esc_html__( 'Blocked IP-addresses. Enter a comma separated list of IP-addresses.', 'fvs' ); ?></legend>
		<textarea 
			id="blocked_ips" 
			name="blocked_ips" 
			rows="5" 
			cols="35"
		><?php echo esc_html( implode( ',', $fvs_ip_block_list ) ); ?></textarea>
		</fieldset>
		<br>
		<input type="hidden" name="action" value="fvs_form_response" />
		<input type="hidden" name="fvs_nonce" value="<?php echo esc_html( $fvs_nonce ); ?>" />
		<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_html__( 'Save', 'fvs' ); ?>" />
	</form>
	</div>
<?php else : ?>
	<p><?php echo esc_html__( 'You are not authorized to perform this operation.', 'fvs' ); ?></p>
<?php endif; ?>

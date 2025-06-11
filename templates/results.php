<?php
/**
 * Results page
 *
 * @package Forminator Voting System
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$fvs_votation_results_db     = $fvs_votation_results_db ?? array();
$fvs_votes_per_ip_results_db = $fvs_votes_per_ip_results_db ?? array();

/**
 * Sort results by number of votes.
 *
 * @param stdClass $a An item in the array.
 * @param stdClass $b An item in the array.
 *
 * @return int
 */
function fvs_cmp( stdClass $a, stdClass $b ): int {
	if ( $a === $b ) {
		return 0;
	}
	return ( $a->num_votes > $b->num_votes ) ? -1 : 1;
}

uasort( $fvs_votation_results_db, 'fvs_cmp' );
uasort( $fvs_votes_per_ip_results_db, 'fvs_cmp' );

$fvs_total_num_votes = 0;
foreach ( $fvs_votation_results_db as $fvs_row ) {
	$fvs_total_num_votes += $fvs_row->num_votes;
}
?>

<h1><?php echo esc_html__( 'Results', 'fvs' ); ?></h1>

<?php if ( ! count( FVS_VOTATION_FORM_IDS ) ) : ?>
	<p>
	<?php echo esc_html__( 'No forms have been selected', 'fvs' ); ?>
	<a href="<?php echo esc_html( get_admin_url() ); ?>admin.php?page=render_votation_settings"><?php echo esc_html__( 'Select forms in settings.', 'fvs' ); ?></a>
	</p>
<?php endif; ?>

<h2><?php echo esc_html__( 'Total number of votes:', 'fvs' ); ?> <?php echo esc_html( $fvs_total_num_votes ); ?></h2>

<?php if ( ! count( $fvs_votation_results_db ) ) : ?>
	<p><?php echo esc_html__( 'There are not yet any votes.', 'fvs' ); ?></p>
<?php endif; ?>

<?php if ( count( $fvs_votation_results_db ) ) : ?>
<h2><?php echo esc_html__( 'Number of votes per alternative', 'fvs' ); ?></h2>
<div class="wrap">
<table class="widefat striped">
	<tr>
	<th><b><?php echo esc_html__( 'Placement', 'fvs' ); ?></b></th>
	<th><b><?php echo esc_html__( 'Alternative', 'fvs' ); ?></b></th>
	<th><b><?php echo esc_html__( 'Number of votes', 'fvs' ); ?></b></th>
	<th><b><?php echo esc_html__( 'Form', 'fvs' ); ?></b></th>
	</tr>
	<?php $fvs_index = 1; ?>
	<?php foreach ( $fvs_votation_results_db as $fvs_result ) : ?>
		<?php
		$fvs_alternative = preg_split(
			'/";s:/',
			preg_split( '/formName";s:\d+:"/', $fvs_result->alternative )[1]
		)[0];
		?>
	<tr>
		<td>
		<?php echo esc_html( $fvs_index ); ?>
		</td>
		<td>
		<?php echo esc_html( $fvs_alternative ); ?>
		</td>
		<td>    
		<?php echo esc_html( $fvs_result->num_votes ); ?>
		</td>  
		<td>
		<?php echo esc_html( $fvs_result->form_id ); ?>
		</td>
	</tr>
		<?php ++$fvs_index; ?>
	<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

<?php if ( count( $fvs_votes_per_ip_results_db ) ) : ?>
<h2><?php echo esc_html__( 'IP-addresses with the highest number of votes.', 'fvs' ); ?></h2>
<div class="wrap">
<table class="widefat striped">
	<tr>
	<th><b><?php echo esc_html__( 'IP addresses', 'fvs' ); ?></b></th>
	<th><b><?php echo esc_html__( 'Number of votes', 'fvs' ); ?></b></th>
	</tr>
	<?php foreach ( array_slice( $fvs_votes_per_ip_results_db, 0, 20 ) as $fvs_result ) : ?>
	<tr>
		<td>
		<?php echo esc_html( $fvs_result->ip_address ); ?>
		</td>
		<td>
		<?php echo esc_html( $fvs_result->num_votes ); ?>
		</td>
	</tr>
	<?php endforeach; ?>
</table>
</div>
<?php endif; ?>

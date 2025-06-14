<?php
/**
 * Custom debug functions.
 *
 * @package Forminator Voting System
 */

/**
 * Highlight Array
 *
 * @param array $debug_array
 * @return void
 */
function fvs_ha( $debug_array ): void {
	ini_set( 'highlight.comment', '#008000' );
	ini_set( 'highlight.default', '#ccc' );
	ini_set( 'highlight.html', '#808080' );
	ini_set( 'highlight.keyword', '#6868f9; font-weight: bold' );
	ini_set( 'highlight.string', '#8cf580' );

	echo "<pre class='fvs-pre'>";

	highlight_string( "<?php\n" . var_export( $debug_array, true ) . ";\n?>" ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export

	echo '</pre>';

	echo <<<STYLE
		<style>
			.fvs-pre {
				font-size: 0.9rem;
				font-weight: 400;
				background: #101010;
				padding: 0.4rem 1rem;
				line-height: 1.8rem;
				letter-spacing: 0.01rem;
			}
		</style>
	STYLE;
}

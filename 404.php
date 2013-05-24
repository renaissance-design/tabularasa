<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package WordPress
 * @subpackage TabulaRasa
 * @since TabulaRasa 1.0
 */

get_header(); ?>
<div class="content grid8 first" role="main">


				<h1><?php _e( 'Not Found', TabulaRasa::get_textdomain() ); ?></h1>
				<p><?php _e( 'Apologies, but the page you requested could not be found. Perhaps searching will help.', TabulaRasa::get_textdomain() ); ?></p>
				<?php get_search_form(); ?>

	<script type="text/javascript">
		// focus on search field after it has loaded
		document.getElementById('s') && document.getElementById('s').focus();
	</script>
</div>
<?php get_footer(); ?>
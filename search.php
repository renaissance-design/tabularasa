<?php
/**
 * The template for displaying Search Results pages.
 *
 * @package WordPress
 * @subpackage TabulaRasa
 * @since TabulaRasa 1.0
 */

get_header(); ?>
<div class="content" role="main">
<?php if ( have_posts() ) : ?>
				<h1><?php printf( __( 'Search Results for: %s', TabulaRasa::get_textdomain() ), '' . get_search_query() . '' ); ?></h1>
				<?php
				/* Run the loop for the search to output the results.
				 * If you want to overload this in a child theme then include a file
				 * called loop-search.php and that will be used instead.
				 */
				 get_template_part( 'loop', 'search' );
				?>
<?php else : ?>
					<h2><?php _e( 'Nothing Found', TabulaRasa::get_textdomain() ); ?></h2>
					<p><?php _e( 'Sorry, but nothing matched your search criteria. Please try again with some different keywords.', TabulaRasa::get_textdomain() ); ?></p>
					<?php get_search_form(); ?>
<?php endif; ?>
</div>
<?php get_sidebar(); ?>
<?php get_footer(); ?>

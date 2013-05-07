<?php
/**
 * Sets up theme functions.
 * 
 * @package WordPress
 * @subpackage TabulaRasa
 * @since TabulaRasa 1.0
 */
/**
 * Initial theme setup. Custom functions must be added below this point.
 */
require_once(locate_template('/lib/TabulaRasa.php'));
$TabulaRasa = TabulaRasa::get_instance();

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * To override this in a child theme, remove the filter and optionally add
 * your own function tied to the wp_page_menu_args filter hook.
 *
 * @since Twenty Ten 1.0
 */
function twentyten_page_menu_args($args) {
	$args['show_home'] = true;
	return $args;
}

add_filter('wp_page_menu_args', 'twentyten_page_menu_args');

/**
 * Sets the post excerpt length to 40 characters.
 *
 * To override this length in a child theme, remove the filter and add your own
 * function tied to the excerpt_length filter hook.
 *
 * @since Twenty Ten 1.0
 * @return int
 */
function twentyten_excerpt_length($length) {
	return 40;
}

add_filter('excerpt_length', 'twentyten_excerpt_length');

/**
 * Returns a "Continue Reading" link for excerpts
 *
 * @since Twenty Ten 1.0
 * @return string "Continue Reading" link
 */
function twentyten_continue_reading_link() {
	return ' <a href="' . get_permalink() . '">' . __('Continue reading <span class="meta-nav">&rarr;</span>', 'twentyten') . '</a>';
}

/**
 * Replaces "[...]" (appended to automatically generated excerpts) with an ellipsis and twentyten_continue_reading_link().
 *
 * To override this in a child theme, remove the filter and add your own
 * function tied to the excerpt_more filter hook.
 *
 * @since Twenty Ten 1.0
 * @return string An ellipsis
 */
function twentyten_auto_excerpt_more($more) {
	return ' &hellip;' . twentyten_continue_reading_link();
}

add_filter('excerpt_more', 'twentyten_auto_excerpt_more');

/**
 * Adds a pretty "Continue Reading" link to custom post excerpts.
 *
 * To override this link in a child theme, remove the filter and add your own
 * function tied to the get_the_excerpt filter hook.
 *
 * @since Twenty Ten 1.0
 * @return string Excerpt with a pretty "Continue Reading" link
 */
function twentyten_custom_excerpt_more($output) {
	if (has_excerpt() && !is_attachment()) {
		$output .= twentyten_continue_reading_link();
	}
	return $output;
}

add_filter('get_the_excerpt', 'twentyten_custom_excerpt_more');

if (!function_exists('twentyten_comment')) :

	/**
	 * Template for comments and pingbacks.
	 *
	 * To override this walker in a child theme without modifying the comments template
	 * simply create your own twentyten_comment(), and that function will be used instead.
	 *
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 * @since Twenty Ten 1.0
	 */
	function twentyten_comment($comment, $args, $depth) {
		$GLOBALS['comment'] = $comment;
		switch ($comment->comment_type) :
			case '' :
				?>
				<li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
					<div id="comment-<?php comment_ID(); ?>">
						<div class="comment-author vcard">
				<?php echo get_avatar($comment, 40); ?>
						<?php printf(__('%s <span class="says">says:</span>', 'twentyten'), sprintf('<cite class="fn">%s</cite>', get_comment_author_link())); ?>
						</div><!-- .comment-author .vcard -->
				<?php if ($comment->comment_approved == '0') : ?>
							<em><?php _e('Your comment is awaiting moderation.', 'twentyten'); ?></em>
							<br />
								<?php endif; ?>

						<div class="comment-meta commentmetadata"><a href="<?php echo esc_url(get_comment_link($comment->comment_ID)); ?>">
				<?php
				/* translators: 1: date, 2: time */
				printf(__('%1$s at %2$s', 'twentyten'), get_comment_date(), get_comment_time());
				?></a><?php edit_comment_link(__('(Edit)', 'twentyten'), ' ');
				?>
						</div><!-- .comment-meta .commentmetadata -->

						<div class="comment-body"><?php comment_text(); ?></div>

						<div class="reply">
					<?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
						</div><!-- .reply -->
					</div><!-- #comment-##  -->

				<?php
				break;
			case 'pingback' :
			case 'trackback' :
				?>
				<li class="post pingback">
					<p><?php _e('Pingback:', 'twentyten'); ?> <?php comment_author_link(); ?><?php edit_comment_link(__('(Edit)', 'twentyten'), ' '); ?></p>
					<?php
					break;
			endswitch;
		}

	endif;

	/**
	 * Register widgetized areas, including two sidebars and four widget-ready columns in the footer.
	 *
	 * To override twentyten_widgets_init() in a child theme, remove the action hook and add your own
	 * function tied to the init hook.
	 *
	 * @since Twenty Ten 1.0
	 * @uses register_sidebar
	 */
	function twentyten_widgets_init() {
		// Area 1, located at the top of the sidebar.
		register_sidebar(array(
				'name' => __('Primary Widget Area', 'twentyten'),
				'id' => 'primary-widget-area',
				'description' => __('The primary widget area', 'twentyten'),
				'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
		));

		// Area 2, located below the Primary Widget Area in the sidebar. Empty by default.
		register_sidebar(array(
				'name' => __('Secondary Widget Area', 'twentyten'),
				'id' => 'secondary-widget-area',
				'description' => __('The secondary widget area', 'twentyten'),
				'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
		));

		// Area 3, located in the footer. Empty by default.
		register_sidebar(array(
				'name' => __('First Footer Widget Area', 'twentyten'),
				'id' => 'first-footer-widget-area',
				'description' => __('The first footer widget area', 'twentyten'),
				'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
		));

		// Area 4, located in the footer. Empty by default.
		register_sidebar(array(
				'name' => __('Second Footer Widget Area', 'twentyten'),
				'id' => 'second-footer-widget-area',
				'description' => __('The second footer widget area', 'twentyten'),
				'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
		));

		// Area 5, located in the footer. Empty by default.
		register_sidebar(array(
				'name' => __('Third Footer Widget Area', 'twentyten'),
				'id' => 'third-footer-widget-area',
				'description' => __('The third footer widget area', 'twentyten'),
				'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
		));

		// Area 6, located in the footer. Empty by default.
		register_sidebar(array(
				'name' => __('Fourth Footer Widget Area', 'twentyten'),
				'id' => 'fourth-footer-widget-area',
				'description' => __('The fourth footer widget area', 'twentyten'),
				'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
				'after_widget' => '</li>',
				'before_title' => '<h3 class="widget-title">',
				'after_title' => '</h3>',
		));
	}

	/** Register sidebars by running twentyten_widgets_init() on the widgets_init hook. */
	add_action('widgets_init', 'twentyten_widgets_init');

	if (!function_exists('twentyten_posted_on')) :

		/**
		 * Prints HTML with meta information for the current post—date/time and author.
		 *
		 * @since Twenty Ten 1.0
		 */
		function twentyten_posted_on() {
			printf(__('<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', 'twentyten'), 'meta-prep meta-prep-author', sprintf('<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>', get_permalink(), esc_attr(get_the_time()), get_the_date()
							), sprintf('<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>', get_author_posts_url(get_the_author_meta('ID')), sprintf(esc_attr__('View all posts by %s', 'twentyten'), get_the_author()), get_the_author()
							)
			);
		}

	endif;

	if (!function_exists('twentyten_posted_in')) :

		/**
		 * Prints HTML with meta information for the current post (category, tags and permalink).
		 *
		 * @since Twenty Ten 1.0
		 */
		function twentyten_posted_in() {
			// Retrieves tag list of current post, separated by commas.
			$tag_list = get_the_tag_list('', ', ');
			if ($tag_list) {
				$posted_in = __('This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyten');
			} elseif (is_object_in_taxonomy(get_post_type(), 'category')) {
				$posted_in = __('This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyten');
			} else {
				$posted_in = __('Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', 'twentyten');
			}
			// Prints the string, replacing the placeholders.
			printf(
							$posted_in, get_the_category_list(', '), $tag_list, get_permalink(), the_title_attribute('echo=0')
			);
		}


endif;

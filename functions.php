<?php

/**
 * Sets up theme functions.
 * 
 * @package WordPress
 * @subpackage TabulaRasa
 * @since TabulaRasa 1.0
 */

/**
 * Set up theme features
 */
function TabulaRasa_setup() {
	
	require_once(locate_template('/lib/classes/TabulaRasa.php'));

  /* HTML5 features */
  add_theme_support('html5');

  /* Featured Images */
  add_theme_support('post-thumbnails');

  /* Post Formats */
  add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'));

  /* .htaccess firewall */
  add_theme_support('firewall');

  register_nav_menus(array(
      'primary' => __('Primary Navigation', TabulaRasa::get_textdomain()),
      'secondary' => __('Secondary Navigation', TabulaRasa::get_textdomain())
  ));

  

  $TabulaRasa = TabulaRasa::get_instance();
}

add_action('after_setup_theme', 'TabulaRasa_setup');

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
      'name' => __('Primary Widget Area', TabulaRasa::get_textdomain()),
      'id' => 'primary-widget-area',
      'description' => __('The primary widget area', TabulaRasa::get_textdomain()),
      'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
      'after_widget' => '</li>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
  ));

  // Area 2, located below the Primary Widget Area in the sidebar. Empty by default.
  register_sidebar(array(
      'name' => __('Secondary Widget Area', TabulaRasa::get_textdomain()),
      'id' => 'secondary-widget-area',
      'description' => __('The secondary widget area', TabulaRasa::get_textdomain()),
      'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
      'after_widget' => '</li>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
  ));

  // Area 3, located in the footer. Empty by default.
  register_sidebar(array(
      'name' => __('First Footer Widget Area', TabulaRasa::get_textdomain()),
      'id' => 'first-footer-widget-area',
      'description' => __('The first footer widget area', TabulaRasa::get_textdomain()),
      'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
      'after_widget' => '</li>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
  ));

  // Area 4, located in the footer. Empty by default.
  register_sidebar(array(
      'name' => __('Second Footer Widget Area', TabulaRasa::get_textdomain()),
      'id' => 'second-footer-widget-area',
      'description' => __('The second footer widget area', TabulaRasa::get_textdomain()),
      'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
      'after_widget' => '</li>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
  ));

  // Area 5, located in the footer. Empty by default.
  register_sidebar(array(
      'name' => __('Third Footer Widget Area', TabulaRasa::get_textdomain()),
      'id' => 'third-footer-widget-area',
      'description' => __('The third footer widget area', TabulaRasa::get_textdomain()),
      'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
      'after_widget' => '</li>',
      'before_title' => '<h3 class="widget-title">',
      'after_title' => '</h3>',
  ));

  // Area 6, located in the footer. Empty by default.
  register_sidebar(array(
      'name' => __('Fourth Footer Widget Area', TabulaRasa::get_textdomain()),
      'id' => 'fourth-footer-widget-area',
      'description' => __('The fourth footer widget area', TabulaRasa::get_textdomain()),
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
    printf(__('<span class="%1$s">Posted on</span> %2$s <span class="meta-sep">by</span> %3$s', TabulaRasa::get_textdomain()), 'meta-prep meta-prep-author', sprintf('<a href="%1$s" title="%2$s" rel="bookmark"><span class="entry-date">%3$s</span></a>', get_permalink(), esc_attr(get_the_time()), get_the_date()
            ), sprintf('<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s">%3$s</a></span>', get_author_posts_url(get_the_author_meta('ID')), sprintf(esc_attr__('View all posts by %s', TabulaRasa::get_textdomain()), get_the_author()), get_the_author()
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
      $posted_in = __('This entry was posted in %1$s and tagged %2$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', TabulaRasa::get_textdomain());
    }
    elseif (is_object_in_taxonomy(get_post_type(), 'category')) {
      $posted_in = __('This entry was posted in %1$s. Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', TabulaRasa::get_textdomain());
    }
    else {
      $posted_in = __('Bookmark the <a href="%3$s" title="Permalink to %4$s" rel="bookmark">permalink</a>.', TabulaRasa::get_textdomain());
    }
    // Prints the string, replacing the placeholders.
    printf(
            $posted_in, get_the_category_list(', '), $tag_list, get_permalink(), the_title_attribute('echo=0')
    );
  }








endif;
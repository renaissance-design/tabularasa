<?php

/**
 * Main class for the theme framework
 *
 * @package WordPress
 * @subpackage TabulaRasa
 * @since TabulaRasa 1.0
 */
class TabulaRasa {

  private static $instance;
  public $slug;
  private $htaccess;
  private $options;
  private $theme;
  public $settings;
  public $dev;

  /**
   * Constructor for the framework class
   * 
   */
  public function __construct() {

    $this->theme = wp_get_theme();

    $this->slug = sanitize_title_with_dashes($this->theme->name);

    $this->settings = get_option('TabulaRasa_general_settings');
    
    $this->dev = array();

    add_action('init', array(&$this, 'init'));

    add_action('after_setup_theme', array(&$this, 'setup'));

    if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') || stristr($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false) {
      require_once(locate_template('/lib/classes/htaccess.php'));
      $this->htaccess = new TabulaRasa_htaccess($this->slug);
    }

    require_once(locate_template('/lib/classes/options.php'));
    $this->options = new TabulaRasa_options($this->slug, $this->theme->name);

    if (!class_exists('SmartMetaBox')) {
      require_once(locate_template('/lib/classes/meta.php'));
    }

    add_action('widgets_init', array(&$this, 'register_widgets'));
  }

  /**
   * Singleton instance
   * 
   * @return object
   */
  public static function get_instance() {
    if (!isset(self::$instance)) {
      $c = __CLASS__;
      self::$instance = new $c();
    }
    return self::$instance;
  }

  /**
   * Returns the theme's textdomain for use in templates
   * 
   * @return string
   */
  public static function get_textdomain() {
    return self::$instance->slug;
  }

  /**
   * Echo pagination. If used with a custom query, it needs to be passed as an argument. Otherwise it assumes the default $wp_query
   * 
   * @param object $query An instance of WP_Query
   */
  public static function paginate($query = '') {
    if (!($query instanceof WP_Query)) {
      global $wp_query;
      $query = $wp_query;
    }
    if ($query->max_num_pages > 1) {
      $current_page = max(1, get_query_var('paged'));
      echo '<nav class="pagination">';
      echo paginate_links(array(
          'base' => get_pagenum_link(1) . '%_%',
          'format' => __('page', self::$instance->get_textdomain()) . '/%#%',
          'current' => $current_page,
          'total' => $query->max_num_pages,
          'prev_text' => __('Prev', self::$instance->get_textdomain()),
          'next_text' => __('Next', self::$instance->get_textdomain()),
          'type' => 'list'
      ));
      echo '</nav>';
    }
  }

  /**
   * Hooks to WP init()
   */
  function init() {

    if (is_admin()) {
      
    }
    else {
      $this->dev_tools();
    }
  }

  /**
   * Sets theme defaults
   * 
   * @return void
   */
  function setup() {
    load_theme_textdomain($this->slug, get_template_directory() . '/lang');
    add_editor_style('css/editor-style.css');
    add_action('wp_enqueue_scripts', array(&$this, 'bulletproof_jquery'), 20);
    add_filter('wp_page_menu_args', array(&$this, 'page_menu_args'));
    add_filter('user_contactmethods', array(&$this, 'update_contact_methods'), 10, 1);
    $this->cleanup_header();
    $this->cleanup_nav();
    add_filter('excerpt_length', array(&$this, 'get_excerpt_length'));
    add_filter('excerpt_more', array(&$this, 'auto_excerpt_more'));
    add_filter('get_the_excerpt', array(&$this, 'custom_excerpt_more'));
    add_action('admin_bar_menu', array(&$this, 'dev_toolbar_items'), 100);
    add_filter('template_include', array(&$this, 'dev_template_id'), 1000);
  }

  /**
   * Clean up the header
   * 
   * @return void
   */
  function cleanup_header() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'feed_links', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'parent_post_rel_link', 10, 0);
    remove_action('wp_head', 'start_post_rel_link', 10, 0);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
    remove_action('wp_head', 'feed_links_extra', 2);
    remove_action('wp_head', 'feed_links_extra', 3);
    add_filter('use_default_gallery_style', '__return_false');
    add_filter('style_loader_tag', array(&$this, 'cleanup_type'));
    add_filter('wp_title', array(&$this, 'filter_wp_title'), 10, 2);
    add_action('widgets_init', array(&$this, 'remove_recent_comments_style'));
  }

  /**
   * Cleans up the default output of WP menus
   * 
   * @return void
   */
  function cleanup_nav() {
    add_filter('wp_nav_menu', array(&$this, 'cleanup_nav_menu'));
    add_filter('wp_page_menu', array(&$this, 'cleanup_nav_menu'));
  }

  /**
   * Includes additional widgets
   * 
   * @return void
   */
  function register_widgets() {
    require_once(locate_template('/lib/classes/widgets.php'));
    register_widget('TabulaRasa_Twitter_Widget');
  }

  /**
   * Gets the excerpt length from settings
   * 
   * @since Tabula Rasa 1.1
   * @return int
   */
  function get_excerpt_length() {
    return (int) $this->settings['excerpt_length'];
  }

  /**
   * Replaces the "..." appended to post excerpts with an ellipsis
   * 
   * @since Tabula Rasa 1.1
   * @param string $more
   * @return string
   */
  function auto_excerpt_more($more) {
    return ' &hellip;' . $this->continue_reading_link();
  }

  /**
   * Returns a "Continue Reading" link for excerpts
   *
   * @since Tabula Rasa 1.1
   * @return string "Continue Reading" link
   */
  function continue_reading_link() {
    return ' <a href="' . get_permalink() . '">' . $this->settings['continue_reading_link'] . '</a>';
  }

  /**
   * Adds a pretty "Continue Reading" link to custom post excerpts.
   *
   * @since Tabula Rasa 1.1
   * @return string Excerpt with a pretty "Continue Reading" link
   */
  function custom_excerpt_more($output) {
    if (has_excerpt() && !is_attachment()) {
      $output .= $this->continue_reading_link();
    }
    return $output;
  }

  /**
   * Replaces WordPress' built-in jQuery from the Google CDN
   * 
   * @return void
   */
  function bulletproof_jquery() {
    global $wp_scripts;

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
    $jquery_version = $wp_scripts->registered['jquery']->ver;
    $url = $protocol . '://ajax.googleapis.com/ajax/libs/jquery/' . $jquery_version . '/jquery.min.js';
    wp_deregister_script('jquery');
    if (get_transient('google_jquery') == true) {
      wp_register_script('jquery', $url, array(), $jquery_version, true);
    }
    else {
      $resp = wp_remote_head($url);
      if (!is_wp_error($resp) && 200 == $resp['response']['code']) {
        set_transient('google_jquery', true, 60 * 5);
        wp_register_script('jquery', $url, array(), $jquery_version, true);
      }
      else {
        set_transient('google_jquery', false, 60 * 5);
        $url = get_bloginfo('wpurl') . '/wp-includes/js/jquery/jquery.js';
        wp_register_script('jquery', $url, array(), $jquery_version, true);
      }
    }
    wp_enqueue_script('jquery');
  }

  /**
   * Filters the default output of wp_title()
   * 
   * @global int $paged
   * @global int $page
   * @param string $title
   * @param string $separator
   * @return string
   */
  function filter_wp_title($title, $separator) {

    if (is_feed()) {
      return $title;
    }

    global $paged, $page;

    $site_name = get_bloginfo('name', 'display');
    $site_description = get_bloginfo('description', 'display');

    if (is_search()) {
      $title = sprintf(__('Search results for %s', $this->slug), '"' . get_search_query() . '"');
      if ($paged >= 2)
        $title .= ' ' . $separator . ' ' . sprintf(__('Page %s', $this->slug), $paged);
      $title .= ' ' . $separator . ' ' . $site_name;
      return $title;
    }

    if ($site_description && (is_home())) {
      $title .= $site_description . ' ' . $separator;
    }
    if ($paged >= 2 || $page >= 2) {
      $title .= ' ' . $separator . ' ' . sprintf(__('Page %s', $this->slug), max($paged, $page));
    }
    $title .= ' ' . $site_name;
    return $title;
  }

  /**
   * Strip the unnecessary type attribute from <style> elements
   * 
   * @param string $src
   * @return string
   */
  function cleanup_type($src) {
    return str_replace("type='text/css'", '', $src);
  }

  /**
   * Removes the default styles that are packaged with the Recent Comments widget.
   * 
   * @return void
   */
  function remove_recent_comments_style() {
    global $wp_widget_factory;
    remove_action('wp_head', array(
        $wp_widget_factory->widgets['WP_Widget_Recent_Comments'],
        'recent_comments_style'
            )
    );
  }

  /**
   * Show home link in wp_page_menu() by default
   */
  function page_menu_args($args) {
    $args['show_home'] = true;
    return $args;
  }

  /**
   * Replaces the many default active menu classes with one
   * 
   * @param string $menu
   * @return string
   */
  function cleanup_nav_menu($menu) {
    $replace = array(
        'current-menu-item' => 'active',
        'current-menu-parent' => 'active',
        'current-menu-ancestor' => 'active',
        'current_page_item' => 'active',
        'current_page_parent' => 'active',
        'current_page_ancestor' => 'active',
    );

    $menu = str_replace(array_keys($replace), $replace, $menu);
    return $menu;
  }

  /**
   * Updates the user profile with some more up to date contact methods.
   * 
   * @param array $contact_methods
   * @return array
   */
  function update_contact_methods($contact_methods) {
    $new_methods = array(
        'twitter' => 'Twitter',
        'googleplus' => 'Google+',
        'facebook' => 'Facebook',
        'linkedin' => 'LinkedIn',
        'appdotnet' => 'App.net'
    );
    foreach ($new_methods as $key => $value) {
      if (!isset($contact_methods[$key])) {
        $contact_methods[$key] = $value;
      }
    }
    $remove_methods = array(
        'aim',
        'yim',
        'jabber'
    );
    foreach ($remove_methods as $method) {
      unset($contact_methods[$method]);
    }

    return $contact_methods;
  }

  /**
   * Adds the dev menu to the toolbar
   * 
   * @since TabulaRasa 1.11
   * @param object $admin_bar
   */
  function dev_toolbar_items($admin_bar) {
    if (current_user_can('manage_options') && WP_DEBUG === true) {
      $admin_bar->add_menu(array(
          'id' => 'tr-dev-tools',
          'title' => 'TabulaRasa Dev Tools',
          'href' => '#',
          'meta' => array(
              'title' => __('Dev Tools'),
          ),
      ));
      if (!is_admin()) {
        global $wp_query;
        $admin_bar->add_menu(
                array(
                    'id' => 'tr-dev-grid-overlay',
                    'parent' => 'tr-dev-tools',
                    'title' => __('Grid Overlay'),
                    'href' => '#',
                    'meta' => array(
                        'title' => __('Show the grid overlay'),
                    )
                )
        );
        $admin_bar->add_menu(
                array(
                    'id' => 'tr-dev-current-template',
                    'parent' => 'tr-dev-tools',
                    'title' => __('Template') . ': ' . $this->dev['current_template'],
                    'href' => admin_url('theme-editor.php?file=' . $this->dev['current_template'] . '&amp;theme=' . $this->theme->name),
                    'meta' => array(
                        'title' => __('Edit this template'),
                    )
                )
        );
      }
    }
  }
  
  /**
   * Stores the current template for later retrieval
   * 
   * @author Mike Bishop mike@miniman-webdesign.co.uk
   * 
   * @param string $template
   * @return string
   * @since TabulaRasa 1.12
   */
  function dev_template_id($template) {
    $this->dev['current_template'] = basename($template);
    return $template;
  }

  /**
   * Loads the dev tools
   * 
   * @since TabulaRasa 1.11
   */
  function dev_tools() {
    if (current_user_can('manage_options') && WP_DEBUG === true) {
      add_action('wp_footer', array(&$this, 'dev_grid_overlay'), 100);
      wp_register_style('tabularasa-dev', get_stylesheet_directory_uri() . '/css/dev.css');
      wp_enqueue_style('tabularasa-dev');
    }
  }

  /**
   * Adds a grid overlay to the frontend to aid development
   * 
   * @since TabulaRasa 1.11
   */
  function dev_grid_overlay() {
    ?>
    <div class="dev-overlay">
      <div class="container">
        <div class="grid1 first">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
        <div class="grid1">
        </div>
      </div>
    </div>
    <script>
      jQuery(document).ready(function() {
        jQuery('#wp-admin-bar-tr-grid-overlay a').click(function() {
          jQuery('.dev-overlay').toggle();
        });
      });
    </script>
    <?php
  }

}
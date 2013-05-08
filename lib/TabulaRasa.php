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

	/**
	 * Constructor for the framework class
	 * 
	 */
	public function __construct() {

		$theme = wp_get_theme();

		$this->slug = sanitize_title_with_dashes($theme->name);

		add_action('init', array(&$this, 'init'));

		add_action('after_setup_theme', array(&$this, 'setup'));

		if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') || stristr($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false) {
			require_once(locate_template('/lib/htaccess.php'));
			$this->htaccess = new TabulaRasa_htaccess($this->slug);
		}

		if (!class_exists('SmartMetaBox')) {
			require_once(locate_template('/lib/meta.php'));
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
	 * Hooks to WP init()
	 */
	function init() {

		if (is_admin()) {
			
		} else {
			
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
		require_once(locate_template('/lib/widgets.php'));
		register_widget('TabulaRasa_Twitter_Widget');
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
		} else {
			$resp = wp_remote_head($url);
			if (!is_wp_error($resp) && 200 == $resp['response']['code']) {
				set_transient('google_jquery', true, 60 * 5);
				wp_register_script('jquery', $url, array(), $jquery_version, true);
			} else {
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
		foreach($new_methods as $key => $value) {
			if(!isset($contact_methods[$key])) {
				$contact_methods[$key] = $value;
			}
		}
		$remove_methods = array(
				'aim',
				'yim',
				'jabber'
		);
		foreach($remove_methods as $method) {
			unset($contact_methods[$method]);
		}
		
		return $contact_methods;
	}

}
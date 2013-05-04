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
	 * @param string $slug
	 */
	public function __construct($slug = 'tabularasa') {

		$this->slug = $slug;

		add_action('init', array(&$this, 'init'));

		add_action('after_setup_theme', array(&$this, 'setup'));

		if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') || stristr($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false) {
			require_once('htaccess.php');
			$this->htaccess = new TabulaRasa_htaccess($this->slug);
		}
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
		register_nav_menus(array(
				'primary_navigation' => __('Primary Navigation', $this->slug),
		));
		add_theme_support('post-thumbnails');
		add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'));
		add_editor_style('css/editor-style.css');
		add_action('wp_enqueue_scripts', array(&$this, 'bulletproof_jquery'), 20);
		
		$this->cleanup_header();
	}

	/**
	 * Clean up the header
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
	}

	/**
	 * Includes additional widgets
	 * 
	 * @return void
	 */
	function widgets() {
		require_once('widgets.php');
	}

	/**
	 * Replaces WordPress' built-in jQuery from the Google CDN
	 * 
	 * @return void
	 */
	function bulletproof_jquery() {
		global $wp_scripts;

		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http';
		$jquery_version = $wp_scripts->registered['jquery-core']->ver;
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

	function cleanup_type($src) {
		return str_replace(" type='text/css'", '', $src);
	}

}
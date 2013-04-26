<?php

/**
 * Main class for the theme framework
 *
 * @author Chris Cox
 */
class TabulaRasa {

    private static $instance;
    public $slug;
    private $htaccess;

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
     */
    function setup() {

        load_theme_textdomain($this->slug, get_template_directory() . '/lang');

        register_nav_menus(array(
            'primary_navigation' => __('Primary Navigation', $this->slug),
        ));

        add_theme_support('post-thumbnails');

        add_theme_support('post-formats', array('aside', 'gallery', 'link', 'image', 'quote', 'status', 'video', 'audio', 'chat'));

        add_editor_style('css/editor-style.css');

        add_action('wp_enqueue_scripts', array(&$this, 'bulletproof_jquery'));
    }

    function widgets() {
        require_once('widgets.php');
    }

    /**
     * Replaces WordPress' built-in jQuery from the Google CDN
     */
    function bulletproof_jquery() {
        $protocol = ($_SERVER['HTTPS'] == 'on') ? 'https' : 'http';
        $url = $protocol . '://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
        wp_deregister_script('jquery');
        if (get_transient('google_jquery') == true) {
            wp_register_script('jquery', $url, array(), null, true);
        } else {
            $resp = wp_remote_head($url);
            if (!is_wp_error($resp) && 200 == $resp['response']['code']) {
                set_transient('google_jquery', true, 60 * 5);
                wp_register_script('jquery', $url, array(), null, true);
            } else {
                set_transient('google_jquery', false, 60 * 5);
                $url = get_bloginfo('wpurl') . '/wp-includes/js/jquery/jquery.js';
                wp_register_script('jquery', $url, array(), '1.7.1', true);
            }
        }
        wp_enqueue_script('jquery');
    }

}

?>

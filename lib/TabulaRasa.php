<?php

/**
 * Main class for the theme framework
 *
 * @author Chris Cox
 */
class TabulaRasa {
    
    private static $instance;
    public $slug;

    public function __construct($slug = 'tabularasa') {
        
        $this->slug = $slug;
        
        add_action('init', array(&$this, 'init'));  
        
        add_action('after_setup_theme', array(&$this, 'setup'));
        
        register_activation_hook(__FILE__, array(&$this, 'activate'));
        
        register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
        
        register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));
        
        if (stristr($_SERVER['SERVER_SOFTWARE'], 'apache') || stristr($_SERVER['SERVER_SOFTWARE'], 'litespeed') !== false) {
            require_once('htaccess.php');
            $htaccess = new TabulaRasa_htaccess($this->slug);
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
        require_once(locate_template('/lib/widgets.php'));
        if (is_admin()) {
            
        }
        else {
            
        }
    }
    
    /**
     * Activation hook
     */
    function activate() {

    }

    /**
     * Deactivation hook
     */
    function deactivate() {
        
    }

    /**
     * Uninstall hook
     */
    function uninstall() {
        
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
    }
    
    
}

?>

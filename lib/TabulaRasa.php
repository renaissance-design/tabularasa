<?php

/**
 * Main class for the theme framework
 *
 * @author Chris Cox
 */
class TabulaRasa {
    
    private static $instance;
    public $slug;

    public function __construct() {
        
        $this->slug = 'tabularasa';
        
        add_action('init', array(&$this, 'init'));                    
        
        register_activation_hook(__FILE__, array(&$this, 'activate'));
        
        register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
        
        register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));
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
}

?>

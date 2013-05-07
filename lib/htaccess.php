<?php

/**
 * Sets up the .htaccess file, adding custom rules
 */
class TabulaRasa_htaccess {

    public $slug;

    public function __construct($slug) {

        $this->slug = $slug;

        add_action('admin_init', array(&$this, 'htaccess_write_check'));
        
        add_action('generate_rewrite_rules', array(&$this, 'htaccess_add_rules'));
               
    }

    /**
     * Checks if the .htaccess file is writable. If not, queues an error if the current user is admin.
		 * 
		 * @return void
     */
    function htaccess_write_check() {
        if (!is_writable(get_home_path() . '.htaccess')) {
            if (current_user_can('manage_options')) {
                add_action('admin_notices', array(&$this, 'htaccess_writable_error'));
            }
        };
    }

    /**
     * Displays an error notification if the .htaccess file isn't writable
		 * 
		 * @return void
     */
    function htaccess_writable_error() {
        echo '<div class="error"><p>' . sprintf(__('Please make sure your <a href="%s">.htaccess</a> file is writable ', $this->slug), admin_url('options-permalink.php')) . '</p></div>';
    }

    /**
     * Adds extra goodness whenever WP writes to .htaccess
     * 
     * @global object $wp_rewrite
     * @param string $content
     * @return bool
     */
    function htaccess_add_rules($content) {
        global $wp_rewrite;
        $home_path = function_exists('get_home_path') ? get_home_path() : ABSPATH;
        $htaccess_file = $home_path . '.htaccess';
        $mod_rewrite_enabled = function_exists('got_mod_rewrite') ? got_mod_rewrite() : false;
               
        if ((!file_exists($htaccess_file) && is_writable($home_path) && $wp_rewrite->using_mod_rewrite_permalinks()) || is_writable($htaccess_file)) {
            if($mod_rewrite_enabled) {
                $firewall_rules = extract_from_markers($htaccess_file, 'Tabula Rasa');
                if($firewall_rules === array()) {
                    $filename = dirname(__FILE__) . '/TR-htaccess';
                    return insert_with_markers($htaccess_file, 'Tabula Rasa', extract_from_markers($filename, 'Tabula Rasa'));
                }
            }
        }
        
        return $content;
    }

}
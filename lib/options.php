<?php

/**
 * Class to handle options
 */
class TabulaRasa_options {

  /**
   * Theme slug, used for textdomain
   * 
   * @var string 
   */
  public $slug;

  /**
   * Theme name
   * 
   * @var string 
   */
  public $name;

  /**
   * 
   *
   * @var string 
   */
  private $general_settings_key = 'TabulaRasa_general_settings';

  /**
   *
   * @var string 
   */
  private $advanced_settings_key = 'TabulaRasa_advanced_settings';

  /**
   *
   * @var string 
   */
  private $options_key = 'TabulaRasa_options';

  /**
   *
   * @var array 
   */
  private $settings_tabs = array();

  /**
   * Constructor
   */
  function __construct($slug, $name) {

    $this->slug = $slug;
    $this->name = $name;
    add_action('init', array(&$this, 'load_settings'));
    add_action('admin_init', array(&$this, 'register_general_settings'));
    // add_action('admin_init', array(&$this, 'register_advanced_settings'));
    add_action('admin_menu', array(&$this, 'add_admin_menus'));
  }

  /**
   * Load the settings
   */
  function load_settings() {
    $this->general_settings = (array) get_option($this->general_settings_key);
    $this->advanced_settings = (array) get_option($this->advanced_settings_key);
    $this->general_settings = array_merge(array(
        'excerpt_length' => '40'
            ), $this->general_settings);
    /* $this->advanced_settings = array_merge(array(
        'advanced_option' => 'Advanced value'
            ), $this->advanced_settings);
     * 
     */
  }

  /**
   * Registers the settings for the general tab
   */
  function register_general_settings() {
    $this->settings_tabs[$this->general_settings_key] = __('General', $this->slug);

    register_setting($this->general_settings_key, $this->general_settings_key);
    add_settings_section('section_general', __('General Theme Settings', $this->slug), array(&$this, 'section_general_desc'), $this->general_settings_key);
    add_settings_field('excerpt_length', __('Excerpt Length', $this->slug), array(&$this, 'excerpt_length'), $this->general_settings_key, 'section_general');
  }

  /**
   * Description for the general settings tab
   */
  function section_general_desc() {
    _e('Some general theme settings.', $this->slug);
  }

  /**
   * Input for excerpt length
   */
  function excerpt_length() {
    ?>
    <input type="text" name="<?php echo $this->general_settings_key; ?>[excerpt_length]" value="<?php echo esc_attr($this->general_settings['excerpt_length']); ?>" />
    <?php
  }

  /**
   * Register the settings for the advanced tab
   */
  function register_advanced_settings() {
    $this->settings_tabs[$this->advanced_settings_key] = __('Advanced', $this->slug);

    register_setting($this->advanced_settings_key, $this->advanced_settings_key);
    add_settings_section('section_advanced', 'Advanced Plugin Settings', array(&$this, 'section_advanced_desc'), $this->advanced_settings_key);
    add_settings_field('advanced_option', 'An Advanced Option', array(&$this, 'field_advanced_option'), $this->advanced_settings_key, 'section_advanced');
  }

  /**
   * Adds a description to the advanced tab
   */
  function section_advanced_desc() {
    echo 'Advanced section description goes here.';
  }

  /**
   * Adds options for the advanced tab
   */
  function field_advanced_option() {
    ?>
    <input type="text" name="<?php echo $this->advanced_settings_key; ?>[advanced_option]" value="<?php echo esc_attr($this->advanced_settings['advanced_option']); ?>" />
    <?php
  }

  /**
   * Adds a manu entry for the theme options page
   * 
   * @return void
   */
  function add_admin_menus() {
    $theme_page = add_theme_page(
            __('Theme Options', $this->slug), __('Theme Options', $this->slug), 'edit_theme_options', 'theme_options', array(&$this, 'options_page')
    );
    if (!$theme_page) {
      return;
    }

    add_action("load-$theme_page", array(&$this, 'options_help'));
  }

  /**
   * Outputs the theme options page
   */
  function options_page() {
    $tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;
    ?>
    <div class="wrap">
    <?php $this->options_tabs(); ?>
      <form method="post" action="options.php">
    <?php wp_nonce_field('update-options'); ?>
      <?php settings_fields($tab); ?>
      <?php do_settings_sections($tab); ?>
        <?php submit_button(); ?>
      </form>
    </div>
        <?php
      }

      /**
       * Outputs the tabs for the theme options page
       */
      function options_tabs() {
        $current_tab = isset($_GET['tab']) ? $_GET['tab'] : $this->general_settings_key;

        screen_icon();
        echo '<h2 class="nav-tab-wrapper">';
        foreach ($this->settings_tabs as $tab_key => $tab_caption) {
          $active = $current_tab == $tab_key ? 'nav-tab-active' : '';
          echo '<a class="nav-tab ' . $active . '" href="?page=' . $this->options_key . '&tab=' . $tab_key . '">' . $tab_caption . '</a>';
        }
        echo '</h2>';
      }

      /**
       * Outputs the help section for the theme options page
       */
      function options_help() {
        $help = '<p>' . __('Some themes provide customization options that are grouped together on a Theme Options screen. If you change themes, options may change or disappear, as they are theme-specific. Your current theme, ' . $this->name . ', provides the following Theme Options:', $this->slug) . '</p>' .
                '<ol>' .
                '<li>' . __('<strong>Color Scheme</strong>: You can choose a color palette of "Light" (light background with dark text) or "Dark" (dark background with light text) for your site.', $this->slug) . '</li>' .
                '<li>' . __('<strong>Link Color</strong>: You can choose the color used for text links on your site. You can enter the HTML color or hex code, or you can choose visually by clicking the "Select a Color" button to pick from a color wheel.', $this->slug) . '</li>' .
                '<li>' . __('<strong>Default Layout</strong>: You can choose if you want your site&#8217;s default layout to have a sidebar on the left, the right, or not at all.', $this->slug) . '</li>' .
                '</ol>' .
                '<p>' . __('Remember to click "Save Changes" to save any changes you have made to the theme options.', $this->slug) . '</p>';

        $sidebar = '<p><strong>' . __('For more information:', $this->slug) . '</strong></p>' .
                '<p>' . __('<a href="http://codex.wordpress.org/Appearance_Theme_Options_Screen" target="_blank">Documentation on Theme Options</a>', $this->slug) . '</p>' .
                '<p>' . __('<a href="http://wordpress.org/support/" target="_blank">Support Forums</a>', $this->slug) . '</p>';

        $screen = get_current_screen();

        if (method_exists($screen, 'add_help_tab')) {
          // WordPress 3.3
          $screen->add_help_tab(array(
              'title' => __('Overview', $this->slug),
              'id' => 'theme-options-help',
              'content' => $help,
                  )
          );

          $screen->set_help_sidebar($sidebar);
        }
        else {
          // WordPress 3.2
          add_contextual_help($screen, $help . $sidebar);
        }
      }

    }
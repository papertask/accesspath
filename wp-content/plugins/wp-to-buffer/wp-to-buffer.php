<?php
/**
* Plugin Name: WP to Buffer
* Plugin URI: http://www.wpzinc.com/plugins/wp-to-buffer-pro
* Version: 3.3.7
* Author: WP Zinc
* Author URI: http://www.wpzinc.com
* Description: Send WordPress Pages, Posts or Custom Post Types to your Buffer (buffer.com) account for scheduled publishing to social networks.
*/

/**
 * WP to Buffer Class
 * 
 * @package   WP_To_Buffer
 * @author    Tim Carr
 * @version   1.0.0
 * @copyright WP Zinc
 */
class WP_To_Buffer {

    /**
     * Holds the class object.
     *
     * @since   3.1.4
     *
     * @var     object
     */
    public static $instance;

    /**
     * Plugin
     *
     * @since   3.0.0
     *
     * @var     object
     */
    public $plugin = '';

    /**
     * Dashboard
     *
     * @since   3.1.4
     *
     * @var     object
     */
    public $dashboard = '';

    /**
     * Classes
     *
     * @since   3.4.9
     *
     * @var     array
     */
    public $classes = '';

    /**
     * Constructor. Acts as a bootstrap to load the rest of the plugin
     *
     * @since   1.0.0
     */
    public function __construct() {

        // Bail if the Pro version of the Plugin is active
        if ( class_exists( 'WP_To_Buffer_Pro' ) ) {
            return;
        }

        // Plugin Details
        $this->plugin                   = new stdClass;
        $this->plugin->name             = 'wp-to-buffer';
        $this->plugin->filter_name      = 'wp_to_buffer';
        $this->plugin->displayName      = 'WP to Buffer';
        
        $this->plugin->settingsName     = 'wp-to-buffer-pro'; // Settings key - used in both Free + Pro, and for oAuth
        $this->plugin->account          = 'Buffer';
        $this->plugin->version          = '3.3.7';
        $this->plugin->buildDate        = '2018-05-03 18:00:00';
        $this->plugin->requires         = 3.6;
        $this->plugin->tested           = '4.9.5';
        $this->plugin->folder           = plugin_dir_path( __FILE__ );
        $this->plugin->url              = plugin_dir_url( __FILE__ );
        $this->plugin->documentation_url= 'https://www.wpzinc.com/documentation/wordpress-to-buffer-pro';
        $this->plugin->support_url      = 'https://www.wpzinc.com/support';
        $this->plugin->upgrade_url      = 'https://www.wpzinc.com/plugins/wordpress-to-buffer-pro';
        $this->plugin->review_name      = 'wp-to-buffer';
        $this->plugin->review_notice    = sprintf( __( 'Thanks for using %s to schedule your social media statuses on Buffer!', $this->plugin->name ), $this->plugin->displayName );

        // Default Settings
        $this->plugin->publish_default_status  = __( 'New Post: {title} {url}', $this->plugin->name );
		$this->plugin->update_default_status   = __( 'Updated Post: {title} {url}', $this->plugin->name );
    
        // Upgrade Reasons
        $this->plugin->upgrade_reasons = array(
            array(
                __( 'Pinterest', $this->plugin->name ), 
                __( 'Post to your Pinterest boards', $this->plugin->name ),
            ),
            array(
                __( 'Instagram', $this->plugin->name ), 
                __( 'Post Direct to Instagram', $this->plugin->name ),
            ),
            array(
                __( 'Multiple, Customisable Status Messages', $this->plugin->name ), 
                __( 'Each Post Type and Social Network can have multiple, unique status message and settings', $this->plugin->name ),
            ),
            array(
                __( 'Separate Options per Social Network', $this->plugin->name ), 
                __( 'Define different statuses for each Post Type and Social Network', $this->plugin->name ),
            ),
            array(
                __( 'Dynamic Tags', $this->plugin->name ), 
                __( 'Dynamically build status updates with Post, Author and Meta tags', $this->plugin->name ),
            ),
            array(
                __( 'Schedule Statuses', $this->plugin->name ), 
                __( 'Each status update can be added to the start/end of your Buffer queue, posted immediately or scheduled at a specific time', $this->plugin->name ),
            ),
            array(
                __( 'Full Image Control', $this->plugin->name ), 
                __( 'Choose to display WordPress Featured Images with your status updates', $this->plugin->name ),
            ),
            array(
                __( 'Conditional Publishing', $this->plugin->name ), 
                __( 'Require taxonomy term(s) to be present, or not present, for Posts to publish to Buffer', $this->plugin->name ),
            ),
            array(
                __( 'Override Settings on Individual Posts', $this->plugin->name ), 
                __( 'Each Post can have its own Buffer settings', $this->plugin->name ),
            ),
            array(
                __( 'Bulk Publish', $this->plugin->name ), 
                __( 'Publish evergreen WordPress content and revive old posts with the Bulk Publish option', $this->plugin->name ),
            ),
            array(
                __( 'WP-Cron Compatible', $this->plugin->name ), 
                __( 'Optionally enable WP-Cron to send status updates via Cron, speeding up UI performance', $this->plugin->name ),
            ),
        );
    
        // Dashboard Submodule
        if ( ! class_exists( 'WPZincDashboardWidget' ) ) {
            require_once( $this->plugin->folder . '_modules/dashboard/dashboard.php' );
        }
        $this->dashboard = new WPZincDashboardWidget( $this->plugin );

        // Defer loading of Plugin Classes
        add_action( 'init', array( $this, 'initialize' ), 1 );

    }

    /**
     * Initializes required and licensed classes
     *
     * @since   3.4.9
     */
    public function initialize() {

        $this->classes = new stdClass;

        // Initialize required classes
        $this->classes->admin     = new WP_To_Social_Pro_Admin( self::$instance );
        $this->classes->ajax      = new WP_To_Social_Pro_AJAX( self::$instance );
        $this->classes->api       = new WP_To_Social_Pro_Buffer_API( self::$instance );
        $this->classes->common    = new WP_To_Social_Pro_Common( self::$instance );
        $this->classes->install   = new WP_To_Social_Pro_Install( self::$instance );
        $this->classes->log       = new WP_To_Social_Pro_Log( self::$instance );   
        $this->classes->post      = new WP_To_Social_Pro_Post( self::$instance );
        $this->classes->publish   = new WP_To_Social_Pro_Publish( self::$instance );
        $this->classes->settings  = new WP_To_Social_Pro_Settings( self::$instance );

        // Run the migration routine from Free + Pro v2.x --> Pro v3.x
        if ( is_admin() ) {
            $this->classes->settings->migrate_settings();
        }
        
    }

    /**
     * Returns the given class
     *
     * @since   3.4.9
     *
     * @param   string  $name   Class Name
     */
    public function get_class( $name ) {

        return $this->classes->{ $name };

    }

    /**
     * Returns the singleton instance of the class.
     *
     * @since   3.1.4
     *
     * @return  object Class.
     */
    public static function get_instance() {

        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof self ) ) {
            self::$instance = new self;
        }

        return self::$instance;

    }

}

/**
 * Define the autoloader for this Plugin
 *
 * @since   3.4.7
 *
 * @param   string  $class_name     The class to load
 */
function WP_To_Buffer_Autoloader( $class_name ) {

    // Define the required start of the class name
    $class_start_name = array(
        'WP_To_Social_Pro',
    );

    // Get the number of parts the class start name has
    $class_parts_count = count( explode( '_', $class_start_name[0] ) );

    // Break the class name into an array
    $class_path = explode( '_', $class_name );

    // Bail if it's not a minimum length
    if ( count( $class_path ) < $class_parts_count ) {
        return;
    }

    // Build the base class path for this class
    $base_class_path = '';
    for ( $i = 0; $i < $class_parts_count; $i++ ) {
        $base_class_path .= $class_path[ $i ] . '_';
    }
    $base_class_path = trim( $base_class_path, '_' );

    // Bail if the first parts don't match what we expect
    if ( ! in_array( $base_class_path, $class_start_name ) ) {
        return;
    }

    // Define the file name we need to include
    $file_name = strtolower( implode( '-', array_slice( $class_path, $class_parts_count ) ) ) . '.php';

    // Define the paths with file name we need to include
    $include_paths = array(
        dirname( __FILE__ ) . '/includes/admin/' . $file_name,
        dirname( __FILE__ ) . '/includes/global/' . $file_name,
        dirname( __FILE__ ) . '/vendor/includes/admin/' . $file_name,
        dirname( __FILE__ ) . '/vendor/includes/global/' . $file_name,
    );

    // Iterate through the include paths to find the file
    foreach ( $include_paths as $path_file ) {
        if ( file_exists( $path_file ) ) {
            require_once( $path_file );
            return;
        }
    }

}
spl_autoload_register( 'WP_To_Buffer_Autoloader' );

/**
 * Runs the installation and update routines when the plugin is activated.
 *
 * @since   3.0.0
 *
 * @param   bool    $network_wide   Is network wide activation
 */
function wp_to_buffer_activate( $network_wide = false ) {

    // Initialise Plugin
    $wp_to_buffer = WP_To_Buffer::get_instance();
    $wp_to_buffer->initialize();

    // Check if we are on a multisite install, activating network wide, or a single install
    if ( is_multisite() && $network_wide ) {
        // Multisite network wide activation
        // Iterate through each blog in multisite, creating table
        $sites = wp_get_sites( array( 
            'limit' => 0 
        ) );
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            $wp_to_buffer->get_class( 'install' )->install();
            restore_current_blog();
        }
    } else {
        $wp_to_buffer->get_class( 'install' )->install();
    }

}
register_activation_hook( __FILE__, 'wp_to_buffer_activate' );

/**
 * Runs the installation and update routines when the plugin is activated
 * on a WPMU site.
 *
 * @since   3.0.0
 *
 * @param   int     $blog_id    Site ID
 */
function wp_to_buffer_activate_wpmu_site( $blog_id ) {

    // Initialise Plugin
    $wp_to_buffer = WP_To_Buffer::get_instance();
    $wp_to_buffer->initialize();

    // Run installation routine
    switch_to_blog( $blog_id );
    $wp_to_buffer->get_class( 'install' )->install();
    restore_current_blog();

}
add_action( 'activate_wpmu_site', 'wp_to_buffer_activate_wpmu_site' );

// Finally, initialize the main plugin
$wp_to_buffer = WP_To_Buffer::get_instance();
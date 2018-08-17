<?php
/**
 * Administration class
 * 
 * @package WP_To_Social_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Social_Pro_Admin {

    /**
     * Holds the base class object.
     *
     * @since   3.2.0
     *
     * @var     object
     */
    public $base;

    /**
     * Holds the success and error messages
     *
     * @since   3.2.6
     *
     * @var     array
     */
    public $notices = array(
        'success'   => array(),
        'error'     => array(),
    );

    /**
     * Constructor
     *
     * @since   3.0.0
     *
     * @param   object $base    Base Plugin Class
     */
    public function __construct( $base ) {

        // Store base class
        $this->base = $base;

        // Actions
        add_action( 'init', array( $this, 'oauth' ) );
        add_action( 'admin_notices', array( $this, 'check_plugin_setup' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_css' ) );
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'plugins_loaded', array( $this, 'load_language_files' ) );

    }

    /**
     * Stores the access token if supplied, showing a success message
     * Displays any errors from the oAuth process
     *
     * @since   3.3.3
     */
    public function oauth() {

        // If we've returned from the oAuth process and an error occured, add it to the notices
        if ( isset( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-error' ] ) ) {
            switch( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-error' ] ) {
                /**
                 * Access Denied
                 * - User denied our app access
                 */
                case 'access_denied':
                    $this->notices['error'][] = sprintf( __( 'You did not grant our Plugin access to your %s account. We are unable to post to %s until you do this. Please click on the Authorize Plugin button.', $this->base->plugin->name ), $this->base->plugin->account, $this->base->plugin->account );
                    break;

                /**
                 * Invalid Grant
                 * - A parameter sent by the oAuth gateway is wrong
                 */
                case 'invalid_grant':
                    $this->notices['error'][] = sprintf( __( 'We were unable to complete authentication with %s.  Please try again, or <a href="%s" target="_blank">contact us for support</a>.', $this->base->plugin->name ), $this->base->plugin->account, $this->base->plugin->support_url );
                    break;

                /**
                 * Expired Token
                 * - The oAuth gateway did not exchange the code for an access token within 30 seconds
                 */
                case 'expired_token':
                    $this->notices['error'][] = sprintf( __( 'The oAuth process has expired.  Please try again, or <a href="%s" target="_blank">contact us for support</a> if this issue persists.', $this->base->plugin->name ), $this->base->plugin->support_url );
                    break;

                /**
                 * Other Error
                 */
                default:
                    $this->notices['error'][] = $_REQUEST[ $this->base->plugin->settingsName . '-oauth-error' ];
                    break;
            }
        }

        // If an Access Token is included in the request, store it and show a success message
        if ( isset( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-access-token' ] ) ) {
            // Define expiry
            $expiry = sanitize_text_field( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-expires' ] );
            if ( $expiry > 0 ) {
                $expiry = strtotime( '+' . sanitize_text_field( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-expires' ] ) . ' seconds' );
            }
            // Setup API
            $this->base->get_class( 'api' )->set_tokens(
                sanitize_text_field( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-access-token' ] ),
                sanitize_text_field( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-refresh-token' ] ),
                $expiry
            );

            // Test Tokens
            $user = $this->base->get_class( 'api' )->user();
            
            // If something went wrong, show an error
            if ( is_wp_error( $user ) ) {
                $this->notices['error'][] = $user->get_error_message();
                return;
            }

            // Test worked! Save Tokens and Expiry
            $this->base->get_class( 'settings' )->update_tokens( 
                sanitize_text_field( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-access-token' ] ),
                sanitize_text_field( $_REQUEST[ $this->base->plugin->settingsName . '-oauth-refresh-token' ] ),
                $expiry
            );

            // Show success message
            $this->notices['success'][] = sprintf( __( 'Thanks, %s! You\'ve authorized our Plugin access to post updates to your %s account.<br />Please now configure the Post Type(s) you want to send to your %s account below.', $this->base->plugin->name ), 
                ( isset( $user->name ) ? $user->name : $user->fullName ), 
                $this->base->plugin->account, 
                $this->base->plugin->account
            );
        }

    }

    /**
     * Checks that the oAuth authorization flow has been completed, and that
     * at least one Post Type with one Social Media account has been enabled.
     *
     * Displays a dismissible WordPress notification if this has not been done.
     *
     * @since   1.0.0
     */
    public function check_plugin_setup() {

        // Check for access token
        $access_token = $this->base->get_class( 'settings' )->get_access_token();
        if ( empty( $access_token ) ) {
            ?>
            <div class="notice notice-error">
                <p>
                    <?php 
                    echo sprintf( 
                        __( '%s needs to be authorized with %s before you can start sending Posts to %s.  <a href="%s">Click here to Authorize.</a>', $this->base->plugin->name ),
                        $this->base->plugin->displayName,
                        $this->base->plugin->account,
                        $this->base->plugin->account, 
                        $this->base->get_class( 'api' )->get_oauth_url()
                    );
                    ?>
                </p>
            </div>
            <?php

            // Don't output any further errors
            return;
        }

    }

    /**
     * Register and enqueue any JS and CSS for the WordPress Administration
     *
     * @since 1.0.0
     */
    public function admin_scripts_css() {

        global $id, $post;

        // Get current screen
        $screen = get_current_screen();

        // CSS - always load
        // Menu Icon is inline, because when Gravity Forms no conflict mode is ON, it kills all enqueued styles,
        // which results in a large menu SVG icon displaying.
        ?>
        <style type="text/css">
            li.toplevel_page_<?php echo $this->base->plugin->settingsName; ?>-settings a div.wp-menu-image, li.toplevel_page_<?php echo $this->base->plugin->name; ?> a div.wp-menu-image {
                background: url(<?php echo $this->base->plugin->url; ?>/vendor/assets/images/icons/<?php echo strtolower( $this->base->plugin->account ); ?>-dark.svg) center no-repeat;
                background-size: 16px 16px;
            }
            li.toplevel_page_<?php echo $this->base->plugin->settingsName; ?>-settings a div.wp-menu-image img, li.toplevel_page_<?php echo $this->base->plugin->name; ?> a div.wp-menu-image img {
                display: none;
            }

            body.admin-color-fresh li.toplevel_page_<?php echo $this->base->plugin->settingsName; ?>-settings a div.wp-menu-image, 
            body.admin-color-fresh li.toplevel_page_<?php echo $this->base->plugin->name; ?> a div.wp-menu-image,
            body.admin-color-midnight li.toplevel_page_<?php echo $this->base->plugin->settingsName; ?>-settings a div.wp-menu-image, 
            body.admin-color-midnight li.toplevel_page_<?php echo $this->base->plugin->name; ?> a div.wp-menu-image {
                background: url(<?php echo $this->base->plugin->url; ?>/vendor/assets/images/icons/<?php echo strtolower( $this->base->plugin->account ); ?>-light.svg) center no-repeat;
                background-size: 16px 16px;
            }
        </style>
        <?php
        wp_enqueue_style( $this->base->plugin->name, $this->base->plugin->url . 'vendor/assets/css/admin.css', array(), $this->base->plugin->version );
        
        // Don't load anything else if we're not on a Plugin screen
        if ( ! isset( $screen->base ) ) {
            return;
        }
        if ( strpos ( $screen->base, $this->base->plugin->name ) === false && $screen->base != 'post' ) {
            return;
        }
    
        // Plugin Admin
        // These scripts are registered in _modules/dashboard/dashboard.php
        wp_enqueue_script( 'wpzinc-admin-conditional' );
        wp_enqueue_script( 'wpzinc-admin-selectize' );
        wp_enqueue_script( 'wpzinc-admin-tabs' );
        wp_enqueue_script( 'wpzinc-admin-tags' );
        wp_enqueue_script( 'wpzinc-admin' ); 

        // JS
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'jquery-ui-progressbar' );
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_script( $this->base->plugin->name . '-admin', $this->base->plugin->url . 'vendor/assets/js/min/admin-min.js', array( 'jquery' ), $this->base->plugin->version, true );
        wp_localize_script( $this->base->plugin->name . '-admin', 'wp_to_social_pro', array(
            'ajax'                      => admin_url( 'admin-ajax.php' ),
            'clear_log_message'         => __( 'Are you sure you want to clear the log file associated with this Post?', $this->base->plugin->name ),
            'clear_log_nonce'           => wp_create_nonce( $this->base->plugin->name . '-clear-log' ),
            'clear_log_completed'       => sprintf( __( 'No status updates have been sent to %s.', $this->base->plugin->name ), $this->base->plugin->account ),
            'post_id'                   => ( isset( $post->ID ) ? $post->ID : (int) $id ),
        ) );
        
    }
    
    /**
     * Add the Plugin to the WordPress Administration Menu
     *
     * @since   1.0.0
     */
    public function admin_menu() {

        // Menus
        add_menu_page( $this->base->plugin->displayName, $this->base->plugin->displayName, 'manage_options', $this->base->plugin->name . '-settings', array( $this, 'settings_screen' ), $this->base->plugin->url . 'vendor/assets/images/icons/' . strtolower( $this->base->plugin->account ) . '-dark.svg' );
        add_submenu_page( $this->base->plugin->name . '-settings', __( 'Settings', $this->base->plugin->name ), __( 'Settings', $this->base->plugin->name ), 'manage_options', $this->base->plugin->name . '-settings', array( $this, 'settings_screen' ) );
        add_submenu_page( $this->base->plugin->name . '-settings', __( 'Upgrade', $this->base->plugin->name ), __( 'Upgrade', $this->base->plugin->name ), 'manage_options', $this->base->plugin->name . '-upgrade', array( $this, 'upgrade_screen' ) );

    }

    /**
     * Upgrade Screen
     *
     * @since 3.2.5
     */
    public function upgrade_screen() {   
        // We never reach here, as we redirect earlier in the process
    }

    /**
     * Outputs the Settings Screen
     *
     * @since   3.0.0
     */
    public function settings_screen() {

        // Maybe disconnect
        if ( isset( $_GET[ $this->base->plugin->name . '-disconnect' ] ) ) {
            $result = $this->disconnect();
            if ( is_string( $result ) ) {
                // Error - add to array of errors for output
                $this->notices['error'][] = $result; 
            } elseif ( $result === true ) {
                // Success
                $this->notices['success'][] = sprintf( __( '%s account disconnected successfully.', $this->base->plugin->name ), $this->base->plugin->account ); 
            }
        }

        // Maybe save settings
        $result = $this->save_settings();
        if ( is_string( $result ) ) {
            // Error - add to array of errors for output
            $this->notices['error'][] = $result;
        } elseif ( $result === true ) {
            // Success
            $this->notices['success'][] = __( 'Settings saved successfully.', $this->base->plugin->name ); 
        }

        // Either define the oAuth URL, or set the access token
        $access_token = $this->base->get_class( 'settings' )->get_access_token();
        $refresh_token = $this->base->get_class( 'settings' )->get_refresh_token();
        $expires = $this->base->get_class( 'settings' )->get_token_expires();
        if ( ! empty( $access_token ) ) {
            $this->base->get_class( 'api' )->set_tokens( $access_token, $refresh_token, $expires );
        } else {
            $oauth_url = $this->base->get_class( 'api' )->get_oauth_url();
        }

        // Get Profiles
        // Display an error if we couldn't fetch the profiles
        $profiles = $this->base->get_class( 'api' )->profiles( true, $this->base->get_class( 'common' )->get_transient_expiration_time() );
        if ( is_wp_error( $profiles ) ) {
            // If the error is a 401, the user revoked access to the plugin
            // Disconnect the Plugin, and explain why this happened
            if ( $profiles->get_error_code() == 401 ) {
                // Disconnect the Plugin
                $this->disconnect();

                // Fetch a new oAuth URL
                $oauth_url = $this->base->get_class( 'api' )->get_oauth_url();

                // Display an error message
                $this->notices['error'][] = sprintf( __( 'Hmm, it looks like you revoked access to %s through your %s account  This means we can no longer post updates to your social networks.  To re-authorize, click the Authorize Plugin button.', $this->base->plugin->name ), $this->base->plugin->displayName, $this->base->plugin->account );
            } else {
                // Some other error
                $this->notices['error'][] = $profiles->get_error_message();
            }
        }

        // Get Post Types, Image Options and Roles
        $post_types     = $this->base->get_class( 'common' )->get_post_types();

        // Get URL parameters
        $tab            = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'auth' );
        $post_type      = ( isset( $_GET['type'] ) ? $_GET['type'] : '' );
        if ( ! empty( $post_type ) ) {
            // Run profiles through role restriction
            $tags               = $this->base->get_class( 'common' )->get_tags( $post_type );
        }

        // Get Schedule Options and Post Actions
        $schedule                   = $this->base->get_class( 'common' )->get_schedule_options();
        $post_actions               = $this->base->get_class( 'common' )->get_post_actions();
  
        // Load View
        include_once( $this->base->plugin->folder . 'vendor/views/settings.php' ); 
        
    }

    /**
     * Helper method to get the setting value from the plugin settings
     *
     * @since   3.0.0
     *
     * @param   string    $type         Setting Type
     * @param   string    $keys         Setting Key(s)
     * @param   mixed     $default      Default Value if Setting does not exist
     * @return  mixed                   Value
     */
    public function get_setting( $type = '', $key = '', $default = '' ) {

        // Post Type Setting or Bulk Setting
        if ( post_type_exists( $type ) || $type == 'bulk' ) {
            return $this->base->get_class( 'settings' )->get_setting( $type, $key, $default );
        }

        // Access token
        if ( $key == 'access_token' ) {
            return $this->base->get_class( 'settings' )->get_access_token();
        }

        // Roles
        if ( $type == 'roles' ) {
            return $this->base->get_class( 'settings' )->get_setting( $type, $key, $default );
        }

        // Setting
        return $this->base->get_class( 'settings' )->get_option( $key, $default );

    }

    /**
     * Disconnect by removing the access token
     *
     * @since   3.0.0
     *
     * @return  string Result
     */
    public function disconnect() {

        return $this->base->get_class( 'settings' )->delete_access_token();

    }

    /**
     * Helper method to save settings
     *
     * @since   3.0.0
     *
     * @return  mixed Error String on error, true on success
     */
    public function save_settings() {

        // Bail if security checks fail
        $result = $this->validate_nonce();
        if ( $result != true ) {
            return $result;
        }

        // Get URL parameters
        $tab            = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'auth' );
        $post_type      = ( isset( $_GET['type'] ) ? $_GET['type'] : '' );
        
        switch ( $tab ) {
            /**
             * Authentication
             */
            case 'auth':
                // oAuth settings are now handled by this class' oauth() function
                // Save other Settings
                $this->base->get_class( 'settings' )->update_option( 'cron', ( isset( $_POST['cron'] ) ? 1 : 0 ) );
                $this->base->get_class( 'settings' )->update_option( 'log', ( isset( $_POST['log'] ) ? 1 : 0 ) );
                $this->base->get_class( 'settings' )->update_option( 'override', ( isset( $_POST['override'] ) ? sanitize_text_field( $_POST['override'] ) : 0 ) );
                $this->base->get_class( 'settings' )->update_option( 'image_custom', ( isset( $_POST['image_custom'] ) ? absint( $_POST['image_custom'] ) : 0 ) );
                $this->base->get_class( 'settings' )->update_option( 'image_dimensions', ( isset( $_POST['image_dimensions'] ) ? 1 : 0 ) );
                $this->base->get_class( 'settings' )->update_option( 'restrict_roles', ( isset( $_POST['restrict_roles'] ) ? 1 : 0 ) );
                $this->base->get_class( 'settings' )->update_settings( 'roles', ( isset( $_POST['roles'] ) ? $_POST['roles'] : array() ) );

                return true;

                break;

            /**
             * Post Type
             */
            default:
                // Save Settings for this Post Type
                return $this->base->get_class( 'settings' )->update_settings( $post_type, $_POST[ $this->base->plugin->name ] );

                break;
        }

    }

    /**
     * Validates the nonce field on submitted POST data
     *
     * @since   3.4.7
     *
     * @return  mixed   WP_Error | boolean
     */
    private function validate_nonce() {

        // Check if a POST request was made
        if ( ! isset( $_POST['submit'] ) ) {
            return false;
        }

        // Missing nonce 
        if ( ! isset( $_POST[ $this->base->plugin->name . '_nonce' ] ) ) { 
            return __( 'Nonce field is missing. Settings NOT saved.', $this->base->plugin->name );
        }

        // Invalid nonce
        if ( ! wp_verify_nonce( $_POST[ $this->base->plugin->name . '_nonce' ], $this->base->plugin->name ) ) {
            return __('Invalid nonce specified. Settings NOT saved.', $this->base->plugin->name );
        }

        return true;

    }

    /**
     * Loads plugin textdomain
     *
     * @since   3.0.0
     */
    public function load_language_files() {

        load_plugin_textdomain( $this->base->plugin->name, false, $this->base->plugin->name . '/languages/' );

    }

}
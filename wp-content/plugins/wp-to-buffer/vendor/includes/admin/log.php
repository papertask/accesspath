<?php
/**
 * Logging class
 * 
 * @package WP_To_Social_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Social_Pro_Log {

    /**
     * Holds the base class object.
     *
     * @since   3.2.0
     *
     * @var     object
     */
    public $base;

    /**
     * Holds the meta key
     *
     * @since   3.4.7
     *
     * @var     string
     */
    private $meta_key = '';

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

        // Define post meta key for storing logging
        $this->meta_key = '_' . str_replace( '-', '_', $this->base->plugin->settingsName ) . '_log';

        // Actions
        add_action( 'admin_menu', array( $this, 'admin_meta_boxes' ) );
        add_action( 'wp_loaded', array( $this, 'export_log' ) );

    }

    /**
     * Adds Metaboxes to Post Edit Screens
     *
     * @since   3.0.0
     */
    public function admin_meta_boxes() {

        // Only load if Logging is enabled
        if ( ! $this->base->get_class( 'settings' )->get_option( 'log' ) ) {
            return;
        }

        // Get Post Types
        $post_types = $this->base->get_class( 'common' )->get_post_types();

        // Add meta boxes for each
        foreach ( $post_types as $post_type => $post_type_obj ) {
            add_meta_box( $this->base->plugin->name . '-log', sprintf( __( '%s Log', $this->base->plugin->name ), $this->base->plugin->displayName ), array( $this, 'meta_log' ), $post_type, 'normal', 'low' );   
        }

    }

    /**
     * Outputs the plugin's log of existing status update calls made to the API
     *
     * @since   3.0.0
     *
     * @param   WP_Post     $post   Post
     */
    public function meta_log( $post ) {

        // Setup API
        $this->base->get_class( 'api' )->set_tokens( 
            $this->base->get_class( 'settings' )->get_access_token(),
            $this->base->get_class( 'settings' )->get_refresh_token(),
            $this->base->get_class( 'settings' )->get_token_expires()
        );

        // Get log and profiles
        $log = $this->get_log( $post->ID );
        $profiles = $this->base->get_class( 'api' )->profiles( false, $this->base->get_class( 'common' )->get_transient_expiration_time() );

        // Load View
        include_once( $this->base->plugin->folder . 'vendor/views/log.php' ); 

    }

    /**
     * Retrieves the log for the given Post ID
     *
     * @since   3.0.0
     *
     * @param   int     $post_id    Post ID
     * @return  array               Log
     */
    public function get_log( $post_id ) {

        // Get log
        $log = get_post_meta( $post_id, $this->meta_key, true );

        // Allow filtering
        $log = apply_filters( $this->base->plugin->filter_name . '_get_log', $log, $post_id );

        // Return
        return $log;

    }

    /**
     * Stores the log results against the given Post ID
     *
     * @since   3.0.0
     *
     * @param   int    $post_id     Post ID
     * @param   array  $log         Log Entry / Log Entries
     * @return  bool                Success
     */
    public function update_log( $post_id, $log ) {

        // Get current log
        $old_log = $this->get_log( $post_id );

        // If log exist, merge it with the new log
        if ( $old_log !== false && is_array( $old_log ) ) {
            $log = array_merge( $old_log, $log );
        }

        // Allow devs to filter before saving
        $log = apply_filters( $this->base->plugin->filter_name . '_update_log', $log, $post_id );

        // update_option will return false if no changes were made, so we can't rely on this
        update_post_meta( $post_id, $this->meta_key, $log );
        
        return true;
    }

    /**
     * Exports a Post's API log file in JSON format
     *
     * @since   3.0.0
     */
    public function export_log() {

        // Check the user requested a log
        if ( ! isset( $_GET[ $this->base->plugin->name . '-export-log' ] ) ) {
            return;
        }

        // Get log
        $log = $this->get_log( absint( $_GET['post'] ) );

        // Build JSON
        $json = json_encode( $log );
        
        // Export
        header( "Content-type: application/x-msdownload" );
        header( "Content-Disposition: attachment; filename=log.json" );
        header( "Pragma: no-cache" );
        header( "Expires: 0" );
        echo $json;
        exit();
                
    }

    /**
     * Clears a Post's API log
     *
     * @since   3.0.0
     *
     * @param   int     $post_id    Post ID
     */
    public function clear_log( $post_id = 0 ) {

        // If no Post ID has been specified, check the request
        if ( $post_id == 0 && isset( $_REQUEST['post'] ) ) {
            $post_id = absint( $_REQUEST['post'] );
        }

        // Bail if no Post ID
        if ( ! $post_id ) {
            return false;
        }

        // Delete log
        return delete_post_meta( $post_id, $this->meta_key );

    }

}
<?php
/**
 * Post class
 * 
 * @package WP_To_Social_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Social_Pro_Publish {

    /**
     * Holds the base class object.
     *
     * @since   3.2.4
     *
     * @var     object
     */
    public $base;

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
        add_action( 'wp_loaded', array( $this, 'register_publish_hooks' ), 1 );
        add_action( $this->base->plugin->name, array( $this, 'publish' ), 1, 2 );

    }

    /**
     * Registers publish hooks against all public Post Types
     *
     * @since   3.0.0
     */
    public function register_publish_hooks() {

        add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
        
    }

    /**
     * Handles all Post transitions, checking to see whether a Post is going to be published
     * or updated.
     *
     * @since   3.1.6
     *
     * @param   string      $new_status     New Status
     * @param   string      $old_status     Old Status
     * @param   WP_Post     $post           Post
     */
    public function transition_post_status( $new_status, $old_status, $post ) {

        // Bail if the Post Type isn't public
        // This prevents the rest of this routine running on e.g. ACF Free, when saving Fields (which results in Field loss)
        $post_types = array_keys( $this->base->get_class( 'common' )->get_post_types() );
        if ( ! in_array( $post->post_type, $post_types ) ) {
            return;
        }

        // New Post Screen loading
        // Draft saved
        if ( $new_status == 'auto-draft' || $new_status == 'draft' || $new_status == 'inherit' || $new_status == 'trash' ) {
            return;
        }

        // Publish
        if ( $new_status == 'publish' && $new_status != $old_status ) {
            $result = $this->publish( $post->ID, 'publish' );
        }

        // Update
        if ( $new_status == 'publish' && $old_status == 'publish' ) {
            $result = $this->publish( $post->ID, 'update' );
        }

        // If no result, bail
        if ( ! isset( $result ) ) {
            return;
        }

        // If no errors, return
        if ( ! is_wp_error( $result ) ) {
            return;
        }

        // If logging is disabled, return
        $log_enabled = $this->base->get_class( 'settings' )->get_option( 'log' );
        if ( ! $log_enabled ) {
            return;
        }

        // Add the error to the log so that the user can see why no statuses were sent to API
        $this->base->get_class( 'log' )->update_log( $post->ID, array(
            array(
                'date'              => strtotime( 'now' ),
                'success'           => false,
                'message'           => $result->get_error_message(),
            ),
        ) );
    
    }

    /**
     * Main function. Called when any Page, Post or CPT is published or updated
     *
     * @since   3.0.0
     *
     * @param   int         $post_id                Post ID
     * @param   string      $action                 Action (publish|update)
     * @param   bool        $is_bulk_publish_action Is Bulk Publish Action
     * @return  mixed                               WP_Error | API Results array
     */
    public function publish( $post_id, $action, $is_bulk_publish_action = false ) {

        // Get Post
        global $post;
        $post = get_post( $post_id );
        if ( ! $post ) {
            return new WP_Error( 'no_post', sprintf( __( 'No WordPress Post could be found for Post ID %s', $this->base->plugin->name ), $post_id ) );
        }

        // Determine post type
        $post_type = $post->post_type;

        // Use Plugin Settings
        $settings = $this->base->get_class( 'settings' )->get_settings( $post_type );

        // Check a valid access token exists
        $access_token = $this->base->get_class( 'settings' )->get_access_token();
        $refresh_token = $this->base->get_class( 'settings' )->get_refresh_token();
        $expires = $this->base->get_class( 'settings' )->get_token_expires();
        if ( ! $access_token ) {
            return new WP_Error( 'no_access_token', sprintf( __( 'The Plugin has not been authorized with %s! Go to %s > Settings to setup the plugin.', $this->base->plugin->name ), $this->base->plugin->account, $this->base->plugin->displayName ) );
        }
        
        // Check settings exist
        // If not, this means the CPT or Post-level settings have not been configured, so we
        // don't need to do anything
        if ( ! $settings ) {
            return new WP_Error( 'no_settings', sprintf( __( 'No settings have been defined! Go to %s > Settings to setup the plugin.', $this->base->plugin->name ), $this->base->plugin->displayName ) );
        }

        // Setup API
        $this->base->get_class( 'api' )->set_tokens( $access_token, $refresh_token, $expires );

        // Get Profiles
        $profiles = $this->base->get_class( 'api' )->profiles( false, $this->base->get_class( 'common' )->get_transient_expiration_time() );

        // Array for storing statuses we'll send to the API
        $statuses = array();

        // Iterate through each social media profile
        foreach ( $settings as $profile_id => $profile_settings ) {

            // Get detailed settings from Post or Plugin
            // Use Plugin Settings
            $profile_enabled = $this->base->get_class( 'settings' )->get_setting( $post_type, '[' . $profile_id . '][enabled]', 0 );
            $profile_override = $this->base->get_class( 'settings' )->get_setting( $post_type, '[' . $profile_id . '][override]', 0 );

            // Either use override settings (or if Pinterest, always use override settings)
            if ( $profile_override || ( isset( $profiles[ $profile_id ] ) && $profiles[ $profile_id ]['service'] == 'pinterest' ) ) {
                $action_enabled = $this->base->get_class( 'settings' )->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][enabled]', 0 );
                $status_settings = $this->base->get_class( 'settings' )->get_setting( $post_type, '[' . $profile_id . '][' . $action . '][status]', array() );
            } else {
                $action_enabled = $this->base->get_class( 'settings' )->get_setting( $post_type, '[default][' . $action . '][enabled]', 0 );
                $status_settings = $this->base->get_class( 'settings' )->get_setting( $post_type, '[default][' . $action . '][status]', array() );
            }
           

            // Check if this profile is enabled
            if ( ! $profile_enabled ) {
                continue;
            }

            // Check if this profile's action is enabled
            if ( ! $action_enabled ) {
                continue;
            }

            // Determine which social media service this profile ID belongs to
            foreach ( $profiles as $profile ) {
                if ( $profile['id'] == $profile_id ) {
                    $service = $profile['service'];
                    break;
                }
            }

            // Iterate through each Status
            foreach ( $status_settings as $index => $status ) {
                $statuses[] = $this->build_args( $post, $profile_id, $service, $status, $action );
            }

        }

        // Check if any statuses exist
        // If not, exit
        if ( count( $statuses ) == 0 ) {
            return new WP_Error( 'no_statuses', sprintf( __( 'No settings are defined in the Plugin Settings for sending this Post Type to %s on %s. If you are expecting this Post Type to be sent to %s on %s, please check the Plugin Settings.', $this->base->plugin->name ), $this->base->plugin->account, $action, $this->base->plugin->account, $action ) );
        }

        // Allow developers to filter statuses before sending them
        $statuses = apply_filters( $this->base->plugin->filter_name . '_publish_statuses', $statuses, $post_id, $action );

        // Send status messages to the API
        $results = $this->send( $statuses, $post_id, $action, $profiles );

        // If no results, we're finished
        if ( empty( $results ) || count( $results ) == 0 ) {
            return;
        }

        // Check each result, to see if an error occured
        $errors = false;
        foreach ( $results as $result ) {
            if ( is_wp_error( $result ) ) {
                $errors = true;
                break;
            }
        }

        // Request that the user review the plugin. Notification displayed later,
        // can be called multiple times and won't re-display the notification if dismissed.
        if ( ! $errors ) {
            $this->base->dashboard->request_review();
        }

    }

    /**
     * Helper method to build arguments and create a status via the API
     *
     * @since   3.0.0
     *
     * @param   obj     $post                       Post
     * @param   string  $profile_id                 Profile ID
     * @param   string  $service                    Service
     * @param   array   $status                     Status Settings
     * @param   string  $action                     Action (publish|update)
     * @param   bool    $is_bulk_publish_action     Is Bulk Publish Action
     * @return  bool                                Success
     */
    private function build_args( $post, $profile_id, $service, $status, $action, $is_bulk_publish_action = false ) {

        // Build each API argument
        // Profile ID
        $args = array(
            'profile_ids'   => array( $profile_id ),
        );

        // Get the character limit for the status text based on the profile's service
        $character_limit = $this->base->get_class( 'common' )->get_character_limit( $service );

        // Text
        $args['text'] = $this->parse_text( $post, $status['message'], $character_limit );

        // Shorten URLs
        $args['shorten'] = true;

        // Schedule
        switch( $status['schedule'] ) {

            case 'queue_bottom':
                // This is the default for the API, so nothing more to do here
                break;

            case 'queue_top':
                $args['top'] = true;
                break;

            case 'now':
                $args['now'] = true;
                break;

            case 'custom':
                // Check days, hours, minutes are set
                if ( empty( $status['days'] ) ) {
                    $status['days'] = 0;
                }
                if ( empty( $status['hours'] ) ) {
                    $status['hours'] = 0;
                }
                if ( empty( $status['minutes'] ) ) {
                    $status['minutes'] = 0;
                }
                
                // Define the Post Date, depending on the action
                $post_date = ( ( $action == 'publish' ) ? $post->post_date_gmt : $post->post_modified_gmt );
                
                // If this status is for Bulk Publish, set the Post Date to now
                if ( $is_bulk_publish_action ) {
                    $post_date = date( 'Y-m-d H:i:s' );
                }

                // Add days, hours and minutes
                $timestamp = strtotime( '+' . $status['days'] . ' days ' . $status['hours'] . ' hours ' . $status['minutes'] . ' minutes', strtotime( $post_date ) );
                $args['scheduled_at'] = date( 'Y-m-d H:i:s', $timestamp );
                break;

            case 'custom_field':
                // Check days, hours, minutes are set
                if ( empty( $status['days'] ) ) {
                    $status['days'] = 0;
                }
                if ( empty( $status['hours'] ) ) {
                    $status['hours'] = 0;
                }
                if ( empty( $status['minutes'] ) ) {
                    $status['minutes'] = 0;
                }

                // Define the Post Date, depending on the action
                $post_date = ( ( $action == 'publish' ) ? $post->post_date_gmt : $post->post_modified_gmt );
                
                // If this status is for Bulk Publish, set the Post Date to now
                if ( $is_bulk_publish_action ) {
                    $post_date = date( 'Y-m-d H:i:s' );
                }

                // Add or subtract days, hours and minutes to the Custom Value
                $symbol = ( $status['schedule_custom_field_relation'] == 'before' ? '-' : '+' );
                $timestamp = strtotime( $symbol . $status['days'] . ' days ' . $status['hours'] . ' hours ' . $status['minutes'] . ' minutes', strtotime( $post_date ) );
                $args['scheduled_at'] = date( 'Y-m-d H:i:s', $timestamp );
                break;

        }

        // Media
        // By default, fetch from OpenGraph by specifying the Post Link
        $args['media'] = array(
            'link' => rtrim( get_permalink( $post->ID ), '/' ),
        );

        // If a Featured Image is present, use that instead
        $featured_image_id = get_post_thumbnail_id( $post->ID );
        if ( $featured_image_id > 0 ) {
            $featured_image = wp_get_attachment_image_src( $featured_image_id, 'large' );
            $featured_image_thumbnail = wp_get_attachment_image_src( $featured_image_id, 'thumbnail' );

            if ( is_array( $featured_image ) ) {
                $args['media'] = array(
                    'title'         => $post->post_title,
                    'description'   => $post->post_excerpt,
                    'picture'       => $featured_image[0],
                    'thumbnail'     => $featured_image_thumbnail[0],
                );
            }
        }

        // Pinterest
        if ( $service == 'pinterest' ) {
            $args['subprofile_ids'] = array(
                $status['sub_profile'],
            );
            $args['source_url'] = get_permalink( $post->ID );
        }

        // Allow devs to filter before returning
        $args = apply_filters( $this->base->plugin->filter_name . '_publish_build_args', $args, $post, $profile_id, $service, $status );

        // Return args
        return $args;

    }

    /**
     * Populates the status message by replacing tags with Post/Author data
     *
     * @since   3.0.0
     *
     * @param   WP_Post     $post               Post
     * @param   string      $message            Status Message to parse
     * @param   int         $character_limit    Character Limit
     * @return  string                          Parsed Status Message
     */
    public function parse_text( $post, $message, $character_limit = 0 ) {

        // 1. Get author
        $author = get_user_by( 'id', $post->post_author );
        
        // 2. Check if we have an excerpt. If we don't (i.e. it's a Page or CPT with no excerpt functionality), we need
        // to create an excerpt
        if ( empty( $post->post_excerpt ) ) {
            $excerpt = strip_tags( wp_trim_words( strip_shortcodes( $post->post_content ) ) );
        } else {
            $excerpt = strip_tags( wp_trim_words( strip_shortcodes( $post->post_excerpt ) ) );
        }

        // 2a. Decode certain entities for FB + G+ compatibility
        $excerpt = html_entity_decode( $excerpt );

        // 2b. Parse content, by removing shortcodes and HTML tags
        $content = html_entity_decode( strip_tags( apply_filters( 'the_content', strip_shortcodes( $post->post_content ) ) ) );

        // 2c. Limit content length based on the given character limit
        // @TODO Improve this method, as we may still end up with a status message that's too long
        if ( $character_limit > 0 && strlen( $content ) > $character_limit ) {
            $content = substr( $content, 0, $character_limit );
        }
        
        // 3. Parse message
        $text = $message;
        $text = str_replace( '{sitename}', get_bloginfo( 'name' ), $text );
        $text = str_replace( '{title}', $post->post_title, $text );
        $text = str_replace( '{excerpt}', $excerpt, $text );
        $text = str_replace( '{content}', $content, $text );
        $text = str_replace( '{date}', date( 'dS F Y', strtotime( $post->post_date ) ), $text );
        $text = str_replace( '{url}', rtrim( get_permalink( $post->ID ), '/' ), $text );
        
        return $text;

    }

    /**
     * Helper method to iterate through statuses, sending each via a separate API call
     * to the API
     *
     * @since   3.0.0
     *
     * @param   array $ statuses    Statuses
     * @param   int     $post_id    Post ID
     * @param   string  $action     Action
     * @param   array   $profiles   All Enabled Profiles
     * @return  array               API Result for each status
     */
    public function send( $statuses, $post_id, $action, $profiles ) {

        // Assume no errors
        $errors = false;

        // Setup API
        $this->base->get_class( 'api' )->set_tokens( 
            $this->base->get_class( 'settings' )->get_access_token(),
            $this->base->get_class( 'settings' )->get_refresh_token(),
            $this->base->get_class( 'settings' )->get_token_expires()
        );
       
        // Setup logging
        $log = array();
        $log_enabled = $this->base->get_class( 'settings' )->get_option( 'log' );

        // Setup results array
        $results = array();

        foreach ( $statuses as $index => $status ) {
            // Send request
            $result = $this->base->get_class( 'api' )->updates_create( $status );
            
            // Store result in array
            $results[] = $result;

            // Only continue if logging is enabled
            if ( ! $log_enabled ) {
                continue;
            }

            // Store result
            if ( is_wp_error( $result ) ) {
                // Error
                $error = true;
                $log[] = array(
                    'date'              => strtotime( 'now' ),
                    'success'           => false,
                    'status'            => $status,
                    'profile_name'      => $profiles[ $status['profile_ids'][0] ]['formatted_service'] . ': ' . $profiles[ $status['profile_ids'][0] ]['formatted_username'],
                    
                    // Data from the API
                    'profile'           => $status['profile_ids'][0],
                    'message'           => $result->get_error_message(),
                );
            } else {
                // OK
                $log[] = array(
                    'date'              => strtotime( 'now' ),
                    'success'           => true,
                    'status'            => $status,
                    'profile_name'      => $profiles[ $status['profile_ids'][0] ]['formatted_service'] . ': ' . $profiles[ $status['profile_ids'][0] ]['formatted_username'],
                    
                    // Data from the API
                    'profile'           => $result['profile_id'],
                    'message'           => $result['message'],
                    'status_text'       => $result['status_text'],
                    'status_created_at' => $result['status_created_at'],
                    'status_due_at'     => $result['due_at'],
                );
            }
        }

        // If no errors were reported, set a meta key to show a success message
        // This triggers admin_notices() to tell the user what happened
        if ( ! $errors ) {
            update_post_meta( $post_id, '_' . $this->base->plugin->filter_name . '_success', 1 );
            update_post_meta( $post_id, '_' . $this->base->plugin->filter_name . '_error', 0 );
        } else {
            update_post_meta( $post_id, '_' . $this->base->plugin->filter_name . '_success', 0 );
            update_post_meta( $post_id, '_' . $this->base->plugin->filter_name . '_error', 1 );
        }

        // Save log
        if ( $log_enabled ) {
            $this->base->get_class( 'log' )->update_log( $post_id, $log );
        }

        // Return results
        return $results;
        
    }

}
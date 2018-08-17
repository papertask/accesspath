<?php
/**
 * Common class
 * 
 * @package WP_To_Social_Pro
 * @author  Tim Carr
 * @version 3.0.0
 */
class WP_To_Social_Pro_Common {

    /**
     * Holds the base class object.
     *
     * @since   3.4.7
     *
     * @var     object
     */
    public $base;

    /**
     * Constructor
     *
     * @since   3.4.7
     *
     * @param   object $base    Base Plugin Class
     */
    public function __construct( $base ) {

        // Store base class
        $this->base = $base;
        
    }

    /**
     * Helper method to retrieve schedule options
     *
     * @since   3.0.0
     *
     * @return  array   Schedule Options
     */
    public function get_schedule_options() {

        // Build schedule options, depending on the Plugin
        switch ( $this->base->plugin->name ) {

            case 'wp-to-buffer':
                $schedule = array(
                    'queue_bottom'  => sprintf( __( 'Add to End of %s Queue', $this->base->plugin->name ), $this->base->plugin->account ),
                );
                break;

            case 'wp-to-buffer-pro':
                $schedule = array(
                    'queue_bottom'  => sprintf( __( 'Add to End of %s Queue', $this->base->plugin->name ), $this->base->plugin->account ),
                    'queue_top'     => sprintf( __( 'Add to Start of %s Queue', $this->base->plugin->name ), $this->base->plugin->account ),
                    'now'           => __( 'Post Immediately', $this->base->plugin->name ),
                    'custom'        => __( 'Custom Time', $this->base->plugin->name ),
                    'custom_field'  => __( 'Custom Time (based on Custom Field / Post Meta Value)', $this->base->plugin->name ),
                );
                break;

            case 'wp-to-hootsuite':
                $schedule = array(
                    'now'           => __( 'Post Immediately', $this->base->plugin->name ),
                );
                break;

            case 'wp-to-hootsuite-pro':
                $schedule = array(
                    'now'           => __( 'Post Immediately', $this->base->plugin->name ),
                    'custom'        => __( 'Custom Time', $this->base->plugin->name ),
                    'custom_field'  => __( 'Custom Time (based on Custom Field / Post Meta Value)', $this->base->plugin->name ),
                );
                break;
                
        }

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_schedule_options', $schedule );

    }

    /**
     * Helper method to retrieve public Post Types
     *
     * @since   3.0.0
     *
     * @return  array   Public Post Types
     */
    public function get_post_types() {

        // Get public Post Types
        $types = get_post_types( array(
            'public' => true,
        ), 'objects' );

        // Filter out excluded post types
        $excluded_types = $this->get_excluded_post_types();
        if ( is_array( $excluded_types ) ) {
            foreach ( $excluded_types as $excluded_type ) {
                unset( $types[ $excluded_type ] );
            }
        }

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_post_types', $types );

    }

    /**
     * Helper method to retrieve excluded Post Types
     *
     * @since   3.0.0
     *
     * @return  array   Excluded Post Types
     */
    public function get_excluded_post_types() {

        // Get excluded Post Types
        $types = array(
            'attachment',
        );

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_excluded_post_types', $types );

    }

    /**
     * Helper method to retrieve excluded Taxonomies
     *
     * @since   3.0.5
     *
     * @return  array   Excluded Post Types
     */
    public function get_excluded_taxonomies() {

        // Get excluded Post Types
        $taxonomies = array(
            'post_format',
        );

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_excluded_taxonomies', $taxonomies );

    }

    /**
     * Helper method to retrieve a Post Type's taxonomies
     *
     * @since   3.0.0
     *
     * @param   string  $post_type  Post Type
     * @return  array               Taxonomies
     */
    public function get_taxonomies( $post_type ) {

        // Get Post Type Taxonomies
        $taxonomies = get_object_taxonomies( $post_type, 'objects' );

        // Get excluded Taxonomies
        $excluded_taxonomies = $this->get_excluded_taxonomies();

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_taxonomies', $taxonomies, $post_type );

    }

    /**
     * Helper method to retrieve available tags for status updates
     *
     * @since   3.0.0
     *
     * @param   string  $post_type  Post Type
     * @return  array               Tags
     */
    public function get_tags( $post_type ) {

        // Get post type
        $post_types = $this->get_post_types();

        // Build tags array
        $tags = array(
            'post' => array(
                '{sitename}'            => __( 'Site Name', $this->base->plugin->name ),
                '{title}'               => __( 'Post Title', $this->base->plugin->name ),
                '{excerpt}'             => __( 'Post Excerpt', $this->base->plugin->name ),
                '{content}'             => __( 'Post Content', $this->base->plugin->name ),
                '{date}'                => __( 'Post Date', $this->base->plugin->name ),
                '{url}'                 => __( 'Post URL', $this->base->plugin->name ),
            ),
        );

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_tags', $tags, $post_type );

    }

    /**
     * Helper method to retrieve Post actions
     *
     * @since   3.0.0
     *
     * @return  array           Post Actions
     */
    public function get_post_actions() {

        // Build post actions
        $actions = array(
            'publish'   => __( 'Publish', $this->base->plugin->name ),
            'update'    => __( 'Update', $this->base->plugin->name ),
        );

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_post_actions', $actions );

    }

    /**
     * Helper method to retrieve character limits, depending on the social media network
     *
     * @since   3.4.2
     *
     * @return  array   Character Limits
     */
    public function get_character_limits() {

        $character_limits = array(
            'twitter'   => 280,
            'pinterest' => 500,
            'instagram' => 2200,
            'facebook'  => 5000,
            'linkedin'  => 700,
            'google'    => 5000
        );

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_character_limits', $character_limits );

    }

    /**
     * Helper method to retrieve the character limit for the given service.
     *
     * @since   3.4.2
     *
     * @param   string  $service    Social Media Service
     * @return  int                 Character Limit
     */
    public function get_character_limit( $service ) {

        // Assume there is no limit
        $character_limit = 0;

        // Get character limits for all social networks
        $character_limits = $this->get_character_limits();

        // Bail if the service doesn't have a character limit defined
        if ( ! isset( $character_limits[ $service ] ) ) {
            return $character_limit;
        }

        // Filter and return the character limit
        $character_limit = absint( $character_limits[ $service ] );
        return apply_filters( $this->base->plugin->filter_name . '_get_character_limit', $character_limit, $service );

    }

    /**
     * Helper method to retrieve transient expiration time
     *
     * @since   3.0.0
     *
     * @return  int     Expiration Time (seconds)
     */
    public function get_transient_expiration_time() {

        // Set expiration time for all transients = 12 hours
        $expiration_time = ( 12 * HOUR_IN_SECONDS );

        // Return filtered results
        return apply_filters( $this->base->plugin->filter_name . '_get_transient_expiration_time', $expiration_time );

    }

}
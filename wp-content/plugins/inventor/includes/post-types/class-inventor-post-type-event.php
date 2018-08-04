<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Inventor_Post_Type_Event
 *
 * @class Inventor_Post_Type_Event
 * @package Inventor/Classes/Post_Types
 * @author Pragmatic Mates
 */
class Inventor_Post_Type_Event {
	public static $date_and_time_fields = 'date_and_time_interval';

	/**
	 * Initialize custom post type
	 *
	 * @access public
	 * @return void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'definition' ) );
        add_action( 'cmb2_init', array( __CLASS__, 'fields' ) );
		add_filter( 'inventor_shop_allowed_listing_post_types', array( __CLASS__, 'allowed_purchasing' ) );
		add_filter( 'inventor_claims_allowed_listing_post_types', array( __CLASS__, 'allowed_claiming' ) );

		add_filter( 'inventor_filter_fields', array( __CLASS__, 'filter_fields' ), 11 );
		add_filter( 'inventor_filter_query_taxonomies', array( __CLASS__, 'filter_query_by_taxonomies' ), 10, 2 );
		add_filter( 'inventor_filter_field_plugin_dir', array( __CLASS__, 'filter_field_plugin_dir' ), 10, 2 );
	}

	/**
	 * Defines if post type can be claimed
	 *
	 * @access public
	 * @param array $post_types
	 * @return array
	 */
	public static function allowed_claiming( $post_types ) {
		$post_types[] = 'event';
		return $post_types;
	}

	/**
	 * Defines if post type can be purchased
	 *
	 * @access public
	 * @param array $post_types
	 * @return array
	 */
	public static function allowed_purchasing( $post_types ) {
		$post_types[] = 'event';
		return $post_types;
	}

	/**
	 * Custom post type definition
	 *
	 * @access public
	 * @return void
	 */
	public static function definition() {
		$labels = array(
			'name'                  => __( 'Events', 'inventor' ),
			'singular_name'         => __( 'Event', 'inventor' ),
			'add_new'               => __( 'Add New Event', 'inventor' ),
			'add_new_item'          => __( 'Add New Event', 'inventor' ),
			'edit_item'             => __( 'Edit Event', 'inventor' ),
			'new_item'              => __( 'New Event', 'inventor' ),
			'all_items'             => __( 'Events', 'inventor' ),
			'view_item'             => __( 'View Event', 'inventor' ),
			'search_items'          => __( 'Search Events', 'inventor' ),
			'not_found'             => __( 'No Events found', 'inventor' ),
			'not_found_in_trash'    => __( 'No Events Found in Trash', 'inventor' ),
			'parent_item_colon'     => '',
			'menu_name'             => __( 'Cars', 'inventor' ),
			'icon'                  => apply_filters( 'inventor_listing_type_icon', 'inventor-poi-theatre', 'event' )
		);

		register_post_type( 'event',
			array(
				'labels'            => $labels,
				'show_in_menu'	  	=> 'listings',
				'supports'          => array( 'title', 'editor', 'thumbnail', 'comments', 'author' ),
				'has_archive'       => true,
				'rewrite'           => array( 'slug' => _x( 'events', 'URL slug', 'inventor' ) ),
				'public'            => true,
				'show_ui'           => true,
				'show_in_rest'      => true,
				'categories'        => array(),
			)
		);
	}

    /**
     * Defines custom fields
     *
     * @access public
     * @return array
     */
    public static function fields() {
		Inventor_Post_Types::add_metabox( 'event', array( 'general' ) );

        $cmb = new_cmb2_box( array(
            'id'            => INVENTOR_LISTING_PREFIX . 'event_details',
            'title'         => __( 'Details', 'inventor' ),
            'object_types'  => array( 'event' ),
            'context'       => 'normal',
            'priority'      => 'high',
			'show_in_rest'  => true,
        ) );

        $cmb->add_field( array(
            'name'              => __( 'Event type', 'inventor' ),
            'id'                => INVENTOR_LISTING_PREFIX . 'event_type',
            'type'              => 'taxonomy_select',
            'taxonomy'          => 'event_types',
        ) );

        Inventor_Post_Types::add_metabox( 'event', array( self::$date_and_time_fields, 'gallery', 'banner', 'contact', 'social', 'video', 'price', 'flags', 'location', 'listing_category' ) );
    }

	/**
	 * Returns datetime timestamps for given event
	 *
	 * @access public
	 * @param int $post_id
	 * @return array
	 */
	public static function get_timestamps( $post_id = null ) {
		if ( empty ( $post_id ) ) {
			$post_id = get_the_ID();
		}

		$timestamps = array();

		if ( self::$date_and_time_fields == 'date_and_time_interval' ) {
			$date = get_post_meta( $post_id, INVENTOR_LISTING_PREFIX . 'date', true );

			if ( ! empty( $date ) ) {
				// TODO: check timezone
				$date_str = strftime( '%Y-%m-%d', $date );

				$time_from = get_post_meta( $post_id, INVENTOR_LISTING_PREFIX . 'time_from', true );

				if ( ! empty( $time_from ) ) {
					$time_from_timestamp = strtotime($time_from);
					$time_from_str = strftime('%H:%M', $time_from_timestamp);
					$timestamp_from = date('Y-m-d H:i', strtotime("$date_str $time_from_str"));
					$timestamps['from'] = strtotime($timestamp_from);
				}

				$time_to = get_post_meta( $post_id, INVENTOR_LISTING_PREFIX . 'time_to', true );

				if ( ! empty( $time_to ) ) {
					$time_to_timestamp = strtotime($time_to);
					$time_to_str = strftime('%H:%M', $time_to_timestamp);
					$timestamp_to = date('Y-m-d H:i', strtotime("$date_str $time_to_str"));
					$timestamps['to'] = strtotime($timestamp_to);
				}
			}
		}

		// TODO: implement other date & time range metaboxes

		return $timestamps;
	}

	/**
	 * Adds event type field to filter
	 *
	 * @access public
	 * @param $fields array
	 * @return array
	 */
	public static function filter_fields( $fields ) {
		$fields['event-type'] = __( 'Event type', 'inventor' );
		return $fields;
	}

	/**
	 * Sets filter field plugin dir for event type
	 *
	 * @access public
	 * @param $plugin_dir string
	 * @param $template string
	 * @return string
	 */
	public static function filter_field_plugin_dir( $plugin_dir, $template ) {
		if ( $template == 'event-type' ) {
			return INVENTOR_DIR;
		}
		return $plugin_dir;
	}

	/**
	 * Filters listings by event types taxonomy filter field
	 *
	 * @access public
	 * @param $taxonomies array
	 * @param $params array
	 * @return array
	 */
	public static function filter_query_by_taxonomies( $taxonomies, $params ) {
		$filter_field_identifier = 'event-type';
		$field_type = apply_filters( 'inventor_filter_field_type', 'SELECT', $filter_field_identifier );
		$field_name = $field_type == 'SELECT' ? 'event-type' : 'event-types';
		$taxonomy = 'event_types';

		if ( ! empty( $params[ $field_name ] ) ) {
			$value = $params[ $field_name ];
			$operator = 'IN';

			$taxonomies[] = array(
				'taxonomy'  => $taxonomy,
				'field'     => 'slug',
				'terms'     => $value,
				'operator'  => $operator
			);
		}

		return $taxonomies;
	}
}

Inventor_Post_Type_Event::init();